<?php

use App\Http\Controllers\StatisticController;
use Illuminate\Support\Facades\Route;

Route::get('/admin/statistics', [StatisticController::class, 'index'])->middleware(['auth', 'account.active', 'role:admin'])->name('statistics.index');
