<?php

namespace App\Models;

// use Illuminate\Contracts\Auth\MustVerifyEmail;
use Database\Factories\UserFactory;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;
use Laravel\Sanctum\HasApiTokens;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\HasOne;
use App\Models\UserSetting;
use App\Models\Friendship;

class User extends Authenticatable
{
    /** @use HasFactory<UserFactory> */
    use HasApiTokens, HasFactory, Notifiable;

    /**
     * The attributes that are mass assignable.
     *
     * @var list<string>
     */
    protected $fillable = [
        'name',
        'email',
        'password',
    ];

    /**
     * The attributes that should be hidden for serialization.
     *
     * @var list<string>
     */
    protected $hidden = [
        'password',
        'remember_token',
    ];

    /**
     * Get the attributes that should be cast.
     *
     * @return array<string, string>
     */
    protected function casts(): array
    {
        return [
            'email_verified_at' => 'datetime',
            'password' => 'hashed',
        ];
    }

    public function expenses(): HasMany
    {
        return $this->hasMany(Expense::class);
    }

    /**
     * Thông tin cài đặt người dùng (Avatar, v.v.)
     */
    public function settings()
    {
        return $this->hasOne(UserSetting::class);
    }

    /**
     * Lấy tất cả các quan hệ bạn bè của user
     */
    public function friendships()
    {
        return $this->hasMany(Friendship::class, 'sender_id')
            ->orWhere('receiver_id', $this->id);
    }

    /**
     * Helper để lấy danh sách User là bạn bè thực thụ
     */
    public function friends()
    {
        $sent = Friendship::where('sender_id', $this->id)
            ->where('status', 'accepted')
            ->with('receiver.settings')
            ->get()
            ->pluck('receiver');

        $received = Friendship::where('receiver_id', $this->id)
            ->where('status', 'accepted')
            ->with('sender.settings')
            ->get()
            ->pluck('sender');

        return $sent->concat($received);
    }

    public function sentFriendships(): HasMany
    {
        return $this->hasMany(Friendship::class, 'sender_id');
    }

    public function receivedFriendships(): HasMany
    {
        return $this->hasMany(Friendship::class, 'receiver_id');
    }
}
