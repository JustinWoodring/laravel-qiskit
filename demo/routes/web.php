<?php

use App\Http\Controllers\BackendController;
use App\Http\Controllers\JobController;
use App\Http\Controllers\SessionController;
use Illuminate\Support\Facades\Route;

/*
|--------------------------------------------------------------------------
| Demo Routes
|--------------------------------------------------------------------------
|
| These routes demonstrate the main features of laravel-qiskit.
|
*/

Route::get('/', fn () => view('welcome'));

// --- Backends ---
Route::get('/backends', [BackendController::class, 'index'])->name('backends.index');

// --- Jobs ---
Route::get('/jobs', [JobController::class, 'index'])->name('jobs.index');
Route::get('/jobs/{id}', [JobController::class, 'show'])->name('jobs.show');

// --- Submit ---
Route::get('/submit/bell-state', [JobController::class, 'submitBellState'])->name('submit.bell');
Route::get('/submit/ghz', [JobController::class, 'submitGhz'])->name('submit.ghz');
Route::get('/submit/vqe', [JobController::class, 'submitVqe'])->name('submit.vqe');
Route::post('/jobs/{id}/cancel', [JobController::class, 'cancel'])->name('jobs.cancel');

// --- Sessions ---
Route::get('/sessions/demo', [SessionController::class, 'demo'])->name('sessions.demo');
