<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class FoodImage extends Model
{
    use HasFactory;

    protected $fillable = [
        'food_id',
        'images',
    ];

    public function Food(): \Illuminate\Database\Eloquent\Relations\BelongsTo
{
    return $this->belongsTo(\App\Models\Food::class, 'food_id', 'id');
}

}