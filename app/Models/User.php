<?php

// namespace App\Models;

// // use Illuminate\Contracts\Auth\MustVerifyEmail;
// use Database\Factories\UserFactory;
// use Illuminate\Database\Eloquent\Factories\HasFactory;
// use Illuminate\Foundation\Auth\User as Authenticatable;
// use Illuminate\Notifications\Notifiable;

// class User extends Authenticatable
// {
//     /** @use HasFactory<UserFactory> */
//     use HasFactory, Notifiable;

//     /**
//      * The attributes that are mass assignable.
//      *
//      * @var list<string>
//      */
//     protected $fillable = [
//         'name',
//         'email',
//         'password',
//     ];

//     /**
//      * The attributes that should be hidden for serialization.
//      *
//      * @var list<string>
//      */
//     protected $hidden = [
//         'password',
//         'remember_token',
//     ];

//     /**
//      * Get the attributes that should be cast.
//      *
//      * @return array<string, string>
//      */
//     protected function casts(): array
//     {
//         return [
//             'email_verified_at' => 'datetime',
//             'password' => 'hashed',
//         ];
//     }
// }



namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;

class User extends Authenticatable
{
    use HasFactory, Notifiable;

    /**
     * The attributes that are mass assignable.
     */
    protected $fillable = [
        'name',
        'email',
        'password',
        'role',
        'phone',
        'address',
        'profile_photo',
        'bio',
        'status',
        'shop_name',
        'tribe',
        'region',
    ];

    /**
     * The attributes that should be hidden for serialization.
     */
    protected $hidden = [
        'password',
        'remember_token',
    ];

    /**
     * The attributes that should be cast.
     */
    protected function casts(): array
    {
        return [
            'email_verified_at' => 'datetime',
            'password' => 'hashed',
        ];
    }

    // ==========================================
    // ROLE HELPER METHODS
    // ==========================================

    /**
     * Check if user is an admin.
     */
    public function isAdmin(): bool
    {
        return $this->role === 'admin';
    }

    /**
     * Check if user is an artisan (seller).
     */
    public function isArtisan(): bool
    {
        return $this->role === 'artisan';
    }

    /**
     * Check if user is a customer (buyer).
     */
    public function isCustomer(): bool
    {
        return $this->role === 'customer';
    }

    /**
     * Check if artisan account is approved.
     */
    public function isApproved(): bool
    {
        return $this->status === 'approved';
    }

    // ==========================================
    // RELATIONSHIPS
    // ==========================================

    /**
     * Products created by this artisan.
     */
    public function products()
    {
        return $this->hasMany(Product::class);
    }

    /**
     * Orders placed by this customer.
     */
    public function orders()
    {
        return $this->hasMany(Order::class);
    }

    /**
     * Cultural stories authored by this user.
     */
    public function culturalStories()
    {
        return $this->hasMany(CulturalStory::class);
    }

    /**
     * Reviews written by this user.
     */
    public function reviews()
    {
        return $this->hasMany(Review::class);
    }

    // ==========================================
    // HELPER METHODS
    // ==========================================

    /**
     * Get the profile photo URL or a placeholder.
     */
    public function getProfilePhotoUrlAttribute(): string
    {
        if ($this->profile_photo) {
            return asset('storage/' . $this->profile_photo);
        }
        return 'https://ui-avatars.com/api/?name=' . urlencode($this->name) . '&background=8B4513&color=fff';
    }

    /**
     * Get total sales amount for this artisan.
     */
    public function getTotalSalesAttribute(): float
    {
        return OrderItem::whereHas('product', function ($q) {
            $q->where('user_id', $this->id);
        })->whereHas('order', function ($q) {
            $q->where('status', 'delivered');
        })->sum('subtotal');
    }
}
