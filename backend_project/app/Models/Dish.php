<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Support\Facades\Request;

class Dish extends Model
{
    use HasFactory;

    protected $fillable = ['name', 'price', 'description', 'isVegetarian', 'category', 'photo', 'rating', 'isActive', 'restaurant_id'];

    protected $hidden = ['pivot', 'created_at', 'updated_at', 'restaurant_id', 'isDeleted'];

    protected $casts = [
        'isVegetarian' => 'boolean',
        'isActive' => 'boolean'
    ];

    public function restaurant(): BelongsTo
    {
        return $this->belongsTo(Restaurant::class);
    }

    public function menus(): BelongsToMany
    {
        return $this->belongsToMany(Menu::class);
    }

    public function ratings(): BelongsToMany
    {
        return $this->belongsToMany(Rating::class);
    }

    public function orders(): BelongsToMany
    {
        return $this->belongsToMany(Order::class);
    }

    public function baskets(): hasMany
    {
        return $this->hasMany(Basket::class);
    }

    public function getPhotoAttribute($value)
    {
        $host = config('app.url') . ':' . Request::getPort() . '/storage/uploads/';

        if ($value == null) {
            return $value;
        }

        return $host . $value;
    }

    public function setIsActiveAttribute($value)
    {
        if ($value == 'true') {
            $this->attributes['isActive'] = 1;
        } elseif ($value == 'false') {
            $this->attributes['isActive'] = 0;
        } else {
            $this->attributes['isActive'] = $value;
        }
    }

    public function setIsVegetarianAttribute($value)
    {
        if ($value == 'true') {
            $this->attributes['isVegetarian'] = 1;
        } elseif ($value == 'false') {
            $this->attributes['isVegetarian'] = 0;
        } else {
            $this->attributes['isVegetarian'] = $value;
        }
    }
}
