<?php

// namespace App\Models;

// use Illuminate\Database\Eloquent\Model;

// class Order extends Model
// {
//     //
// }


namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Order extends Model
{
    use HasFactory;

    protected $fillable = [
        'user_id',
        'order_number',
        'recipient_name',
        'delivery_address',
        'contact_number',
        'city',
        'province',
        'postal_code',
        'payment_method',
        'payment_status',
        'status',
        'subtotal',
        'shipping_fee',
        'total_amount',
        'courier_name',
        'tracking_number',
        'estimated_delivery',
        'notes',
        'processing_at',
        'shipped_at',
        'delivered_at',
    ];

    protected $casts = [
        'estimated_delivery' => 'datetime',
        'processing_at' => 'datetime',
        'shipped_at' => 'datetime',
        'delivered_at' => 'datetime',
    ];


    /**
     * The customer who placed this order.
     */
    public function customer()
    {
        return $this->belongsTo(User::class, 'user_id');
    }

    /**
     * Items in this order.
     */
    public function items()
    {
        return $this->hasMany(OrderItem::class);
    }

    /**
     * Reviews associated with this order.
     */
    public function reviews()
    {
        return $this->hasMany(Review::class);
    }

    // ==========================================
    // STATUS HELPERS
    // ==========================================

    /**
     * Get a human-readable status label.
     */
    public function getStatusLabelAttribute(): string
    {
        return match($this->status) {
            'pending'    => 'Pending',
            'processing' => 'Processing',
            'shipped'    => 'Shipped',
            'delivered'  => 'Delivered',
            'cancelled'  => 'Cancelled',
            default      => ucfirst($this->status),
        };
    }

    /**
     * Get CSS badge class for status.
     */
    public function getStatusBadgeAttribute(): string
    {
        return match($this->status) {
            'pending'    => 'badge-warning',
            'processing' => 'badge-info',
            'shipped'    => 'badge-primary',
            'delivered'  => 'badge-success',
            'cancelled'  => 'badge-danger',
            default      => 'badge-secondary',
        };
    }

    /**
     * Get formatted total.
     */
    public function getFormattedTotalAttribute(): string
    {
        return '₱' . number_format($this->total_amount, 2);
    }

    /**
     * Generate unique order number.
     */
    public static function generateOrderNumber(): string
    {
        do {
            $number = 'KB-' . strtoupper(substr(md5(uniqid()), 0, 8));
        } while (self::where('order_number', $number)->exists());

        return $number;
    }

    /**
     * Get tracking steps for display.
     */
    public function getTrackingStepsAttribute(): array
    {
        $steps = [
            ['key' => 'pending',    'label' => 'Order Placed',   'icon' => '📦', 'time' => $this->created_at],
            ['key' => 'processing', 'label' => 'Processing',     'icon' => '🔨', 'time' => $this->processing_at],
            ['key' => 'shipped',    'label' => 'Shipped',        'icon' => '🚚', 'time' => $this->shipped_at],
            ['key' => 'delivered',  'label' => 'Delivered',      'icon' => '✅', 'time' => $this->delivered_at],
        ];

        $statusOrder = ['pending', 'processing', 'shipped', 'delivered'];
        $currentIndex = array_search($this->status, $statusOrder);

        foreach ($steps as $index => &$step) {
            $step['completed'] = $index <= $currentIndex;
            $step['current'] = $index === $currentIndex;
        }

        return $steps;
    }
}