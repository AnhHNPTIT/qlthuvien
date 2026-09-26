<?php

use App\Http\Controllers\BookController;
use Illuminate\Support\Facades\Route;

Route::get('/books', [BookController::class, 'index'])->name('books.index');
Route::get('/books/{book}', [BookController::class, 'show'])->whereNumber('book')->name('books.show');
Route::middleware(['auth', 'account.active', 'role:admin'])->group(function () {
    Route::get('/admin/books', [BookController::class, 'adminIndex'])->name('books.admin.index');
    Route::get('/admin/books/create', [BookController::class, 'create'])->name('books.create');
    Route::post('/admin/books', [BookController::class, 'store'])->name('books.store');
    Route::get('/admin/books/{book}/edit', [BookController::class, 'edit'])->name('books.edit');
    Route::put('/admin/books/{book}', [BookController::class, 'update'])->name('books.update');
    Route::delete('/admin/books/{book}', [BookController::class, 'destroy'])->name('books.destroy');
    Route::patch('/admin/books/{book}/status', [BookController::class, 'toggleStatus'])->name('books.toggle-status');
});
