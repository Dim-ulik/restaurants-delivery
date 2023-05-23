<?php

namespace App\Auth;

use Tymon\JWTAuth\Contracts\JWTSubject;

class JWTAuthentication implements JWTSubject
{
    protected $id;
    protected $roles;
    protected $restaurant_id;

    public function __construct($id, $roles, $restaurant_id)
    {
        $this->id = $id;
        $this->roles = $roles;
        $this->restaurant_id = $restaurant_id;
    }

    public function getJWTIdentifier()
    {
        return $this->id;
    }

    public function getJWTCustomClaims()
    {
        return [
            'roles' => $this->roles,
            'restaurantId' => $this->restaurant_id
        ];
    }
}
