<?php

use App\Http\Controllers\UserController;
use Illuminate\Support\Facades\Route;

Route::middleware(['auth', 'account.active', 'role:admin'])->prefix('admin')->group(function () {
    Route::resource('users', UserController::class)->only(['index', 'show', 'edit', 'update', 'destroy']);
    Route::patch('users/{user}/lock', [UserController::class, 'lock'])->name('users.lock');
    Route::patch('users/{user}/unlock', [UserController::class, 'unlock'])->name('users.unlock');
});
