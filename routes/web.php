<?php

use Illuminate\Support\Facades\Route;
use Illuminate\Support\Facades\File;

Route::get('/', function () {
    $apiExists = File::exists(base_path('routes/api.php')) ? 'YES' : 'NO';
    
    return response()->json([
        'status' => 'success',
        'message' => '🚀 SERVER IS RUNNING!',
        'api_file_exists' => $apiExists,
        'time' => now()->toDateTimeString()
    ]);
});
