<?php

use App\Http\Controllers\BorrowController;
use Illuminate\Support\Facades\Route;

Route::post('/books/{book}/borrow', [BorrowController::class, 'borrow'])->middleware(['auth', 'account.active', 'role:user'])->name('borrow.store');
Route::middleware(['auth', 'account.active', 'role:admin'])->prefix('admin')->group(function () {
    Route::get('/borrow-records', [BorrowController::class, 'index'])->name('borrow.index');
    Route::patch('/borrow-records/{borrowRecord}/return', [BorrowController::class, 'returnBook'])->name('borrow.return');
    Route::patch('/borrow-records/{borrowRecord}/pay', [BorrowController::class, 'pay'])->name('borrow.pay');
});
