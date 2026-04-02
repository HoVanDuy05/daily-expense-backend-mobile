<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Message;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class MessageController extends Controller
{
    /**
     * Lấy danh sách Inbox (Những người đã nhắn tin)
     */
    public function index(Request $request)
    {
        $userId = $request->user()->id;

        // Query xịn để lấy tin nhắn cuối cùng với từng người (Inbox style)
        $lastMessages = Message::where('sender_id', $userId)
            ->orWhere('receiver_id', $userId)
            ->orderBy('created_at', 'desc')
            ->get()
            ->unique(function ($item) use ($userId) {
                return $item->sender_id == $userId ? $item->receiver_id : $item->sender_id;
            });

        $inbox = $lastMessages->map(function ($msg) use ($userId) {
            $otherUserId = $msg->sender_id == $userId ? $msg->receiver_id : $msg->sender_id;
            $otherUser = User::find($otherUserId)->load('settings');
            
            return [
                'id' => $otherUserId,
                'name' => $otherUser->name,
                'avatar' => $otherUser->settings->avatar ?? null,
                'lastMessage' => $msg->content,
                'time' => $msg->created_at->diffForHumans(),
                'unread' => $msg->receiver_id == $userId && !$msg->read_at,
                'timestamp' => $msg->created_at
            ];
        });

        return response()->json($inbox->values());
    }

    /**
     * Lấy lịch sử chat với 1 người cụ thể
     */
    public function chat(Request $request, $friendId)
    {
        $userId = $request->user()->id;

        $messages = Message::where(function($q) use ($userId, $friendId) {
                $q->where('sender_id', $userId)->where('receiver_id', $friendId);
            })
            ->orWhere(function($q) use ($userId, $friendId) {
                $q->where('sender_id', $friendId)->where('receiver_id', $userId);
            })
            ->orderBy('created_at', 'asc')
            ->get();

        // Đánh dấu đã đọc
        Message::where('sender_id', $friendId)
            ->where('receiver_id', $userId)
            ->whereNull('read_at')
            ->update(['read_at' => now()]);

        return response()->json($messages);
    }

    /**
     * Gửi tin nhắn mới
     */
    public function store(Request $request)
    {
        $request->validate([
            'receiver_id' => 'required|exists:users,id',
            'content' => 'required|string'
        ]);

        $message = Message::create([
            'sender_id' => $request->user()->id,
            'receiver_id' => $request->receiver_id,
            'content' => $request->input('content')
        ]);

        return response()->json($message, 201);
    }
}
