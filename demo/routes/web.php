<?php

use App\Http\Controllers\BackendController;
use App\Http\Controllers\JobController;
use App\Http\Controllers\SessionController;
use Illuminate\Support\Facades\Route;

Route::get('/', fn () => redirect()->route('jobs.index'));

Route::prefix('jobs')->name('jobs.')->group(function () {
    Route::get('/',            [JobController::class, 'index'])->name('index');
    Route::post('/',           [JobController::class, 'store'])->name('store');
    Route::get('/{job}',       [JobController::class, 'show'])->name('show');
    Route::post('/{job}/cancel', [JobController::class, 'cancel'])->name('cancel');
});

Route::get('/backends', [BackendController::class, 'index'])->name('backends.index');

Route::post('/sessions/demo', [SessionController::class, 'demo'])->name('sessions.demo');
