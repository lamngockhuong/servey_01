<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Carbon\Carbon;

class Survey extends Model
{
    protected $fillable = [
        'title',
        'user_id',
        'feature',
        'token',
        'status',
        'start_time',
        'deadline',
        'next_reminder_time',
        'description',
        'mail',
        'user_name',
        'update',
    ];

    public function invites()
    {
        return $this->hasMany(Invite::class);
    }

    public function user()
    {
        return $this->belongsTo(User::class);
    }

    public function questions()
    {
        return $this->hasMany(Question::class)->orderBy('sequence');
    }

    public function likes()
    {
        return $this->hasMany(Like::class);
    }

    public function getDeadlineAttribute()
    {
        return (!empty($this->attributes['deadline']))
            ? Carbon::parse($this->attributes['deadline'])->format('Y-m-d H:i:s')
            : null;
    }

    public function temps()
    {
        return $this->hasMany(Temp::class);
    }

    public function settings()
    {
        return $this->hasMany(Setting::class);
    }

    public function getIsOpenAttribute()
    {
        return (empty($this->attributes['deadline']) || Carbon::parse($this->attributes['deadline'])->gt(Carbon::now()));
    }

    public function getSubTitleAttribute()
    {
        return str_limit($this->attributes['title'], config('settings.title_length_default'));
    }

    public function getIsExpiredAttribute()
    {
        return empty($this->attributes['deadline']) ? false : $this->attributes['deadline'] <= Carbon::now()->toDateTimeString();
    }
}
