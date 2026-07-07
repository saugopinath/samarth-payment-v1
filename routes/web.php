<?php

use Illuminate\Support\Facades\Route;

Route::redirect('/', '/login');

require __DIR__.'/auth.php';
