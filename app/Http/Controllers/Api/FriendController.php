<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Friendship;
use App\Models\User;
use Illuminate\Http\Request;

class FriendController extends Controller
{
    /**
     * Tìm kiếm người dùng mới để kết bạn
     */
    public function search(Request $request)
    {
        $query = $request->get('q');
        if (!$query) return response()->json([]);

        $users = User::with('settings')
            ->where('id', '!=', $request->user()->id)
            ->where(function ($q) use ($query) {
                $q->where('name', 'LIKE', "%$query%")
                  ->orWhere('email', 'LIKE', "%$query%");
            })
            ->limit(10)
            ->get();

        return response()->json($users);
    }

    /**
     * Gửi yêu cầu kết bạn
     */
    public function sendRequest(Request $request)
    {
        $request->validate(['receiver_id' => 'required|exists:users,id']);
        $senderId = $request->user()->id;
        $receiverId = $request->receiver_id;

        if ($senderId == $receiverId) {
            return response()->json(['message' => 'Không thể kết bạn với chính mình.'], 400);
        }

        $existing = Friendship::where(function ($q) use ($senderId, $receiverId) {
            $q->where('sender_id', $senderId)->where('receiver_id', $receiverId);
        })->orWhere(function ($q) use ($senderId, $receiverId) {
            $q->where('sender_id', $receiverId)->where('receiver_id', $senderId);
        })->first();

        if ($existing) {
            return response()->json(['message' => 'Yêu cầu kết bạn đã tồn tại hoặc đã là bạn bè.'], 400);
        }

        Friendship::create([
            'sender_id'   => $senderId,
            'receiver_id' => $receiverId,
            'status'      => 'pending',
        ]);

        return response()->json(['message' => 'Đã gửi lời mời kết bạn.']);
    }

    /**
     * Phản hồi lời mời (chấp nhận/từ chối)
     */
    public function respond(Request $request)
    {
        $request->validate([
            'sender_id' => 'required|exists:users,id',
            'action'    => 'required|in:accept,decline',
        ]);

        $receiverId = $request->user()->id;
        $senderId = $request->sender_id;

        $friendship = Friendship::where('sender_id', $senderId)
            ->where('receiver_id', $receiverId)
            ->where('status', 'pending')
            ->firstOrFail();

        if ($request->action === 'accept') {
            $friendship->update(['status' => 'accepted']);
            return response()->json(['message' => 'Đã chấp nhận lời mời.']);
        } else {
            $friendship->delete();
            return response()->json(['message' => 'Đã từ chối lời mời.']);
        }
    }

    /**
     * Lấy danh sách bạn bè & lời mời đang chờ
     */
    public function index(Request $request)
    {
        $user = $request->user();

        // Danh sách bạn bè (status = accepted)
        $friendsSent = Friendship::where('sender_id', $user->id)->where('status', 'accepted')->with('receiver.settings')->get()->pluck('receiver');
        $friendsReceived = Friendship::where('receiver_id', $user->id)->where('status', 'accepted')->with('sender.settings')->get()->pluck('sender');
        $friends = $friendsSent->concat($friendsReceived);

        // Lời mời đang chờ
        $requests = Friendship::where('receiver_id', $user->id)
            ->where('status', 'pending')
            ->with('sender.settings')
            ->get()
            ->pluck('sender');

        return response()->json([
            'friends'  => $friends,
            'requests' => $requests,
        ]);
    }
}
