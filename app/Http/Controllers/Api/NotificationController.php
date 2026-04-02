<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;

class NotificationController extends Controller
{
    /**
     * Danh sách thông báo của user
     */
    public function index(Request $request)
    {
        $notifications = $request->user()->notifications;
        return response()->json($notifications);
    }

    /**
     * Đánh dấu đã đọc
     */
    public function markAsRead(Request $request, $id)
    {
        $notification = $request->user()->notifications()->findOrFail($id);
        $notification->markAsRead();
        return response()->json(['message' => 'Đã đọc.']);
    }
}
