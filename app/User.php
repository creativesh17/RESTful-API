<?php

namespace App;

use Illuminate\Support\Str;
use Laravel\Passport\HasApiTokens;
use App\Transformers\UserTransformer;
use Illuminate\Notifications\Notifiable;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Contracts\Auth\MustVerifyEmail;
use Illuminate\Foundation\Auth\User as Authenticatable;

class User extends Authenticatable
{
    use Notifiable, HasApiTokens, SoftDeletes;

    public $transformer = UserTransformer::class;

    const VERIFIED_USER = '1';
    const UNVERIFIED_USER = '0';

    const ADMIN_USER = 'true';
    const REGULAR_USER = 'false';

    protected $table = 'users';

    protected $fillable = [
        'name',
        'email', 
        'password',
        'verified',
        'verification_token',
        'admin',
    ];

 
    protected $hidden = [
        'password', 
        'remember_token',
        'verification_token',
    ];


    protected $casts = [
        'email_verified_at' => 'datetime',
    ];

    // mutator
    public function setNameAttribute($name) {
        $this->attributes['name'] = strtolower($name);
    }
    // accessor
    public function getNameAttribute($name) {
        return ucwords($name);
    }
    // mutator
    public function setEmailAttribute($email) {
        $this->attributes['email'] = strtolower($email);
    }

    public function isVerified() {
        return $this->verified == User::VERIFIED_USER;
    }

    public function isAdmin() {
        return $this->admin == User::ADMIN_USER;
    }

    public static function generateVerificationCode() {
        return Str::random(40);
    }
}
