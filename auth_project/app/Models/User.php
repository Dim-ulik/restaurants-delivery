<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use App\Services\PasswordService;

class User extends Model
{
    use HasFactory;

    protected $fillable = ['fullName', 'birthDate', 'gender', 'phone', 'email', 'password', 'isBanned', 'token', 'address'];
    protected $casts = [
        'birthDate' => 'datetime:Y-m-d\TH:i:s.v\Z',
    ];

    protected $hidden = ['pivot', 'created_at', 'updated_at', 'isBanned', 'token', 'password', 'tokenGetTime'];

    protected static function boot()
    {
        parent::boot();

        static::creating(function ($user) {
            $user->password = PasswordService::codePassword($user->password);
        });
    }
}
