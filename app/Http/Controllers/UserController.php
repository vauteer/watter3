<?php

namespace App\Http\Controllers;

use App\Http\Resources\UserResource;
use App\Models\User;
use App\Notifications\UserNotification;
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
    protected const URL_KEY = 'lastUsersUrl';

    public function validationRules($id): array
    {
        return [
            'name' => 'required|string',
            'email' => [
                'required',
                'email',
                Rule::unique('users')->ignore($id)
            ],
            'admin' => 'required|boolean',
        ];
    }

    private function accountRules($id): array
    {
        return [
            'name' => 'required|string',
            'email' => [
                'required',
                'email',
                Rule::unique('users')->ignore($id)
            ],
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
        $request->session()->put(self::URL_KEY, url()->full());
        return inertia('Users/Index', [
            'users' => UserResource::collection(User::query()
                ->when($request->input('search'), function($query, $search) {
                    $query->where('name', 'like', "%{$search}%");
                })
                ->paginate(10)
                ->withQueryString()
            ),

            'options' => $request->only(['search']),

            'canCreate' => Auth::user()->admin,
        ]);
    }

    private function editOptions(): array
    {
        return [
            'origin' => session(self::URL_KEY),
        ];
    }

    public function create(Request $request): Response
    {
        return inertia('Users/Edit', $this->editOptions());
    }

    public function store(Request $request): RedirectResponse
    {
        $attributes = $request->validate($this->validationRules(-1));

        $password = Str::random(8);
        Log::info("Created User {$attributes['name']} with Password {$password}");

        $user = User::create(array_merge($attributes, [
            'password' => Hash::make($password),
        ]));

        $user->notify(new UserNotification("Für Sie wurde ein Zugang erstellt.", "Ihr Passwort lautet: {$password}"));

        return redirect(session(self::URL_KEY))
            ->with('success', "{$user->name} wurde hinzugefügt.");
    }

    public function edit(Request $request, User $user): Response
    {
        return inertia('Users/Edit', array_merge_recursive($this->editOptions(), [
            'deletable' => !$user->isUsed(),
            'user' => [
                'id' => $user->id,
                'name' => $user->name,
                'email' => $user->email,
                'admin' => $user->admin,
            ],
        ]));
    }

    public function update(Request $request, User $user): RedirectResponse
    {
        $attributes = $request->validate($this->validationRules($user->id));
        $user->update($attributes);


        return redirect(session(self::URL_KEY))
            ->with('success', "{$user->name} wurde geändert.");
    }

    public function destroy(Request $request, User $user): RedirectResponse
    {
        $user->delete();

        return redirect(session(self::URL_KEY))
            ->with('success', 'Benutzer wurde gelöscht.');
    }

    public function editAccount(Request $request): Response
    {
        $origin = url()->previous();
        $request->session()->put(self::URL_KEY, $origin);
        $user = auth()->user();

        return inertia('Users/Account', [
            'origin' => $origin,
            'user' => [
                'id' => $user->id,
                'name' => $user->name,
                'email' => $user->email,
                'profile_image' => $user->profile_image,
            ],
        ]);
    }

    public function updateAccount(Request $request): RedirectResponse
    {
        $user = auth()->user();
        $attributes = $request->validate($this->accountRules($user->id));
        $passwordAttributes = $request->validate($this->passwordRules());

        if ($passwordAttributes['password'] !== null) {
            //Log::info($passwordAttributes['password']);
            $attributes = array_merge($attributes, [
                'password' => Hash::make($passwordAttributes['password']),
            ]);
        }

        $user->update($attributes);

        User::removeOrphanProfileImages();

        return redirect(session(self::URL_KEY))
            ->with('success', "Das Konto wurde geändert.");
    }


    public function loginAs(Request $request, User $user): RedirectResponse
    {
        auth()->login($user);

        return redirect()->route('tournaments');
    }
}
