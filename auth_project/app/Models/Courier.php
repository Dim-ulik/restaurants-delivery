<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\DB;

class Courier extends User
{
    protected $table = 'users';

    public static function boot()
    {
        parent::boot();

        static::created(function ($user) {
            DB::table('roles')->insert([
                'userId' => $user->id,
                'role' => 'Courier'
            ]);
        });
    }
}
