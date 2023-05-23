<?php

namespace App\Services;

class AffiliationsService
{
    public static function restaurantAffiliation($userRestaurantId, $restaurantId): bool
    {
        if ($userRestaurantId != $restaurantId) {
            return false;
        }
        return true;
    }

    public static function dishAffiliation($dish, $restaurantId): bool
    {
        if ($dish->restaurant_id != $restaurantId) {
            return false;
        }
        return true;
    }

    public static function orderAffiliation($userRestaurantId, $order): bool
    {
        if ($order->restaurant_id != $userRestaurantId) {
            return false;
        }
        return true;
    }

    public static function menuAffiliation($managerRestaurantId, $menu): bool
    {
        if ($menu->restaurant_id != $managerRestaurantId) {
            return false;
        }
        return true;
    }
}
