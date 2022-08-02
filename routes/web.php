<?php

use App\Http\Controllers\TournamentController;
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

Route::get('/', function () {
    return Inertia::render('Welcome', [
        'canLogin' => Route::has('login'),
        'canRegister' => Route::has('register'),
        'laravelVersion' => Application::VERSION,
        'phpVersion' => PHP_VERSION,
    ]);
});

Route::middleware(['auth', 'verified'])->group(function() {
    Route::get('/dashboard', function () {
        return Inertia::render('Dashboard');
    })->middleware(['auth', 'verified'])->name('dashboard');

    Route::get('/users', [TournamentController::class, 'index'])->name('users');
    Route::get('/users/create', [TournamentController::class, 'create']);
    Route::post('/users', [TournamentController::class, 'store']);
    Route::get('/users/{user}/edit', [TournamentController::class, 'edit']);
    Route::put('/users/{user}', [TournamentController::class, 'update']);
    Route::delete('/users/{user}', [TournamentController::class, 'destroy']);

    Route::get('/tournaments', [TournamentController::class, 'index'])->name('tournaments');
    Route::get('/tournaments/create', [TournamentController::class, 'create']);
    Route::post('/tournaments', [TournamentController::class, 'store']);
    Route::get('/tournaments/{tournament}/edit', [TournamentController::class, 'edit']);
    Route::put('/tournaments/{tournament}', [TournamentController::class, 'update']);
    Route::delete('/tournaments/{tournament}', [TournamentController::class, 'destroy']);
});

require __DIR__.'/auth.php';
