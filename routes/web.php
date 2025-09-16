<?php

use Illuminate\Support\Facades\Route;

// Ruta principal
Route::get('/', fn() => view('app'));

// Catch-all route para SPA - debe estar al final
// Esto manejará todas las rutas de Vue Router
Route::get('/{any}', fn() => view('app'))->where('any', '.*');
