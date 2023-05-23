<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\DB;

class Cook extends User
{
    protected $table = 'users';

    public static function boot()
    {
        parent::boot();

        static::addGlobalScope(function ($query) {
            $query->where('is_admin', true);
        });

        static::created(function ($user) {
            DB::table('roles')->insert([
                'userId' => $user->id,
                'role' => 'Cook'
            ]);
        });
    }
}
