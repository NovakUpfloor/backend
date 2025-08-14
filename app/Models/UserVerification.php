<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class UserVerification extends Model
{
    protected $fillable = [
        'user_id',
        'token',
        'expired_at'
    ];

    protected $casts = [
        'expired_at' => 'datetime'
    ];

    public function user()
    {
        return $this->belongsTo(User::class, 'user_id', 'id_user');
    }

    public function isExpired()
    {
        return $this->expired_at->isPast();
    }

    public static function generateToken()
    {
        return bin2hex(random_bytes(32));
    }
}