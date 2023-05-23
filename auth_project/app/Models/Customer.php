<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Builder;
use Illuminate\Support\Facades\DB;

class Customer extends User
{
    protected $table = 'users';

    public static function boot()
    {
        parent::boot();

        static::created(function ($user) {
            DB::table('roles')->insert([
                'userId' => $user->id,
                'role' => 'Customer'
            ]);
        });
    }
}
