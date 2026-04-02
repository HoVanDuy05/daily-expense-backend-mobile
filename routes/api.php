<?php

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;
use App\Http\Controllers\Api\AuthController;
use App\Http\Controllers\Api\ExpenseController;
use App\Http\Controllers\Api\CategoryController;
use App\Http\Controllers\Api\FriendController;
use App\Http\Controllers\Api\NotificationController;

// =========================================
// Public routes (không cần đăng nhập)
// =========================================
Route::prefix('auth')->group(function () {
    Route::post('/register', [AuthController::class, 'register']);
    Route::post('/login',    [AuthController::class, 'login']);
});

// =========================================
// Protected routes (cần Bearer Token)
// =========================================
Route::middleware('auth:sanctum')->group(function () {

    // Auth
    Route::get('/auth/me',     [AuthController::class, 'me']);
    Route::post('/auth/logout', [AuthController::class, 'logout']);

    // Chi tiêu & Danh mục
    Route::get('/categories',       [CategoryController::class, 'index']);
    Route::get('/expenses',         [ExpenseController::class, 'index']);
    Route::get('/expenses/stats',   [ExpenseController::class, 'stats']);
    Route::post('/expenses',        [ExpenseController::class, 'store']);
    Route::delete('/expenses/{expense}', [ExpenseController::class, 'destroy']);

    // Kết bạn & Danh sách bạn bè
    Route::get('/friends/search',  [FriendController::class, 'search']);
    Route::get('/friends',         [FriendController::class, 'index']);
    Route::post('/friends/request', [FriendController::class, 'sendRequest']);
    Route::post('/friends/respond', [FriendController::class, 'respond']);

    // Thông báo
    Route::get('/notifications', [NotificationController::class, 'index']);
    Route::post('/notifications/{id}/read', [NotificationController::class, 'markAsRead']);
});
