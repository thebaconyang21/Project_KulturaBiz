<?php

namespace App\Http\Controllers;

use App\Models\Product;
use App\Models\Category;
use App\Models\CulturalStory;
use App\Models\User;
use Illuminate\Http\Request;

/**
 * HomeController
 * Handles public-facing pages: homepage, product listing, cultural stories.
 */
class HomeController extends Controller
{
    /**
     * Show the homepage with featured products and stories.
     */
    public function index()
    {
        $featuredProducts = Product::with(['artisan', 'category'])
            ->active()
            ->latest()
            ->take(8)
            ->get();

        $categories = Category::where('is_active', true)
            ->withCount(['products' => fn($q) => $q->where('status', 'active')])
            ->get();

        $featuredStories = CulturalStory::with('author')
            ->published()
            ->featured()
            ->latest()
            ->take(3)
            ->get();

        $artisanCount = User::where('role', 'artisan')->where('status', 'approved')->count();
        $productCount = Product::where('status', 'active')->count();
        $storyCount   = CulturalStory::where('is_published', true)->count();

        return view('home', compact(
            'featuredProducts',
            'categories',
            'featuredStories',
            'artisanCount',
            'productCount',
            'storyCount'
        ));
    }

    /**
     * Browse all products with search & filter.
     */
    public function products(Request $request)
    {
        $query = Product::with(['artisan', 'category'])->active();

        // Search
        if ($request->filled('search')) {
            $query->search($request->search);
        }

        // Category filter
        if ($request->filled('category')) {
            $query->where('category_id', $request->category);
        }

        // Price filter
        if ($request->filled('min_price')) {
            $query->where('price', '>=', $request->min_price);
        }
        if ($request->filled('max_price')) {
            $query->where('price', '<=', $request->max_price);
        }

        // Sort
        match ($request->sort) {
            'price_asc'  => $query->orderBy('price', 'asc'),
            'price_desc' => $query->orderBy('price', 'desc'),
            'rating'     => $query->orderBy('average_rating', 'desc'),
            default      => $query->latest(),
        };

        $products   = $query->paginate(12)->withQueryString();
        $categories = Category::where('is_active', true)->get();

        return view('products.index', compact('products', 'categories'));
    }

    /**
     * Show a single product detail page.
     */
    public function productShow(string $slug)
    {
        $product = Product::with(['artisan', 'category', 'culturalStory', 'reviews.customer'])
            ->where('slug', $slug)
            ->firstOrFail();

        $relatedProducts = Product::with(['artisan', 'category'])
            ->active()
            ->where('category_id', $product->category_id)
            ->where('id', '!=', $product->id)
            ->take(4)
            ->get();

        return view('products.show', compact('product', 'relatedProducts'));
    }

    /**
     * List all cultural stories.
     */
    public function culturalStories(Request $request)
    {
        $query = CulturalStory::with('author')->published();

        if ($request->filled('tribe')) {
            $query->where('tribe_community', $request->tribe);
        }

        $stories = $query->latest()->paginate(9)->withQueryString();
        $tribes  = CulturalStory::published()->distinct()->pluck('tribe_community');

        return view('cultural.index', compact('stories', 'tribes'));
    }

    /**
     * Show a single cultural story.
     */
    public function culturalStoryShow(string $slug)
    {
        $story = CulturalStory::with(['author', 'product'])
            ->where('slug', $slug)
            ->published()
            ->firstOrFail();

        $relatedStories = CulturalStory::published()
            ->where('tribe_community', $story->tribe_community)
            ->where('id', '!=', $story->id)
            ->take(3)
            ->get();

        return view('cultural.show', compact('story', 'relatedStories'));
    }

    /**
     * Artisan public profile page.
     */
    public function artisanProfile(string $id)
    {
        $artisan = User::where('id', $id)
            ->where('role', 'artisan')
            ->where('status', 'approved')
            ->firstOrFail();

        $products = Product::with('category')
            ->where('user_id', $artisan->id)
            ->active()
            ->paginate(8);

        $stories = CulturalStory::where('user_id', $artisan->id)
            ->published()
            ->latest()
            ->take(3)
            ->get();

        return view('artisan.profile', compact('artisan', 'products', 'stories'));
    }
}