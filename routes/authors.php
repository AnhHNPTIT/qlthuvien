<?php

use App\Http\Controllers\AuthorController;
use Illuminate\Support\Facades\Route;

Route::middleware(['auth', 'account.active', 'role:admin'])->prefix('admin')->group(function () {
    Route::resource('authors', AuthorController::class)->except(['show']);
});
