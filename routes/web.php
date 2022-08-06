<?php

use App\Http\Controllers\TournamentController;
use App\Http\Controllers\UserController;
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

Route::get('/', [TournamentController::class, 'index'])->name('tournaments');

Route::middleware(['auth', 'verified'])->group(function() {
    Route::get('/dashboard', function () {
        return Inertia::render('Dashboard');
    })->middleware(['auth', 'verified'])->name('dashboard');

    Route::get('/users', [UserController::class, 'index'])->name('users');
    Route::get('/users/create', [UserController::class, 'create']);
    Route::post('/users', [UserController::class, 'store']);
    Route::get('/users/{user}/edit', [UserController::class, 'edit']);
    Route::put('/users/{user}', [UserController::class, 'update']);
    Route::delete('/users/{user}', [UserController::class, 'destroy']);

    Route::get('/tournaments/create', [TournamentController::class, 'create']);
    Route::post('/tournaments', [TournamentController::class, 'store']);
    Route::get('/tournaments/{tournament}/edit', [TournamentController::class, 'edit']);
    Route::put('/tournaments/{tournament}', [TournamentController::class, 'update']);
    Route::delete('/tournaments/{tournament}', [TournamentController::class, 'destroy']);
    Route::get('/tournaments/{tournament}/players', [TournamentController::class, 'createPlayers'])
    ->name('tournament.players');
    Route::post('/tournaments/{tournament}/players', [TournamentController::class, 'storePlayers']);
    Route::delete('/tournaments/{tournament}/players/{player}', [TournamentController::class, 'detachPlayer']);
    Route::delete('/tournaments/{tournament}/teams/{team}', [TournamentController::class, 'detachTeam']);
    Route::post('/tournaments/{tournament}/players/connect', [TournamentController::class, 'connectPlayers']);
});

require __DIR__.'/auth.php';
