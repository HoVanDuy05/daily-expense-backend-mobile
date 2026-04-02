<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\User;
use App\Models\Friendship;
use Illuminate\Http\Request;
use App\Notifications\FriendRequestNotification;

class FriendController extends Controller
{
    /**
     * Tìm kiếm người dùng mới (Chuẩn SEO & Performance)
     */
    public function search(Request $request)
    {
        $query = $request->get('q');
        if (!$query || strlen($query) < 2) return response()->json([]);

        $userId = $request->user()->id;

        // Tìm những người KHÔNG PHẢI là mình và chưa là bạn
        $users = User::where(function($q) use ($query) {
                $q->where('name', 'like', "%$query%")
                  ->orWhere('email', 'like', "%$query%");
            })
            ->where('id', '!=', $userId)
            ->limit(15)
            ->get()
            ->map(function($u) {
                return [
                    'id' => $u->id,
                    'name' => $u->name,
                    'email' => $u->email,
                    'avatar' => $u->settings?->avatar ?? "https://ui-avatars.com/api/?name=" . urlencode($u->name) . "&background=random"
                ];
            });

        return response()->json($users);
    }

    /**
     * Danh sách bạn bè & Lời mời kết bạn
     */
    public function index(Request $request)
    {
        $user = $request->user();

        // Bạn bè (Đã chấp nhận)
        $friends = $user->friends()->map(function($f) {
            return [
                'id' => $f->id,
                'name' => $f->name,
                'avatar' => $f->settings?->avatar ?? "https://ui-avatars.com/api/?name=" . urlencode($f->name) . "&background=random"
            ];
        });

        // Lời mời đang chờ (Pending)
        $requests = Friendship::where('receiver_id', $user->id)
            ->where('status', 'pending')
            ->with('sender')
            ->get()
            ->map(function($fr) {
                return [
                    'id' => $fr->sender->id,
                    'friendship_id' => $fr->id,
                    'name' => $fr->sender->name,
                    'avatar' => $fr->sender->settings->avatar ?? "https://ui-avatars.com/api/?name=" . urlencode($fr->sender->name) . "&background=random"
                ];
            });

        return response()->json([
            'friends' => $friends,
            'requests' => $requests
        ]);
    }

    /**
     * Gửi lời mời kết bạn
     */
    public function sendRequest(Request $request)
    {
        $request->validate(['receiver_id' => 'required|exists:users,id']);
        $senderId = $request->user()->id;
        $receiverId = $request->receiver_id;

        if ($senderId == $receiverId) {
            return response()->json(['message' => 'Bạn không thể kết bạn với chính mình!'], 422);
        }

        // Kiểm tra xem đã có quan hệ chưa
        $existing = Friendship::where(function($q) use ($senderId, $receiverId) {
            $q->where('sender_id', $senderId)->where('receiver_id', $receiverId);
        })->orWhere(function($q) use ($senderId, $receiverId) {
            $q->where('sender_id', $receiverId)->where('receiver_id', $senderId);
        })->first();

        if ($existing) {
            return response()->json(['message' => 'Đã tồn tại lời mời hoặc quan hệ bạn bè!'], 422);
        }

        $friendship = Friendship::create([
            'sender_id' => $senderId,
            'receiver_id' => $receiverId,
            'status' => 'pending'
        ]);

        // Gửi thông báo (DB Notification)
        $receiver = User::find($receiverId);
        $receiver->notify(new FriendRequestNotification($request->user()));

        return response()->json(['message' => 'Đã gửi lời mời kết bạn!'], 201);
    }

    /**
     * Chấp nhận kết bạn
     */
    public function acceptRequest(Request $request, $senderId)
    {
        $receiverId = $request->user()->id;

        $friendship = Friendship::where('sender_id', $senderId)
            ->where('receiver_id', $receiverId)
            ->where('status', 'pending')
            ->first();

        if (!$friendship) {
            return response()->json(['message' => 'Không tìm thấy lời mời kết bạn!'], 404);
        }

        $friendship->update(['status' => 'accepted']);

        return response()->json(['message' => 'Đã chấp nhận kết bạn!']);
    }
}
