<?php

use App\Backup;
use App\Http\Controllers\AccountController;
use App\Http\Controllers\BackupController;
use App\Http\Controllers\FixtureController;
use App\Http\Controllers\PlayerController;
use App\Http\Controllers\StarterController;
use App\Http\Controllers\TournamentController;
use App\Http\Controllers\UserController;
use App\Models\Player;
use App\Models\Tournament;
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

Route::get('/', [TournamentController::class, 'index'])->name('tournaments.index');
Route::get('/tournaments/{tournament}/show', [TournamentController::class, 'show'])
    ->name('tournaments.show');

Route::middleware(['auth', 'verified'])->group(function() {
    Route::get('/dashboard', function () {
        return Inertia::render('Dashboard');
    })->middleware(['auth', 'verified'])->name('dashboard');

    Route::get('/account', [AccountController::class, 'edit'])->name('account.edit');
    Route::put('/account', [AccountController::class, 'update'])->name('account.update');

    Route::get('/users', [UserController::class, 'index'])
        ->name('users.index')->can('viewAny', User::class);
    Route::get('/users/create', [UserController::class, 'create'])
        ->can('create', User::class);
    Route::post('/users', [UserController::class, 'store'])
        ->can('create', User::class);
    Route::get('/users/{user}/edit', [UserController::class, 'edit'])
        ->can('update', 'user');
    Route::put('/users/{user}', [UserController::class, 'update'])
        ->can('update', 'user');
    Route::delete('/users/{user}', [UserController::class, 'destroy'])
        ->can('delete', 'user');
    Route::post('/users/{user}/login', [UserController::class, 'loginAs'])
        ->can('create', 'user');

    Route::get('/backups', [BackupController::class, 'index'])
        ->name('backups.index')->can('view', Backup::class);
    Route::get('/backups/create', [BackupController::class, 'create'])
        ->can('create', Backup::class);
    Route::post('/backups/restore', [BackupController::class, 'restore'])
        ->can('restore', Backup::class);
    Route::get('/backups/download/{filename}', [BackupController::class, 'download'])->name('backups.download')
        ->can('restore', Backup::class);

    Route::get('/players', [PlayerController::class, 'index'])
        ->name('players.index')->can('view', Player::class);
    Route::get('/players/create', [PlayerController::class, 'create'])
        ->can('create', Player::class);
    Route::post('/players', [PlayerController::class, 'store'])
        ->can('create', Player::class);
    Route::get('/players/{player}/edit', [PlayerController::class, 'edit'])
        ->can('update', 'player');
    Route::put('/players/{player}', [PlayerController::class, 'update'])
        ->can('update', 'player');
    Route::delete('/players/{player}', [PlayerController::class, 'destroy'])
        ->can('delete', 'player');

    Route::get('/tournaments/create', [TournamentController::class, 'create'])
        ->name('tournaments.create')->can('create', Tournament::class);
    Route::post('/tournaments', [TournamentController::class, 'store'])
        ->name('tournaments.store')->can('create', Tournament::class);
    Route::get('/tournaments/{tournament}/edit', [TournamentController::class, 'edit'])
        ->name('tournaments.edit')->can('update', 'tournament');
    Route::put('/tournaments/{tournament}', [TournamentController::class, 'update'])
        ->name('tournaments.update')->can('update', 'tournament');
    Route::delete('/tournaments/{tournament}', [TournamentController::class, 'destroy'])
        ->name('tournaments.destroy')->can('delete', 'tournament');
    Route::post('/tournaments/{tournament}/draw', [TournamentController::class, 'draw'])
        ->name('tournaments.draw')->can('update', 'tournament');
    Route::get('/tournaments/{tournament}/lists/{round}', [TournamentController::class, 'tableLists'])
        ->name('tournaments.lists');

    Route::get('/tournaments/{tournament}/players', [StarterController::class, 'create'])
        ->name('tournaments.players.create')->can('update', 'tournament');
    Route::post('/tournaments/{tournament}/players', [StarterController::class, 'store'])
        ->name('tournaments.players.store')->can('update', 'tournament');
    Route::delete('/tournaments/{tournament}/players/{player}', [StarterController::class, 'destroyPlayer'])
        ->name('tournaments.players.destroy')->can('update', 'tournament');
    Route::delete('/tournaments/{tournament}/teams/{team}', [StarterController::class, 'destroyTeam'])
        ->name('tournaments.teams.destroy')->can('update', 'tournament');
    Route::post('/tournaments/{tournament}/players/connect', [StarterController::class, 'connect'])
        ->name('tournaments.players.connect')->can('update', 'tournament');

    Route::get('/tournaments/fixtures/{fixture}/edit', [FixtureController::class, 'edit'])
        ->name('fixtures.edit')->can('update', 'fixture');
    Route::put('/tournaments/fixtures/{fixture}', [FixtureController::class, 'update'])
        ->name('fixtures.update')->can('update', 'fixture');
});

require __DIR__.'/auth.php';
