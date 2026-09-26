<?php

use Illuminate\Support\Facades\Route;

Route::redirect('/', '/books')->name('home');

require __DIR__.'/auth.php';
require __DIR__.'/books.php';
require __DIR__.'/authors.php';
require __DIR__.'/categories.php';
require __DIR__.'/users.php';
require __DIR__.'/borrow.php';
require __DIR__.'/history.php';
require __DIR__.'/statistics.php';
