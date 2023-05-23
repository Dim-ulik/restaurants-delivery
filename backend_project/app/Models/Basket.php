<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class Basket extends Model
{
    use HasFactory;

    protected $fillable = ['user_id', 'dish_id', 'amount'];

    protected $hidden = ['pivot', 'created_at', 'updated_at'];

    public function dish(): BelongsTo
    {
        return $this->belongsTo(Dish::class);
    }
}
