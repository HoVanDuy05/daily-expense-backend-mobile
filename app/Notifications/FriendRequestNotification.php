<?php

namespace App\Notifications;

use App\Models\User;
use Illuminate\Bus\Queueable;
use Illuminate\Notifications\Notification;

class FriendRequestNotification extends Notification
{
    use Queueable;

    protected $sender;

    public function __construct(User $sender)
    {
        $this->sender = $sender;
    }

    public function via($notifiable): array
    {
        return ['database'];
    }

    public function toArray($notifiable): array
    {
        return [
            'type'    => 'friend_request',
            'title'   => 'Lời mời kết bạn mới',
            'message' => "{$this->sender->name} đã gửi lời mời kết bạn.",
            'sender_id' => $this->sender->id,
            'sender_avatar' => $this->sender->settings?->avatar,
        ];
    }
}
