<?php

namespace App\Http\Controllers;

use App\Http\Requests\UserRequest;
use App\Http\Resources\UserResource;
use App\Models\User;
use App\Notifications\UserNotification;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Str;
use Illuminate\Validation\Rule;
use Inertia\Response;

class UserController extends Controller
{
    public function index(Request $request): Response
    {
        $this->setLastUrl();

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
            'origin' => $this->getLastUrl(),
        ];
    }

    public function create(): Response
    {
        return inertia('Users/Edit', $this->editOptions());
    }

    public function store(UserRequest $request): RedirectResponse
    {
        $attributes = $request->validated();

        $password = Str::random(8);
        Log::info("Created User {$attributes['name']} with Password {$password}");

        $user = User::create(array_merge($attributes, [
            'password' => Hash::make($password),
        ]));

        $user->notify(new UserNotification("Für Sie wurde ein Zugang erstellt.", "Ihr Passwort lautet: {$password}"));

        return redirect($this->getLastUrl())
            ->with('success', "{$user->name} wurde hinzugefügt.");
    }

    public function edit(User $user): Response
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

    public function update(UserRequest $request, User $user): RedirectResponse
    {
        $attributes = $request->validated();
        $user->update($attributes);

        return redirect($this->getLastUrl())
            ->with('success', "{$user->name} wurde geändert.");
    }

    public function destroy(User $user): RedirectResponse
    {
        $user->delete();

        return redirect($this->getLastUrl())
            ->with('success', 'Benutzer wurde gelöscht.');
    }

    public function loginAs(User $user): RedirectResponse
    {
        auth()->login($user);

        return redirect()->route('tournaments.index');
    }

    public function showLog(Request $request): \Symfony\Component\HttpFoundation\BinaryFileResponse
    {
        $filename = storage_path('logs/laravel.log');

        return response()->file($filename, ['content-type' => 'text']);
    }
}
