<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;

class Order extends Model
{
    use HasFactory;

    protected $fillable = ['deliveryTime', 'orderTime', 'price', 'address', 'status', 'restaurant_id', 'cook_id', 'courier_id', 'customer_id'];
    protected $casts = [
        'deliveryTime' => 'datetime:Y-m-d\TH:i:s.v\Z',
        'orderTime' => 'datetime:Y-m-d\TH:i:s.v\Z',
    ];
    protected $hidden=['pivot', 'created_at', 'updated_at'];

    public function restaurant(): BelongsTo
    {
        return $this->belongsTo(Restaurant::class);
    }

    public function dishes(): BelongsToMany
    {
        return $this->belongsToMany(Dish::class);
    }
}
