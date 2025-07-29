<?php

use Illuminate\Support\Facades\Route;

Route::get('/', function () {
    return view('app');
});

Route::get('/{any}', function () {
    return view('app'); // Retorna tu vista principal de Blade para que Vue Router la maneje
})->where('any', '.*');
