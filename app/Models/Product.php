<?php

// namespace App\Models;

// use Illuminate\Database\Eloquent\Model;

// class Product extends Model
// {
//     //
// }

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Product extends Model
{
    use HasFactory;

    protected $fillable = [
        'user_id',
        'category_id',
        'name',
        'slug',
        'description',
        'price',
        'stock',
        'images',
        'cultural_background',
        'origin_location',
        'materials_used',
        'status',
        'average_rating',
        'review_count',
    ];

    protected $casts = [
        'images' => 'array',
        'price' => 'float',
        'average_rating' => 'float',
    ];

    // ==========================================
    // RELATIONSHIPS
    // ==========================================

    /**
     * The artisan who created this product.
     */
    public function artisan()
    {
        return $this->belongsTo(User::class, 'user_id');
    }

    /**
     * The category this product belongs to.
     */
    public function category()
    {
        return $this->belongsTo(Category::class);
    }

    /**
     * Order items containing this product.
     */
    public function orderItems()
    {
        return $this->hasMany(OrderItem::class);
    }

    /**
     * Cultural story linked to this product.
     */
    public function culturalStory()
    {
        return $this->hasOne(CulturalStory::class);
    }

    /**
     * Reviews for this product.
     */
    public function reviews()
    {
        return $this->hasMany(Review::class);
    }

    // ==========================================
    // HELPER METHODS
    // ==========================================

    /**
     * Get the primary (first) image URL.
     */
    public function getPrimaryImageAttribute(): string
    {
        if ($this->images && count($this->images) > 0) {
            return asset('storage/' . $this->images[0]);
        }
        return asset('images/product-placeholder.png');
    }

    /**
     * Check if product is in stock.
     */
    public function isInStock(): bool
    {
        return $this->stock > 0 && $this->status === 'active';
    }

    /**
     * Get formatted price.
     */
    public function getFormattedPriceAttribute(): string
    {
        return '₱' . number_format($this->price, 2);
    }

    /**
     * Update average rating after a new review.
     */
    public function updateRating(): void
    {
        $avg = $this->reviews()->where('is_approved', true)->avg('rating');
        $count = $this->reviews()->where('is_approved', true)->count();
        
        $this->update([
            'average_rating' => round($avg, 2),
            'review_count' => $count,
        ]);
    }

    /**
     * Scope: only active products.
     */
    public function scopeActive($query)
    {
        return $query->where('status', 'active');
    }

    /**
     * Scope: search by name or description.
     */
    public function scopeSearch($query, string $term)
    {
        return $query->where(function ($q) use ($term) {
            $q->where('name', 'like', "%{$term}%")
              ->orWhere('description', 'like', "%{$term}%")
              ->orWhere('origin_location', 'like', "%{$term}%");
        });
    }
}