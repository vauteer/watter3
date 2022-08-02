<?php

namespace App\Http\Controllers;

use App\Http\Resources\UserResource;
use App\Models\User;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Log;
use Illuminate\Validation\Rules\Password;
use Illuminate\Support\Str;
use Illuminate\Validation\Rule;
use Inertia\Response;

class UserController extends Controller
{
    public function validationRules($id): array
    {
        return [
            'name' => 'required|string',
            'email' => 'required|email',
        ];
    }

    private function accountRules($id): array
    {
        return [
            'name' => 'required|string',
            'profile_image' => 'nullable|string|max:100',
        ];
    }

    private function passwordRules(): array
    {
        return [
            'current_password' => ['nullable', 'string', 'required_with:password', 'current_password'],
            'password' => ['nullable', 'string', 'confirmed', Password::min(8)],
        ];
    }

    public function index(Request $request): Response
    {
        return inertia('Users/Index', [
            'users' => UserResource::collection(User::query()
                ->when($request->input('search'), function($query, $search) {
                    $query->where('name', 'like', "%{$search}%");
                })
                ->paginate(10)
                ->withQueryString()
            ),

            'filters' => $request->only(['search']),

            'canCreate' => Auth::user()->isAdmin(),
        ]);
    }

    public function create(Request $request): Response
    {
        return inertia('Users/Edit');
    }

    public function store(Request $request): RedirectResponse
    {
        $attributes = $request->validate($this->validationRules);

        $password = Str::random(8);
        Log::info("Created User {$attributes['name']} with Password {$password}");

        $user = User::create(array_merge($attributes, [
            'password' => Hash::make($password),
        ]));

        //$user->notify(new NewUser($password));

        return redirect()->route('users')
            ->with('success', 'User created.');
    }

    public function edit(Request $request, User $user): Response
    {
        return inertia('Users/Edit', [
            'user' => [
                'id' => $user->id,
                'name' => $user->name,
                'email' => $user->email,
            ],
        ]);
    }

    public function update(Request $request, User $user): RedirectResponse
    {
        $attributes = $request->validate($this->validationRules());
        $user->update($attributes);


        return redirect()->route('users')->with('success', 'User updated.');
    }

    public function destroy(Request $request, User $user): RedirectResponse
    {
        $user->delete();

        return redirect()->route('users')
            ->with('success', 'User deleted');;
    }

    public function editAccount(Request $request): Response
    {
        $user = auth()->user();

        return inertia('Users/Account', [
            'user' => [
                'id' => $user->id,
                'name' => $user->name,
                'profile_image' => $user->profile_image,
            ],
        ])
            ->with('languages', \Helpers::languages());
    }

    public function updateAccount(Request $request): RedirectResponse
    {
        $user = auth()->user();
        $attributes = $request->validate($this->accountRules($user->id));
        $passwordAttributes = $request->validate($this->passwordRules());

        if ($passwordAttributes['password'] !== null) {
            Log::info($passwordAttributes['password']);
            $attributes = array_merge($attributes, [
                'password' => Hash::make($passwordAttributes['password']),
            ]);
        }

        $user->update($attributes);

        User::removeOrphanProfileImages();

        return redirect()->route('home')
            ->with('success', 'Account updated.');
    }


    public function loginAs(Request $request, User $user): RedirectResponse
    {
        auth()->login($user);

        return redirect()->route('home');
    }
}
