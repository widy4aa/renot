<?php

use Illuminate\Support\Facades\Route;

// Semua route diserahkan ke Vue Router (SPA)
Route::get('/{any}', function () {
    return view('welcome');
})->where('any', '^(?!api).*$');
