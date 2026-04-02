<?php

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;
use App\Http\Controllers\Api\AuthController;
use App\Http\Controllers\Api\ExpenseController;
use App\Http\Controllers\Api\CategoryController;
use App\Http\Controllers\Api\FriendController;
use App\Http\Controllers\Api\NotificationController;

/*
|--------------------------------------------------------------------------
| API Routes - CẤU TRÚC PHẲNG (DỄ NHẬN DIỆN TRÊN RENDER)
|--------------------------------------------------------------------------
*/

// TEST - Kiểm tra xem API có sống không
Route::get('/test', function() {
    return response()->json(['message' => 'Hệ thống API đã kết nối thành công!']);
});

// AUTH (Công khai)
Route::post('/auth/register', [AuthController::class, 'register']);
Route::post('/auth/login',    [AuthController::class, 'login']);

// NHÓM BẢO MẬT (Cần Token)
Route::middleware('auth:sanctum')->group(function () {
    
    // User Info
    Route::get('/auth/me',      [AuthController::class, 'me']);
    Route::post('/auth/logout', [AuthController::class, 'logout']);

    // Chi tiêu & Danh mục
    Route::get('/categories',     [CategoryController::class, 'index']);
    Route::get('/expenses',       [ExpenseController::class, 'index']);
    Route::get('/expenses/stats', [ExpenseController::class, 'stats']);
    Route::post('/expenses',      [ExpenseController::class, 'store']);
    Route::delete('/expenses/{expense}', [ExpenseController::class, 'destroy']);

    // Mạng xã hội
    Route::get('/friends/search',   [FriendController::class, 'search']);
    Route::get('/friends',          [FriendController::class, 'index']);
    Route::post('/friends/request', [FriendController::class, 'sendRequest']);
    Route::post('/friends/respond', [FriendController::class, 'respond']);

    // HỆ THỐNG TIN NHẮN (CHAT)
    Route::get('/messages', [MessageController::class, 'index']);
    Route::get('/messages/chat/{friendId}', [MessageController::class, 'chat']);

    // Thông báo
    Route::get('/notifications', [NotificationController::class, 'index']);
    Route::post('/notifications/{id}/read', [NotificationController::class, 'markAsRead']);
});
