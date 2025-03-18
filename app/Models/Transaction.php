<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Transaction extends Model
{
    use HasFactory;

    protected $fillable = [
        'barcode_id',
        'code',
        'name',
        'phone',
        'external_id',
        'checkout_link',
        'payment_method',
        'payment_status',
        'subtotal',
        'ppn',
        'total',
    ];

    public function Barcode(): \Illuminate\Database\Eloquent\Relations\BelongsTo
{
    return $this->belongsTo(\App\Models\Barcode::class, 'barcode_id', 'id');
}


    public function transactionItems(): \Illuminate\Database\Eloquent\Relations\HasMany
{
    return $this->hasMany(\App\Models\TransactionItem::class);
}

}