<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;

class User extends Authenticatable
{
    use HasFactory, Notifiable;

    protected $fillable = [
        'name',
        'email',
        'password',
        'role',
        'phone',
        'address',
        'profile_photo',
        'cover_photo',
        'bio',
        'status',
        'shop_name',
        'tribe',
        'region',
        'facebook_url',
        'instagram_url',
        'years_of_experience',
        'craft_specialization',
    ];

    protected $hidden = [
        'password',
        'remember_token',
    ];

    protected function casts(): array
    {
        return [
            'email_verified_at' => 'datetime',
            'password'          => 'hashed',
        ];
    }

    // ─── Role Helpers ──────────────────────────────────────────

    public function isAdmin(): bool    { return $this->role === 'admin'; }
    public function isArtisan(): bool  { return $this->role === 'artisan'; }
    public function isCustomer(): bool { return $this->role === 'customer'; }
    public function isApproved(): bool { return $this->status === 'approved'; }

    // ─── Relationships ─────────────────────────────────────────

    public function products()
    {
        return $this->hasMany(Product::class);
    }

    public function orders()
    {
        return $this->hasMany(Order::class);
    }

    public function culturalStories()
    {
        return $this->hasMany(CulturalStory::class);
    }

    public function reviews()
    {
        return $this->hasMany(Review::class);
    }

    // ─── Computed Attributes ───────────────────────────────────

    /**
     * Profile photo URL — returns uploaded image or a generated avatar.
     */
    public function getProfilePhotoUrlAttribute(): string
    {
        if ($this->profile_photo) {
            return asset('storage/' . $this->profile_photo);
        }

        // UI Avatars — generates a nice letter avatar with brand colors
        return 'https://ui-avatars.com/api/?name=' . urlencode($this->name)
             . '&background=6B3A2A&color=F5EDD8&size=256&bold=true&rounded=true';
    }

    /**
     * Cover photo URL — returns uploaded image or a default gradient banner.
     */
    public function getCoverPhotoUrlAttribute(): string
    {
        if ($this->cover_photo) {
            return asset('storage/' . $this->cover_photo);
        }

        return ''; // handled in blade with CSS gradient fallback
    }

    /**
     * Total sales for this artisan (delivered orders only).
     */
    public function getTotalSalesAttribute(): float
    {
        return OrderItem::whereHas('product', fn($q) => $q->where('user_id', $this->id))
            ->whereHas('order', fn($q) => $q->where('status', 'delivered'))
            ->sum('subtotal');
    }

    /**
     * Total number of products sold.
     */
    public function getTotalProductsSoldAttribute(): int
    {
        return OrderItem::whereHas('product', fn($q) => $q->where('user_id', $this->id))
            ->whereHas('order', fn($q) => $q->where('status', 'delivered'))
            ->sum('quantity');
    }

    /**
     * Average rating across all artisan's products.
     */
    public function getAverageRatingAttribute(): float
    {
        $avg = Product::where('user_id', $this->id)
            ->where('average_rating', '>', 0)
            ->avg('average_rating');

        return round((float) $avg, 1);
    }
}
