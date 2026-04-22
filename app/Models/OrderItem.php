<?php

// namespace App\Models;

// use Illuminate\Database\Eloquent\Model;

// class OrderItem extends Model
// {
//     //
// }


namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class OrderItem extends Model
{
    use HasFactory;

    protected $fillable = [
        'order_id',
        'product_id',
        'product_name',
        'price',
        'quantity',
        'subtotal',
    ];

    protected $casts = [
        'price' => 'float',
        'subtotal' => 'float',
    ];

    /**
     * The order this item belongs to.
     */
    public function order()
    {
        return $this->belongsTo(Order::class);
    }

    /**
     * The product referenced.
     */
    public function product()
    {
        return $this->belongsTo(Product::class);
    }
}