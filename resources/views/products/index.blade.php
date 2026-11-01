@extends('layouts.app')

@section('title', 'Browse Products')

@section('content')

<div class="mx-auto max-w-7xl px-4 py-8 sm:px-6 sm:py-10 lg:px-8">

    {{-- Header --}}
    <div class="relative mb-8 overflow-hidden rounded-3xl bg-brand-900 px-6 py-8 text-white sm:px-10 sm:py-10">
        <div class="pointer-events-none absolute -right-8 -top-20 h-64 w-64 rounded-full border border-white/10"></div>
        <div class="pointer-events-none absolute -right-2 -top-14 h-48 w-48 rounded-full border border-accent/20"></div>
        <div class="relative">
            <p class="mb-3 inline-flex items-center gap-2 text-xs font-semibold uppercase tracking-[0.18em] text-accent">
                <i class="fa-solid fa-sparkles" aria-hidden="true"></i> Thoughtfully made in Mindanao
            </p>
            <div class="flex flex-col gap-5 sm:flex-row sm:items-end sm:justify-between">
                <div>
                    <h1 class="font-display text-3xl font-bold sm:text-4xl">Find something meaningful.</h1>
                    <p class="mt-2 max-w-2xl text-sm leading-6 text-brand-100 sm:text-base">
                        Discover handmade pieces, meet the artisans, and find the story behind every craft.
                    </p>
                </div>
                <div class="shrink-0 rounded-2xl border border-white/15 bg-white/10 px-4 py-3 backdrop-blur-sm">
                    <p class="font-display text-2xl font-bold">{{ number_format($products->total()) }}</p>
                    <p class="text-xs text-brand-100">{{ \Illuminate\Support\Str::plural('handcrafted find', $products->total()) }}</p>
                </div>
            </div>
        </div>
    </div>

    <div class="flex flex-col gap-6 lg:flex-row lg:gap-8">

        {{-- SIDEBAR FILTERS --}}
        <aside class="w-full shrink-0 lg:w-72">
            <form method="GET" action="{{ route('products.index') }}" x-data="{ filtersOpen: false }" class="rounded-2xl border border-brand-100 bg-white p-5 shadow-sm lg:sticky lg:top-24">
                <div class="mb-5 flex items-center justify-between">
                    <h2 class="hidden items-center gap-2 font-semibold text-gray-900 lg:flex">
                        <i class="fa-solid fa-sliders text-brand-600" aria-hidden="true"></i> Refine your search
                    </h2>
                    <button type="button" @click="filtersOpen = !filtersOpen" :aria-expanded="filtersOpen.toString()"
                            class="inline-flex items-center gap-2 font-semibold text-gray-900 lg:hidden">
                        <i class="fa-solid fa-sliders text-brand-600" aria-hidden="true"></i>
                        Filters and sort
                        <i :class="filtersOpen ? 'fa-chevron-up' : 'fa-chevron-down'" class="fa-solid ml-1 text-xs text-gray-400" aria-hidden="true"></i>
                    </button>
                    @if(request()->anyFilled(['search', 'category', 'min_price', 'max_price', 'sort']))
                        <a href="{{ route('products.index') }}" class="text-xs font-semibold text-brand-700 hover:text-brand-900">Reset</a>
                    @endif
                </div>

                <div x-cloak x-show="filtersOpen" x-transition class="lg:!block">
                {{-- Search --}}
                <div class="mb-5">
                    <label for="product-search" class="mb-1.5 block text-xs font-semibold uppercase tracking-wide text-gray-600">Search crafts</label>
                    <div class="relative">
                        <i class="fa-solid fa-magnifying-glass absolute left-3.5 top-1/2 -translate-y-1/2 text-sm text-gray-400" aria-hidden="true"></i>
                        <input id="product-search" type="search" name="search" value="{{ request('search') }}"
                               placeholder="Try “malong” or “basket”"
                               class="w-full rounded-xl border border-gray-200 bg-gray-50 py-3 pl-10 pr-3 text-sm transition placeholder:text-gray-400 focus:border-brand-400 focus:bg-white focus:outline-none focus:ring-2 focus:ring-brand-100">
                    </div>
                </div>

                {{-- Category --}}
                <div class="mb-5">
                    <label for="product-category" class="mb-1.5 block text-xs font-semibold uppercase tracking-wide text-gray-600">Category</label>
                    <select id="product-category" name="category" class="w-full rounded-xl border border-gray-200 bg-gray-50 px-3 py-3 text-sm transition focus:border-brand-400 focus:bg-white focus:outline-none focus:ring-2 focus:ring-brand-100">
                        <option value="">All Categories</option>
                        @foreach($categories as $cat)
                            <option value="{{ $cat->id }}" @selected(request('category') == $cat->id)>{{ $cat->name }}</option>
                        @endforeach
                    </select>
                </div>

                {{-- Price Range --}}
                <div class="mb-5">
                    <label class="mb-1.5 block text-xs font-semibold uppercase tracking-wide text-gray-600">Price range <span class="font-normal normal-case tracking-normal text-gray-400">(₱)</span></label>
                    <div class="flex gap-2">
                        <input type="number" name="min_price" value="{{ request('min_price') }}" min="0" step="0.01" aria-label="Minimum price" placeholder="Min"
                               class="w-full min-w-0 rounded-xl border border-gray-200 bg-gray-50 px-3 py-3 text-sm transition placeholder:text-gray-400 focus:border-brand-400 focus:bg-white focus:outline-none focus:ring-2 focus:ring-brand-100">
                        <input type="number" name="max_price" value="{{ request('max_price') }}" min="0" step="0.01" aria-label="Maximum price" placeholder="Max"
                               class="w-full min-w-0 rounded-xl border border-gray-200 bg-gray-50 px-3 py-3 text-sm transition placeholder:text-gray-400 focus:border-brand-400 focus:bg-white focus:outline-none focus:ring-2 focus:ring-brand-100">
                    </div>
                </div>

                {{-- Sort --}}
                <div class="mb-5">
                    <label for="product-sort" class="mb-1.5 block text-xs font-semibold uppercase tracking-wide text-gray-600">Sort by</label>
                    <select id="product-sort" name="sort" class="w-full rounded-xl border border-gray-200 bg-gray-50 px-3 py-3 text-sm transition focus:border-brand-400 focus:bg-white focus:outline-none focus:ring-2 focus:ring-brand-100">
                        <option value="" @selected(!request('sort'))>Newest First</option>
                        <option value="price_asc" @selected(request('sort') === 'price_asc')>Price: Low to High</option>
                        <option value="price_desc" @selected(request('sort') === 'price_desc')>Price: High to Low</option>
                        <option value="rating" @selected(request('sort') === 'rating')>Top Rated</option>
                    </select>
                </div>

                <button type="submit" class="flex w-full items-center justify-center gap-2 rounded-xl bg-brand-800 py-3 text-sm font-semibold text-white shadow-sm transition hover:bg-brand-900 focus:outline-none focus:ring-2 focus:ring-brand-400 focus:ring-offset-2">
                    <i class="fa-solid fa-sliders text-xs" aria-hidden="true"></i> Show {{ $products->total() }} {{ \Illuminate\Support\Str::plural('result', $products->total()) }}
                </button>
                </div>
            </form>
        </aside>

        {{-- PRODUCT GRID --}}
        <div class="flex-1">
            <div class="mb-5 flex flex-wrap items-center justify-between gap-2">
                <p class="text-sm text-gray-500">
                    @if($products->total() > 0)
                        Showing <span class="font-semibold text-gray-800">{{ $products->firstItem() }}–{{ $products->lastItem() }}</span>
                        of <span class="font-semibold text-gray-800">{{ $products->total() }}</span> finds
                    @else
                        No matching finds
                    @endif
                </p>
                @if(request()->anyFilled(['search', 'category', 'min_price', 'max_price', 'sort']))
                    <a href="{{ route('products.index') }}" class="inline-flex items-center gap-1 text-xs font-semibold text-brand-700 hover:text-brand-900">
                        <i class="fa-solid fa-xmark" aria-hidden="true"></i> Clear filters
                    </a>
                @endif
            </div>
            @if($products->count() > 0)
                <div class="grid grid-cols-1 gap-5 sm:grid-cols-2 xl:grid-cols-3">
                    @foreach($products as $product)
                        <a href="{{ route('products.show', $product->slug) }}"
                           class="group flex h-full flex-col overflow-hidden rounded-2xl border border-brand-100 bg-white shadow-sm transition duration-300 hover:-translate-y-1 hover:border-brand-200 hover:shadow-xl focus:outline-none focus:ring-2 focus:ring-brand-500 focus:ring-offset-2">

                            <div class="relative aspect-[4/3] overflow-hidden bg-gradient-to-br from-brand-100 to-brand-200">
                                @if($product->images && count($product->images) > 0)
                                    <img src="{{ asset('storage/' . $product->images[0]) }}"
                                         alt="{{ $product->name }}"
                                         loading="lazy"
                                         class="h-full w-full object-cover transition duration-500 group-hover:scale-105">
                                @else
                                    <div class="flex h-full items-center justify-center text-brand-500/60">
                                        <i class="fa-solid fa-image text-4xl" aria-hidden="true"></i>
                                    </div>
                                @endif
                                <span class="absolute left-3 top-3 rounded-full bg-white/95 px-3 py-1 text-[11px] font-semibold text-brand-800 shadow-sm">
                                    {{ $product->category->name }}
                                </span>
                                @if($product->stock > 0 && $product->stock <= 5)
                                    <span class="absolute right-3 top-3 rounded-full bg-amber-50 px-3 py-1 text-[11px] font-semibold text-amber-800 shadow-sm">
                                        Only {{ $product->stock }} left
                                    </span>
                                @elseif($product->stock === 0)
                                    <span class="absolute right-3 top-3 rounded-full bg-gray-900/80 px-3 py-1 text-[11px] font-semibold text-white">Sold out</span>
                                @endif
                            </div>

                            <div class="flex flex-1 flex-col p-4 sm:p-5">
                                <p class="mb-1 text-xs text-gray-500">Made by <span class="font-medium text-gray-700">{{ $product->artisan->name }}</span></p>
                                <h2 class="line-clamp-1 font-display text-lg font-bold text-brand-900 transition group-hover:text-brand-700">{{ $product->name }}</h2>
                                @if($product->origin_location)
                                    <p class="mt-2 flex items-center gap-1.5 text-xs text-gray-500">
                                        <i class="fa-solid fa-location-dot text-brand-500" aria-hidden="true"></i> {{ $product->origin_location }}
                                    </p>
                                @else
                                    <div class="mt-2 h-4"></div>
                                @endif

                                @if($product->review_count > 0)
                                    <div class="mt-3 flex items-center gap-1.5">
                                        <i class="fa-solid fa-star text-xs text-amber-500" aria-hidden="true"></i>
                                        <span class="text-xs font-semibold text-gray-700">{{ number_format($product->average_rating, 1) }}</span>
                                        <span class="text-xs text-gray-400">({{ $product->review_count }} {{ \Illuminate\Support\Str::plural('review', $product->review_count) }})</span>
                                    </div>
                                @else
                                    <div class="mt-3 text-xs text-gray-400">New artisan find</div>
                                @endif

                                <div class="mt-auto flex items-end justify-between border-t border-brand-50 pt-4">
                                    <div>
                                        <p class="text-[10px] font-semibold uppercase tracking-wider text-gray-400">Price</p>
                                        <span class="font-display text-xl font-bold text-brand-800">₱{{ number_format($product->price, 2) }}</span>
                                    </div>
                                    <span class="inline-flex items-center gap-1.5 text-xs font-semibold text-brand-700 transition group-hover:text-brand-900">
                                        View craft <i class="fa-solid fa-arrow-right text-[10px] transition group-hover:translate-x-0.5" aria-hidden="true"></i>
                                    </span>
                                </div>
                            </div>
                        </a>
                    @endforeach
                </div>

                {{-- Pagination --}}
                <div class="mt-8 rounded-2xl border border-brand-100 bg-white px-4 py-3">
                    {{ $products->links() }}
                </div>

            @else
                <div class="rounded-3xl border border-dashed border-brand-200 bg-white px-6 py-20 text-center">
                    <div class="mx-auto mb-5 flex h-16 w-16 items-center justify-center rounded-2xl bg-brand-50 text-brand-600">
                        <i class="fa-solid fa-magnifying-glass text-2xl" aria-hidden="true"></i>
                    </div>
                    <h3 class="mb-2 font-display text-2xl font-bold text-brand-900">No finds just yet</h3>
                    <p class="mx-auto max-w-md text-sm leading-6 text-gray-500">Try another search or loosen your filters. There may be a special piece waiting for you.</p>
                    <a href="{{ route('products.index') }}" class="mt-6 inline-flex items-center gap-2 rounded-full bg-brand-800 px-5 py-2.5 text-sm font-semibold text-white transition hover:bg-brand-900">
                        <i class="fa-solid fa-rotate-left text-xs" aria-hidden="true"></i> Show all products
                    </a>
                </div>
            @endif
        </div>
    </div>
</div>

@endsection