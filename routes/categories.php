<?php

use App\Http\Controllers\CategoryController;
use Illuminate\Support\Facades\Route;

Route::middleware(['auth', 'account.active', 'role:admin'])->prefix('admin')->group(function () {
    Route::resource('categories', CategoryController::class)->except(['show']);
});
