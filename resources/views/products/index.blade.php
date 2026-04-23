@extends('layouts.app')

@section('title', 'Browse Products')

@section('content')

<div class="max-w-7xl mx-auto px-4 py-8">

    {{-- Header --}}
    <div class="mb-8">
        <h1 class="font-display text-3xl font-bold text-brand-800 mb-1">Browse Products</h1>
        <p class="text-gray-500">{{ $products->total() }} authentic handcrafted products from Mindanaoan artisans</p>
    </div>

    <div class="flex flex-col lg:flex-row gap-8">

        {{-- SIDEBAR FILTERS --}}
        <aside class="w-full lg:w-64 shrink-0">
            <form method="GET" action="{{ route('products.index') }}" class="bg-white rounded-2xl shadow-sm border border-gray-100 p-5 sticky top-20">
                <h3 class="font-semibold text-gray-800 mb-4">Filter Products</h3>

                {{-- Search --}}
                <div class="mb-5">
                    <label class="block text-sm font-medium text-gray-700 mb-1">Search</label>
                    <input type="text" name="search" value="{{ request('search') }}"
                           placeholder="Malong, basket, necklace…"
                           class="w-full border border-gray-200 rounded-lg px-3 py-2 text-sm focus:outline-none focus:ring-2 focus:ring-brand-400">
                </div>

                {{-- Category --}}
                <div class="mb-5">
                    <label class="block text-sm font-medium text-gray-700 mb-1">Category</label>
                    <select name="category" class="w-full border border-gray-200 rounded-lg px-3 py-2 text-sm focus:outline-none focus:ring-2 focus:ring-brand-400">
                        <option value="">All Categories</option>
                        @foreach($categories as $cat)
                            <option value="{{ $cat->id }}" @selected(request('category') == $cat->id)>{{ $cat->name }}</option>
                        @endforeach
                    </select>
                </div>

                {{-- Price Range --}}
                <div class="mb-5">
                    <label class="block text-sm font-medium text-gray-700 mb-1">Price Range</label>
                    <div class="flex gap-2">
                        <input type="number" name="min_price" value="{{ request('min_price') }}" placeholder="Min"
                               class="w-full border border-gray-200 rounded-lg px-3 py-2 text-sm focus:outline-none focus:ring-2 focus:ring-brand-400">
                        <input type="number" name="max_price" value="{{ request('max_price') }}" placeholder="Max"
                               class="w-full border border-gray-200 rounded-lg px-3 py-2 text-sm focus:outline-none focus:ring-2 focus:ring-brand-400">
                    </div>
                </div>

                {{-- Sort --}}
                <div class="mb-5">
                    <label class="block text-sm font-medium text-gray-700 mb-1">Sort By</label>
                    <select name="sort" class="w-full border border-gray-200 rounded-lg px-3 py-2 text-sm focus:outline-none focus:ring-2 focus:ring-brand-400">
                        <option value="" @selected(!request('sort'))>Newest First</option>
                        <option value="price_asc" @selected(request('sort') === 'price_asc')>Price: Low to High</option>
                        <option value="price_desc" @selected(request('sort') === 'price_desc')>Price: High to Low</option>
                        <option value="rating" @selected(request('sort') === 'rating')>Top Rated</option>
                    </select>
                </div>

                <button type="submit" class="w-full bg-brand-700 text-white rounded-lg py-2 text-sm font-semibold hover:bg-brand-800 transition">
                    Apply Filters
                </button>
                @if(request()->anyFilled(['search', 'category', 'min_price', 'max_price', 'sort']))
                    <a href="{{ route('products.index') }}" class="block text-center text-sm text-gray-500 hover:text-red-500 mt-2">Clear Filters</a>
                @endif
            </form>
        </aside>

        {{-- PRODUCT GRID --}}
        <div class="flex-1">
            @if($products->count() > 0)
                <div class="grid grid-cols-1 sm:grid-cols-2 xl:grid-cols-3 gap-6">
                    @foreach($products as $product)
                        <a href="{{ route('products.show', $product->slug) }}"
                           class="bg-white rounded-2xl shadow-sm border border-gray-100 overflow-hidden hover:shadow-lg hover:-translate-y-1 transition-all duration-200 group">

                            <div class="h-52 bg-gradient-to-br from-brand-100 to-brand-200 relative overflow-hidden">
                                @if($product->images && count($product->images) > 0)
                                    <img src="{{ asset('storage/' . $product->images[0]) }}"
                                         alt="{{ $product->name }}"
                                         class="w-full h-full object-cover group-hover:scale-105 transition-transform duration-300">
                                @else
                                    <div class="w-full h-full flex items-center justify-center">
                                        <span class="text-6xl opacity-30"></span>
                                    </div>
                                @endif
                                <span class="absolute top-3 left-3 bg-white/90 text-brand-700 text-xs font-semibold px-2 py-1 rounded-full">
                                    {{ $product->category->name }}
                                </span>
                                @if($product->stock === 0)
                                    <div class="absolute inset-0 bg-black/40 flex items-center justify-center">
                                        <span class="bg-white text-gray-800 font-bold text-sm px-4 py-1 rounded-full">Out of Stock</span>
                                    </div>
                                @endif
                            </div>

                            <div class="p-4">
                                <h3 class="font-semibold text-gray-900 mb-1 line-clamp-1 group-hover:text-brand-700 transition">{{ $product->name }}</h3>
                                <p class="text-xs text-gray-400 mb-1">by {{ $product->artisan->name }}</p>
                                @if($product->origin_location)
                                    <p class="text-xs text-brand-500 mb-2">📍 {{ $product->origin_location }}</p>
                                @endif

                                @if($product->review_count > 0)
                                    <div class="flex items-center gap-1 mb-2">
                                        <span class="text-accent text-sm">★</span>
                                        <span class="text-sm font-medium text-gray-700">{{ number_format($product->average_rating, 1) }}</span>
                                        <span class="text-gray-400 text-xs">({{ $product->review_count }} reviews)</span>
                                    </div>
                                @endif

                                <div class="flex items-center justify-between mt-3 pt-3 border-t border-gray-50">
                                    <span class="font-bold text-brand-700 text-lg">₱{{ number_format($product->price, 2) }}</span>
                                    @if($product->stock > 0 && $product->stock <= 5)
                                        <span class="text-xs text-orange-500 font-medium">{{ $product->stock }} left</span>
                                    @endif
                                </div>
                            </div>
                        </a>
                    @endforeach
                </div>

                {{-- Pagination --}}
                <div class="mt-10">
                    {{ $products->links() }}
                </div>

            @else
                <div class="text-center py-24 text-gray-400">
                    <div class="text-6xl mb-4">🔍</div>
                    <h3 class="text-xl font-semibold mb-2">No products found</h3>
                    <p class="text-sm">Try adjusting your search or filters.</p>
                    <a href="{{ route('products.index') }}" class="mt-4 inline-block text-brand-600 hover:underline text-sm">Clear all filters</a>
                </div>
            @endif
        </div>
    </div>
</div>

@endsection