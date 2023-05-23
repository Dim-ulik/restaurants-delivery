<?php

namespace App\Services;

use App\Models\Dish;
use App\Models\Order;
use App\Models\Rating;

class RatingService {
    public static function checkRatingFeasibility($customerId, $dishId)
    {
        return Order::where('customer_id', $customerId)
            ->whereHas('dishes', function ($query) use ($dishId) {
                $query->where('id', $dishId);
            })
            ->where('status', 'Delivered')
            ->exists();
    }

    public static function recalculateDishRating($dishId)
    {
        $rating = Rating::where('dish_id', $dishId)->avg('rating');
        Dish::where('id', $dishId)->update([
            'rating' => $rating
        ]);
    }
}
