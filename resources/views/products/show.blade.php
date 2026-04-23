@extends('layouts.app')

@section('title', $product->name)

@section('content')

<div class="max-w-7xl mx-auto px-4 py-8">

    {{-- Breadcrumb --}}
    <nav class="text-sm text-gray-400 mb-6">
        <a href="{{ route('home') }}" class="hover:text-brand-600">Home</a> /
        <a href="{{ route('products.index') }}" class="hover:text-brand-600">Products</a> /
        <a href="{{ route('products.index', ['category' => $product->category_id]) }}" class="hover:text-brand-600">{{ $product->category->name }}</a> /
        <span class="text-gray-600">{{ $product->name }}</span>
    </nav>

    <div class="grid grid-cols-1 lg:grid-cols-2 gap-12 mb-16">

        {{-- PRODUCT IMAGES --}}
        <div x-data="{ activeImage: 0 }">
            <div class="bg-gradient-to-br from-brand-100 to-brand-200 rounded-2xl overflow-hidden aspect-square mb-4">
                @if($product->images && count($product->images) > 0)
                    @foreach($product->images as $i => $image)
                        <img src="{{ asset('storage/' . $image) }}"
                             alt="{{ $product->name }}"
                             x-show="activeImage === {{ $i }}"
                             class="w-full h-full object-cover">
                    @endforeach
                @else
                    <div class="w-full h-full flex items-center justify-center">
                        <span class="text-9xl opacity-20"></span>
                    </div>
                @endif
            </div>
            {{-- Thumbnails --}}
            @if($product->images && count($product->images) > 1)
                <div class="flex gap-3">
                    @foreach($product->images as $i => $image)
                        <button @click="activeImage = {{ $i }}"
                                :class="activeImage === {{ $i }} ? 'ring-2 ring-brand-500' : 'opacity-60'"
                                class="w-20 h-20 rounded-lg overflow-hidden transition">
                            <img src="{{ asset('storage/' . $image) }}" alt="" class="w-full h-full object-cover">
                        </button>
                    @endforeach
                </div>
            @endif
        </div>

        {{-- PRODUCT DETAILS --}}
        <div>
            <div class="flex items-center gap-2 mb-2">
                <span class="badge-secondary">{{ $product->category->name }}</span>
                @if($product->stock > 0)
                    <span class="badge-success">In Stock ({{ $product->stock }} available)</span>
                @else
                    <span class="badge-danger">Out of Stock</span>
                @endif
            </div>

            <h1 class="font-display text-3xl font-bold text-gray-900 mb-3">{{ $product->name }}</h1>

            {{-- Rating --}}
            @if($product->review_count > 0)
                <div class="flex items-center gap-2 mb-4">
                    <div class="flex text-accent">
                        @for($i = 1; $i <= 5; $i++)
                            <span>{{ $i <= round($product->average_rating) ? '★' : '☆' }}</span>
                        @endfor
                    </div>
                    <span class="font-medium">{{ number_format($product->average_rating, 1) }}</span>
                    <span class="text-gray-400 text-sm">({{ $product->review_count }} reviews)</span>
                </div>
            @endif

            <div class="text-3xl font-bold text-brand-700 mb-6">₱{{ number_format($product->price, 2) }}</div>

            <p class="text-gray-600 leading-relaxed mb-6">{{ $product->description }}</p>

            {{-- Origin info --}}
            <div class="bg-brand-50 rounded-xl p-4 mb-6 space-y-2">
                @if($product->origin_location)
                    <p class="text-sm text-gray-700"><span class="font-semibold text-brand-700"> Origin:</span> {{ $product->origin_location }}</p>
                @endif
                @if($product->materials_used)
                    <p class="text-sm text-gray-700"><span class="font-semibold text-brand-700"> Materials:</span> {{ $product->materials_used }}</p>
                @endif
            </div>

            {{-- Artisan info --}}
            <a href="{{ route('artisan.profile', $product->artisan->id) }}"
               class="flex items-center gap-3 p-4 bg-white border border-gray-100 rounded-xl mb-6 hover:border-brand-300 transition group">
                <img src="{{ $product->artisan->profile_photo_url }}" alt="{{ $product->artisan->name }}"
                     class="w-12 h-12 rounded-full">
                <div>
                    <div class="font-semibold text-gray-900 group-hover:text-brand-700">{{ $product->artisan->name }}</div>
                    <div class="text-xs text-gray-500">{{ $product->artisan->shop_name ?? 'Artisan' }} • {{ $product->artisan->tribe }}</div>
                </div>
                <span class="ml-auto text-brand-500 text-sm">View Profile →</span>
            </a>

            {{-- Add to Cart --}}
            @if($product->isInStock())
                @auth
                    @if(auth()->user()->isCustomer())
                        <form method="POST" action="{{ route('cart.add', $product->id) }}" class="flex gap-3">
                            @csrf
                            <input type="number" name="quantity" value="1" min="1" max="{{ $product->stock }}"
                                   class="w-20 border border-gray-200 rounded-lg px-3 py-3 text-center focus:outline-none focus:ring-2 focus:ring-brand-400">
                            <button type="submit"
                                    class="flex-1 bg-brand-700 text-white font-bold py-3 rounded-xl hover:bg-brand-800 transition-all hover:shadow-lg">
                                 Add to Cart
                            </button>
                        </form>
                    @endif
                @else
                    <a href="{{ route('login') }}"
                       class="block w-full text-center bg-brand-700 text-white font-bold py-3 rounded-xl hover:bg-brand-800 transition">
                        Login to Purchase
                    </a>
                @endauth
            @else
                <button disabled class="w-full bg-gray-200 text-gray-500 font-bold py-3 rounded-xl cursor-not-allowed">
                    Out of Stock
                </button>
            @endif

            {{-- Delivery info --}}
            <div class="mt-4 text-xs text-gray-500 space-y-1">
                <p> Estimated delivery: 5-10 business days</p>
                <p>Cash on Delivery available</p>
                <p>Secure checkout</p>
            </div>
        </div>
    </div>

    {{-- CULTURAL STORY SECTION --}}
    @if($product->cultural_background || $product->culturalStory)
        <div class="bg-brand-700 text-white rounded-2xl p-8 mb-12 relative overflow-hidden">
            <div class="absolute inset-0 hero-pattern opacity-20"></div>
            <div class="relative z-10 max-w-3xl">
                <p class="text-accent text-xs font-bold tracking-widest uppercase mb-3">✦ Cultural Heritage Documentation</p>
                <h2 class="font-display text-2xl font-bold mb-4">The Story Behind This Craft</h2>
                <p class="text-brand-100 leading-relaxed mb-4">{{ $product->cultural_background }}</p>
                @if($product->culturalStory)
                    <a href="{{ route('cultural.show', $product->culturalStory->slug) }}"
                       class="inline-block border border-accent text-accent px-5 py-2 rounded-full text-sm font-medium hover:bg-accent hover:text-brand-900 transition">
                        Read Full Cultural Story →
                    </a>
                @endif
            </div>
        </div>
    @endif

    {{-- REVIEWS SECTION --}}
    <div class="mb-12">
        <h2 class="font-display text-2xl font-bold text-gray-900 mb-6">Customer Reviews</h2>

        @if($product->reviews->count() > 0)
            <div class="space-y-4">
                @foreach($product->reviews as $review)
                    <div class="bg-white rounded-xl p-5 border border-gray-100 shadow-sm">
                        <div class="flex items-center gap-3 mb-3">
                            <img src="{{ $review->customer->profile_photo_url }}" alt="" class="w-9 h-9 rounded-full">
                            <div>
                                <div class="font-semibold text-sm">{{ $review->customer->name }}</div>
                                <div class="text-accent text-sm">{{ $review->stars }}</div>
                            </div>
                            <span class="ml-auto text-xs text-gray-400">{{ $review->created_at->diffForHumans() }}</span>
                        </div>
                        @if($review->title)
                            <h4 class="font-semibold text-sm mb-1">{{ $review->title }}</h4>
                        @endif
                        @if($review->comment)
                            <p class="text-gray-600 text-sm">{{ $review->comment }}</p>
                        @endif
                    </div>
                @endforeach
            </div>
        @else
            <div class="text-center py-10 text-gray-400">
                <div class="text-4xl mb-2">⭐</div>
                <p>No reviews yet. Be the first to review this product!</p>
            </div>
        @endif
    </div>

    {{-- RELATED PRODUCTS --}}
    @if($relatedProducts->count() > 0)
        <div>
            <h2 class="font-display text-2xl font-bold text-gray-900 mb-6">You May Also Like</h2>
            <div class="grid grid-cols-2 md:grid-cols-4 gap-5">
                @foreach($relatedProducts as $related)
                    <a href="{{ route('products.show', $related->slug) }}"
                       class="bg-white rounded-xl shadow-sm border border-gray-100 overflow-hidden hover:shadow-md transition group">
                        <div class="h-36 bg-gradient-to-br from-brand-100 to-brand-200">
                            @if($related->images && count($related->images) > 0)
                                <img src="{{ asset('storage/' . $related->images[0]) }}" alt="{{ $related->name }}"
                                     class="w-full h-full object-cover group-hover:scale-105 transition-transform">
                            @else
                                <div class="w-full h-full flex items-center justify-center text-4xl opacity-30"></div>
                            @endif
                        </div>
                        <div class="p-3">
                            <div class="font-medium text-sm line-clamp-1">{{ $related->name }}</div>
                            <div class="font-bold text-brand-700 text-sm mt-1">₱{{ number_format($related->price, 2) }}</div>
                        </div>
                    </a>
                @endforeach
            </div>
        </div>
    @endif

</div>

@endsection