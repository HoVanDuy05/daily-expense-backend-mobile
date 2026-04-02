<?php

use Illuminate\Support\Facades\Route;

Route::get('/', function () {
    return response()->json([
        'status' => 'success',
        'message' => '🚀 DAILY EXPENSE SERVER IS ALIVE!',
        'laravel_version' => App::version(),
        'php_version' => PHP_VERSION,
        'environment' => App::environment()
    ]);
});
