<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class UserSetting extends Model
{
    protected $fillable = [
        'user_id',
        'avatar',
        'currency',
        'notify_push',
        'biometric',
        'theme',
        'language',
    ];

    protected $casts = [
        'notify_push' => 'boolean',
        'biometric'   => 'boolean',
    ];

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }
}
