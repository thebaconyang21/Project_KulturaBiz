<?php

// namespace App\Models;

// use Illuminate\Database\Eloquent\Model;

// class CulturalStory extends Model
// {
//     //
// }



namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class CulturalStory extends Model
{
    use HasFactory;

    protected $fillable = [
        'product_id',
        'user_id',
        'title',
        'slug',
        'story',
        'tribe_community',
        'location',
        'cultural_significance',
        'historical_background',
        'cover_image',
        'gallery_images',
        'is_featured',
        'is_published',
    ];

    protected $casts = [
        'gallery_images' => 'array',
        'is_featured' => 'boolean',
        'is_published' => 'boolean',
    ];

    /**
     * The artisan/author of this story.
     */
    public function author()
    {
        return $this->belongsTo(User::class, 'user_id');
    }

    /**
     * The product linked to this story.
     */
    public function product()
    {
        return $this->belongsTo(Product::class);
    }

    /**
     * Get cover image URL.
     */
    public function getCoverImageUrlAttribute(): string
    {
        if ($this->cover_image) {
            return asset('storage/' . $this->cover_image);
        }
        return asset('images/story-placeholder.jpg');
    }

    /**
     * Scope: only published stories.
     */
    public function scopePublished($query)
    {
        return $query->where('is_published', true);
    }

    /**
     * Scope: featured stories.
     */
    public function scopeFeatured($query)
    {
        return $query->where('is_featured', true);
    }
}