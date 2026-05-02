@extends('layouts.app')

@section('title', 'Home')

@section('content')

{{-- HERO SECTIONnnnnn --}}
<section class="text-white py-24 relative overflow-hidden" style="background: url('{{ asset('images/hero-bg.jpg') }}') center center / cover no-repeat;">
    <div class="absolute inset-0 bg-gradient-to-br from-brand-900/80 to-brand-600/60"></div>
    <div class="max-w-7xl mx-auto px-4 relative z-10">
        <div class="max-w-3xl">
            <p class="text-accent font-medium tracking-widest uppercase text-sm mb-4">✦ Mindanaoan Heritage Marketplace</p>
            <h1 class="font-display text-5xl md:text-6xl font-bold leading-tight mb-6">
                Handcrafted with Soul,<br>
                <span class="text-accent">Woven with Story</span>
            </h1>
            <p class="text-brand-100 text-lg leading-relaxed mb-8 max-w-xl">
                Discover authentic handmade products from Mindanao's indigenous artisans. Every purchase preserves a centuries-old tradition and directly supports the communities that created them.
            </p>
            <div class="flex flex-wrap gap-4">
                <a href="{{ route('products.index') }}"
                   class="bg-accent text-brand-900 font-bold px-8 py-3 rounded-full hover:bg-yellow-400 transition-all hover:shadow-lg">
                    Shop Now
                </a>
                <a href="{{ route('cultural.index') }}"
                   class="border border-white/40 text-white px-8 py-3 rounded-full hover:bg-white/10 transition-all">
                    Explore Stories
                </a>
            </div>
        </div>
    </div>
    
    <div class="absolute right-0 top-0 bottom-0 w-1/3 opacity-10 hidden lg:block">
        <svg viewBox="0 0 400 600" class="w-full h-full">
            <path d="M200,50 C300,100 380,200 350,300 C320,400 250,450 200,550 C150,450 80,400 50,300 C20,200 100,100 200,50 Z" fill="currentColor" opacity="0.3"/>
            <circle cx="200" cy="300" r="120" fill="none" stroke="currentColor" stroke-width="3" opacity="0.4"/>
            <circle cx="200" cy="300" r="80" fill="none" stroke="currentColor" stroke-width="2" opacity="0.3"/>
        </svg>
    </div>
</section>


<section class="bg-brand-800 text-white py-6">
    <div class="max-w-7xl mx-auto px-4">
        <div class="grid grid-cols-3 gap-4 text-center">
            <div>
                <div class="font-display text-3xl font-bold text-accent">{{ $artisanCount }}+</div>
                <div class="text-brand-200 text-sm mt-1">Artisans</div>
            </div>
            <div>
                <div class="font-display text-3xl font-bold text-accent">{{ $productCount }}+</div>
                <div class="text-brand-200 text-sm mt-1">Products</div>
            </div>
            <div>
                <div class="font-display text-3xl font-bold text-accent">{{ $storyCount }}+</div>
                <div class="text-brand-200 text-sm mt-1">Cultural Stories</div>
            </div>
        </div>
    </div>
</section>

{{-- CATEGORIES --}}
<section class="py-16 max-w-7xl mx-auto px-4">
    <h2 class="font-display text-3xl font-bold text-brand-800 mb-2">Shop by Category</h2>
    <p class="text-gray-500 mb-8">Explore traditional crafts from across Mindanao</p>

    <div class="grid grid-cols-2 md:grid-cols-3 lg:grid-cols-6 gap-4">
        @foreach($categories as $category)
            <a href="{{ route('products.index', ['category' => $category->id]) }}"
               class="group bg-white rounded-2xl p-4 text-center shadow-sm border border-gray-100 hover:border-brand-400 hover:shadow-md transition-all duration-200">
                <div class="w-12 h-12 bg-brand-50 rounded-full flex items-center justify-center mx-auto mb-3 group-hover:bg-brand-100 transition">
                    <span class="text-2xl">
                        @switch($category->slug)
                            @case('textiles')
                                <i class="fa-solid fa-shirt text-brand-600 text-2xl"></i>
                                @break
                            @case('baskets')
                                <i class="fa-solid fa-basket-shopping text-brand-600 text-2xl"></i>
                                @break
                            @case('accessories')
                                <i class="fa-solid fa-gem text-brand-600 text-2xl"></i>
                                @break
                            @case('woodcrafts')
                                <i class="fa-solid fa-hammer text-brand-600 text-2xl"></i>
                                @break
                            @case('bags')
                                <i class="fa-solid fa-bag-shopping text-brand-600 text-2xl"></i>
                                @break
                            @case('homedecor')
                                <i class="fa-solid fa-house-chimney text-brand-600 text-2xl"></i>
                                @break
                            @default
                                <i class="fa-solid fa-paintbrush text-brand-600 text-2xl"></i>
                        @endswitch
                    </span>
                </div>
                <div class="text-sm font-semibold text-brand-800 group-hover:text-brand-600">{{ $category->name }}</div>
                <div class="text-xs text-gray-400 mt-1">{{ $category->active_products_count }} items</div>
            </a>
        @endforeach
    </div>
</section>

{{-- FEATURED PRODUCTS --}}
<section class="py-12 bg-gray-50">
    <div class="max-w-7xl mx-auto px-4">
        <div class="flex items-end justify-between mb-8">
            <div>
                <h2 class="font-display text-3xl font-bold text-brand-800 mb-1">Featured Products</h2>
                <p class="text-gray-500">Handpicked authentic creations from our artisans</p>
            </div>
            <a href="{{ route('products.index') }}" class="text-brand-600 font-medium hover:text-brand-800 text-sm">
                View all →
            </a>
        </div>

        <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-4 gap-6">
            @foreach($featuredProducts as $product)
                <a href="{{ route('products.show', $product->slug) }}"
                   class="bg-white rounded-2xl shadow-sm border border-gray-100 overflow-hidden hover:shadow-lg hover:-translate-y-1 transition-all duration-200 group">

                    {{-- Product Image --}}
                    <div class="h-52 bg-gradient-to-br from-brand-100 to-brand-200 relative overflow-hidden">
                        @if($product->images && count($product->images) > 0)
                            <img src="{{ asset('storage/' . $product->images[0]) }}"
                                 alt="{{ $product->name }}"
                                 class="w-full h-full object-cover group-hover:scale-105 transition-transform duration-300">
                        @else
                            <div class="w-full h-full flex items-center justify-center">
                                <span class="text-6xl opacity-40">🎨</span>
                            </div>
                        @endif
                        {{-- Category badge --}}
                        <span class="absolute top-3 left-3 bg-white/90 text-brand-700 text-xs font-semibold px-2 py-1 rounded-full">
                            {{ $product->category->name }}
                        </span>
                    </div>

                    <div class="p-4">
                        <h3 class="font-semibold text-gray-900 mb-1 line-clamp-1">{{ $product->name }}</h3>
                        <p class="text-xs text-gray-500 mb-2">by {{ $product->artisan->name }}</p>

                        {{-- Rating --}}
                        @if($product->review_count > 0)
                            <div class="flex items-center gap-1 mb-2">
                                <i class="fa-solid fa-star text-accent text-sm"></i><!-- <span class="text-accent text-sm">★</span> -->
                                <span class="text-sm font-medium">{{ number_format($product->average_rating, 1) }}</span>
                                <span class="text-gray-400 text-xs">({{ $product->review_count }})</span>
                            </div>
                        @endif

                        <div class="flex items-center justify-between mt-3">
                            <span class="font-bold text-brand-700 text-lg">₱{{ number_format($product->price, 2) }}</span>
                            @if($product->stock <= 5 && $product->stock > 0)
                                <span class="text-xs text-orange-500 font-medium">Only {{ $product->stock }} left!</span>
                            @endif
                        </div>
                    </div>
                </a>
            @endforeach
        </div>
    </div>
</section>

{{-- CULTURAL STORIES TEASER --}}
<section class="py-16 max-w-7xl mx-auto px-4">
    <div class="flex items-end justify-between mb-8">
        <div>
            <h2 class="font-display text-3xl font-bold text-brand-800 mb-1">Cultural Stories</h2>
            <p class="text-gray-500">The heritage and history behind every handcrafted piece</p>
        </div>
        <a href="{{ route('cultural.index') }}" class="text-brand-600 font-medium hover:text-brand-800 text-sm">
            Read all stories →
        </a>
    </div>

    <div class="grid grid-cols-1 md:grid-cols-3 gap-6">
        @foreach($featuredStories as $story)
            <a href="{{ route('cultural.show', $story->slug) }}"
               class="group bg-brand-700 text-white rounded-2xl overflow-hidden hover:shadow-xl transition-all duration-200 hover:-translate-y-1">
                <div class="p-6">
                    <div class="text-accent text-xs font-bold tracking-widest uppercase mb-3">
                        {{ $story->tribe_community }} • {{ $story->location }}
                    </div>
                    <h3 class="font-display text-xl font-bold mb-3 group-hover:text-accent transition-colors leading-tight">
                        {{ $story->title }}
                    </h3>
                    <p class="text-brand-200 text-sm leading-relaxed line-clamp-3">
                        {{ Str::limit($story->story, 150) }}
                    </p>
                    <div class="mt-4 flex items-center gap-2 text-brand-300 text-sm">
                        <img src="{{ $story->author->profile_photo_url }}" alt="" class="w-6 h-6 rounded-full">
                        {{ $story->author->name }}
                    </div>
                </div>
            </a>
        @endforeach
    </div>
</section>

{{-- CTA SECTION --}}
<section class="bg-accent py-16">
    <div class="max-w-4xl mx-auto px-4 text-center">
        <h2 class="font-display text-4xl font-bold text-brand-900 mb-4">Are You a Mindanaoan Artisan?</h2>
        <p class="text-brand-800 text-lg mb-8 max-w-2xl mx-auto">
            Join KulturaBiz and bring your handmade creations to customers across the Philippines and beyond. Share your story, sell your craft, and preserve your heritage.
        </p>
        <a href="{{ route('register') }}"
           class="bg-brand-800 text-white font-bold px-10 py-4 rounded-full hover:bg-brand-900 transition-all hover:shadow-xl inline-block">
            Register as an Artisan
        </a>
    </div>
</section>

@endsection