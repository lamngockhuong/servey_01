<?php

namespace App\Models;

use Illuminate\Notifications\Notifiable;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Carbon\Carbon;

class User extends Authenticatable
{
    use Notifiable;
    /**
     * The attributes that are mass assignable.
     *
     * @var array
     */
    protected $fillable = [
        'name',
        'email',
        'password',
        'birthday',
        'gender',
        'phone',
        'address',
        'image',
        'status',
        'level',
    ];
    /**
     * The attributes that should be hidden for arrays.
     *
     * @var array
     */
    protected $hidden = [
        'password',
        'remember_token',
    ];

    public function likes()
    {
        return $this->hasMany(Like::class);
    }

    public function results()
    {
        return $this->hasMany(Result::class);
    }

    public function surveys()
    {
        return $this->hasMany(Survey::class);
    }

    public function setImageAttribute($value)
    {
        $this->attributes['image'] = $value ?: config('users.avatar_default');
    }

    public function getImageAttribute()
    {
        if (empty($this->attributes['image'])) {
            $this->attributes['image'] = config('settings.image_default');
        }

        return preg_match('#^(http)|(https).*$#', $this->attributes['image'])
            ? $this->attributes['image']
            : asset('/' . config('users.avatar_path') . '/' . $this->attributes['image']);
    }

    public function setPasswordAttribute($value)
    {
        $this->attributes['password'] = bcrypt($value);
    }

    public function setLevelAttribute($value)
    {
        $this->attributes['level'] = $value ?: config('users.level.user');
    }

    public function setStatusAttribute($value)
    {
        $this->attributes['status'] = $value ?: config('users.status.user');
    }

    public function reciveResults()
    {
        return $this->hasMany(Result::class, 'reciver_id');
    }

    public function sendResults()
    {
        return $this->hasMany(Result::class, 'sender_id');
    }

    public function reciveSurveys()
    {
        return $this->hasMany(Invite::class, 'reciver_id');
    }

    public function sendSurveys()
    {
        return $this->hasMany(Invite::class, 'sender_id');
    }

    public function isAdmin()
    {
        return $this->level == config('users.level.admin');
    }

    public function isSupperAdmin()
    {
        return $this->level == config('users.level.supperadmin');
    }

    public function isActive()
    {
        return $this->status == config('users.status.active');
    }

    public function setGenderAttribute($value)
    {
        $this->attributes['gender'] = $value ?: config('users.gender.male');
    }

    public function requestAdmin()
    {
        return $this->hasMany(Request::class, 'admin_id');
    }

    public function requestMember()
    {
        return $this->hasOne(Request::class, 'member_id');
    }

    public function getPartNameAttribute()
    {
        return str_limit($this->attributes['name'], config('users.name_length_default'));
    }

    public function temps()
    {
        return $this->hasMany(Temp::class);
    }

    public function scopeOfTemp($query, $id)
    {
       return $query
        ->join('temps', 'users.id', 'temps.user_id')
        ->where('temps.user_id', $this->id)
        ->where('survey_id', $id);
    }

    public function socialAccounts()
    {
        return $this->hasOne(SocialAccount::class);
    }

    public function getBirthdayAttribute()
    {
        return $this->attributes['birthday']
            ? Carbon::parse($this->attributes['birthday'])->format(trans('temp.format_birthday_with_trans')) : null;
    }

    public function checkLoginWsm()
    {
        return $this->socialAccounts()->where('provider', SocialAccount::PROVIDER_FRAMGIA)->first();
    }
}
