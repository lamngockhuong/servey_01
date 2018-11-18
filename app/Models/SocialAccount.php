<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class SocialAccount extends Model
{
    const PROVIDER_FRAMGIA = 'framgia';
    protected $fillable = [
        'user_id',
        'provider_user_id',
        'provider',
        'email',
        'name',
        'image',
    ];

    public function user()
    {
        return $this->belongsTo(User::class);
    }
}
