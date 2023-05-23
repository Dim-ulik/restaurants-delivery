<?php

namespace App\Services;

use App\Auth\JWTAuthentication;
use App\Models\User;
use Carbon\Carbon;
use Illuminate\Support\Facades\DB;
use Tymon\JWTAuth\Facades\JWTAuth;

class TokenService
{
    public static function getRoles($user)
    {
        $roles = DB::table('roles')->where('userId', $user->id)->select('role')->get();
        $rolesArray = [];

        foreach ($roles as $role) {
            $rolesArray[] = $role->role;
        }

        return $rolesArray;
    }

    public static function getRestaurant($user)
    {
        $restaurantQuery = DB::table('restaurant_affiliation')->where('userId', $user->id);
        if ($restaurantQuery->exists()) {
            return DB::table('restaurant_affiliation')->where('userId', $user->id)->first()->restaurantId;
        }
        else {
            return null;
        }
    }

    private function getJWT($userId, $roles, $restaurantId = null)
    {
        $user = new JWTAuthentication($userId, $roles, $restaurantId);
        JWTAuth::factory()->setTTL(45);

        return JWTAuth::fromSubject($user);
    }

    private function getRefresh()
    {
        return bin2hex(random_bytes(64));
    }

    public static function getTokensPair($user)
    {
        $roles = self::getRoles($user);
        if (in_array('Cook', $roles) || in_array('Manager', $roles)) {
            $restaurantId = self::getRestaurant($user);
            $jwt = self::getJWT($user->id, $roles, $restaurantId);
        }
        else {
            $jwt = self::getJWT($user->id, $roles);
        }

        $refresh = self::getRefresh();

        $user->token = $refresh;
        $user->tokenGetTime = Carbon::now();
        $user->save();

        return [
            'accessToken' => $jwt,
            'refreshToken' => $refresh,
        ];
    }

    public static function refreshToken()
    {

    }
}
