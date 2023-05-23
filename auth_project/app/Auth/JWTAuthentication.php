<?php

namespace App\Auth;

use Tymon\JWTAuth\Contracts\JWTSubject;

class JWTAuthentication implements JWTSubject
{
    protected $id;
    protected $roles;
    protected $restaurantId;

    public function __construct($id, $roles, $restaurantId)
    {
        $this->id = $id;
        $this->roles = $roles;
        $this->restaurantId = $restaurantId;
    }

    public function getJWTIdentifier()
    {
        return $this->id;
    }

    public function getJWTCustomClaims()
    {
        return [
            'roles' => $this->roles,
            'restaurantId' => $this->restaurantId
        ];
    }
}
