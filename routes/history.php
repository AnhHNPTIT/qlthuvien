<?php

use App\Http\Controllers\HistoryController;
use Illuminate\Support\Facades\Route;

Route::get('/history', [HistoryController::class, 'index'])->middleware(['auth', 'account.active', 'role:user'])->name('history.index');
