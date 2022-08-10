<?php

use App\Http\Controllers\PlayerController;
use App\Http\Controllers\TournamentController;
use App\Http\Controllers\UserController;
use App\Models\User;
use Illuminate\Foundation\Application;
use Illuminate\Support\Facades\Route;
use Inertia\Inertia;

/*
|--------------------------------------------------------------------------
| Web Routes
|--------------------------------------------------------------------------
|
| Here is where you can register web routes for your application. These
| routes are loaded by the RouteServiceProvider within a group which
| contains the "web" middleware group. Now create something great!
|
*/

/*Route::get('/', function () {
    return Inertia::render('Welcome', [
        'canLogin' => Route::has('login'),
        'canRegister' => Route::has('register'),
        'laravelVersion' => Application::VERSION,
        'phpVersion' => PHP_VERSION,
    ]);
});*/

Route::get('/test', function() {

   redirect('/');
});

Route::get('/', [TournamentController::class, 'index'])->name('tournaments');
Route::get('/tournaments/{tournament}/show', [TournamentController::class, 'show'])
    ->name('tournaments.show');

Route::middleware(['auth', 'verified'])->group(function() {
    Route::get('/dashboard', function () {
        return Inertia::render('Dashboard');
    })->middleware(['auth', 'verified'])->name('dashboard');

    Route::get('/users', [UserController::class, 'index'])
        ->can('viewAny', User::class)
        ->name('users');
    Route::get('/users/account', [UserController::class, 'editAccount']);
    Route::put('/users/account', [UserController::class, 'updateAccount']);
    Route::get('/users/create', [UserController::class, 'create'])
        ->can('create', 'user');
    Route::post('/users', [UserController::class, 'store'])
        ->can('create', 'user');
    Route::get('/users/{user}/edit', [UserController::class, 'edit'])
        ->can('update', 'user');
    Route::put('/users/{user}', [UserController::class, 'update'])
        ->can('update', 'user');
    Route::delete('/users/{user}', [UserController::class, 'destroy'])
        ->can('delete', 'user');
    Route::post('/users/{user}/login', [UserController::class, 'loginAs'])
        ->can('create', 'user');

    Route::get('/players', [PlayerController::class, 'index'])
        ->can('view', 'player')
        ->name('players');
    Route::get('/players/create', [PlayerController::class, 'create'])
        ->can('create', 'player');
    Route::post('/players', [PlayerController::class, 'store'])
        ->can('create', 'player');
    Route::get('/players/{player}/edit', [PlayerController::class, 'edit'])
        ->can('update', 'player');
    Route::put('/players/{player}', [PlayerController::class, 'update'])
        ->can('update', 'player');
    Route::delete('/players/{player}', [PlayerController::class, 'destroy'])
        ->can('delete', 'player');

    Route::get('/tournaments/create', [TournamentController::class, 'create'])
        ->can('create', 'tournament');
    Route::post('/tournaments', [TournamentController::class, 'store'])
        ->can('create', 'tournament');
    Route::get('/tournaments/{tournament}/edit', [TournamentController::class, 'edit'])
        ->can('update', 'tournament');
    Route::put('/tournaments/{tournament}', [TournamentController::class, 'update'])
        ->can('update', 'tournament');
    Route::delete('/tournaments/{tournament}', [TournamentController::class, 'destroy'])
        ->can('delete', 'tournament');
    Route::get('/tournaments/{tournament}/players', [TournamentController::class, 'createPlayers'])
        ->can('update', 'tournament')
        ->name('tournament.players');
    Route::post('/tournaments/{tournament}/players', [TournamentController::class, 'storePlayers'])
        ->can('update', 'tournament');
    Route::delete('/tournaments/{tournament}/players/{player}', [TournamentController::class, 'detachPlayer'])
        ->can('update', 'tournament');
    Route::delete('/tournaments/{tournament}/teams/{team}', [TournamentController::class, 'detachTeam'])
        ->can('update', 'tournament');
    Route::post('/tournaments/{tournament}/players/connect', [TournamentController::class, 'connectPlayers'])
        ->can('update', 'tournament');
    Route::get('/tournaments/fixtures/{fixture}/edit', [TournamentController::class, 'editFixture'])
        ->can('update', 'tournament');
    Route::put('/tournaments/fixtures/{fixture}', [TournamentController::class, 'updateFixture'])
        ->can('update', 'tournament');
    Route::get('/tournaments/{tournament}/lists/{round}', [TournamentController::class, 'tableLists'])
        ->can('update', 'tournament');
});

require __DIR__.'/auth.php';
