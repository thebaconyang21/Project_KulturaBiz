@extends('layouts.app')

@section('title', 'Home')

@section('content')

{{-- HERO SECTION --}}
<section class="relative isolate flex min-h-[590px] items-center overflow-hidden bg-brand-900 text-white sm:min-h-[640px]"
         style="background-image: url('{{ asset('images/hero-bg.jpg') }}'); background-position: center; background-size: cover;">
    <div class="absolute inset-0 -z-10 bg-gradient-to-r from-brand-900/95 via-brand-900/75 to-brand-900/25"></div>
    <div class="absolute inset-0 -z-10 bg-gradient-to-t from-brand-900/50 via-transparent to-brand-900/10"></div>
    <div class="max-w-7xl mx-auto w-full px-5 py-20 sm:px-8 lg:px-10">
        <div class="max-w-3xl">
            <p class="mb-6 inline-flex items-center gap-2 rounded-full border border-accent/40 bg-black/20 px-4 py-2 text-xs font-semibold uppercase tracking-[0.18em] text-accent backdrop-blur-sm">
                <i class="fa-solid fa-sparkles" aria-hidden="true"></i>
                Mindanaoan heritage marketplace
            </p>
            <h1 class="font-display mb-6 text-5xl font-bold leading-[1.08] tracking-tight sm:text-6xl lg:text-7xl">
                Handcrafted with soul.<br>
                <span class="text-accent">Woven with story.</span>
            </h1>
            <p class="mb-9 max-w-2xl text-base leading-7 text-white/85 sm:text-lg sm:leading-8">
                Discover meaningful handmade pieces from Mindanao's artisans. Meet the makers, explore the traditions, and bring home a craft with a story.
            </p>
            <div class="flex flex-col gap-3 sm:flex-row">
                <a href="{{ route('products.index') }}"
                   class="inline-flex items-center justify-center gap-3 rounded-full bg-accent px-7 py-3.5 font-bold text-brand-900 shadow-lg shadow-black/20 transition hover:-translate-y-0.5 hover:bg-yellow-300">
                    Explore the collection <i class="fa-solid fa-arrow-right text-sm" aria-hidden="true"></i>
                </a>
                <a href="{{ route('cultural.index') }}"
                   class="inline-flex items-center justify-center gap-2 rounded-full border border-white/50 bg-white/5 px-7 py-3.5 font-semibold text-white backdrop-blur-sm transition hover:bg-white/15">
                    <i class="fa-solid fa-book-open" aria-hidden="true"></i> Discover the stories
                </a>
            </div>
            <div class="mt-10 flex flex-wrap items-center gap-x-6 gap-y-3 text-xs font-medium text-white/80 sm:text-sm">
                <span class="inline-flex items-center gap-2"><i class="fa-solid fa-heart text-accent" aria-hidden="true"></i> Made by local artisans</span>
                <span class="hidden h-1 w-1 rounded-full bg-accent sm:block"></span>
                <span class="inline-flex items-center gap-2"><i class="fa-solid fa-book-open text-accent" aria-hidden="true"></i> Rooted in living heritage</span>
            </div>
        </div>
    </div>
    <div class="pointer-events-none absolute bottom-0 right-0 hidden h-40 w-40 translate-x-1/4 translate-y-1/4 rounded-full border border-white/15 lg:block"></div>
    <div class="pointer-events-none absolute bottom-8 right-12 hidden h-24 w-24 rounded-full border border-accent/30 lg:block"></div>
</section>

<section class="relative z-10 -mt-7 px-4 sm:px-6 lg:px-8">
    <div class="mx-auto grid max-w-5xl grid-cols-3 divide-x divide-brand-100 rounded-2xl border border-brand-100 bg-white px-2 py-5 shadow-xl shadow-brand-900/10 sm:px-8 sm:py-6">
        <div class="text-center">
            <div class="font-display text-2xl font-bold text-brand-800 sm:text-3xl">{{ number_format($artisanCount) }}</div>
            <div class="mt-1 text-[10px] font-semibold uppercase tracking-wider text-gray-500 sm:text-xs">Artisans</div>
        </div>
        <div class="text-center">
            <div class="font-display text-2xl font-bold text-brand-800 sm:text-3xl">{{ number_format($productCount) }}</div>
            <div class="mt-1 text-[10px] font-semibold uppercase tracking-wider text-gray-500 sm:text-xs">Products</div>
        </div>
        <div class="text-center">
            <div class="font-display text-2xl font-bold text-brand-800 sm:text-3xl">{{ number_format($storyCount) }}</div>
            <div class="mt-1 text-[10px] font-semibold uppercase tracking-wider text-gray-500 sm:text-xs">Cultural stories</div>
        </div>
    </div>
</section>

{{-- CATEGORIES --}}
<section class="mx-auto max-w-7xl px-4 pb-14 pt-20 sm:px-6 lg:px-8">
    <div class="mb-8 flex flex-wrap items-end justify-between gap-4">
        <div>
            <p class="mb-2 text-xs font-bold uppercase tracking-[0.18em] text-brand-600">Find your favorite craft</p>
            <h2 class="font-display text-3xl font-bold text-brand-900 sm:text-4xl">Shop by category</h2>
            <p class="mt-2 text-sm text-gray-500 sm:text-base">Explore traditional crafts from across Mindanao.</p>
        </div>
        <a href="{{ route('products.index') }}" class="inline-flex items-center gap-2 text-sm font-semibold text-brand-700 transition hover:text-brand-900">
            Browse all <i class="fa-solid fa-arrow-right text-xs" aria-hidden="true"></i>
        </a>
    </div>

    <div class="grid grid-cols-2 gap-3 sm:grid-cols-3 sm:gap-4 lg:grid-cols-6">
        @foreach($categories as $category)
            <a href="{{ route('products.index', ['category' => $category->id]) }}"
               class="group rounded-2xl border border-brand-100 bg-white p-4 text-center shadow-sm transition duration-200 hover:-translate-y-1 hover:border-brand-300 hover:shadow-lg sm:p-5">
                <div class="mx-auto mb-4 flex h-14 w-14 items-center justify-center rounded-2xl bg-brand-50 text-brand-700 transition group-hover:bg-brand-700 group-hover:text-accent">
                    <span class="text-xl">
                        @switch($category->slug)
                            @case('textiles')
                                <i class="fa-solid fa-shirt" aria-hidden="true"></i>
                                @break
                            @case('baskets')
                                <i class="fa-solid fa-basket-shopping" aria-hidden="true"></i>
                                @break
                            @case('accessories')
                                <i class="fa-solid fa-gem" aria-hidden="true"></i>
                                @break
                            @case('woodcrafts')
                                <i class="fa-solid fa-hammer" aria-hidden="true"></i>
                                @break
                            @case('bags')
                                <i class="fa-solid fa-bag-shopping" aria-hidden="true"></i>
                                @break
                            @case('homedecor')
                                <i class="fa-solid fa-house-chimney" aria-hidden="true"></i>
                                @break
                            @default
                                <i class="fa-solid fa-paintbrush" aria-hidden="true"></i>
                        @endswitch
                    </span>
                </div>
                <div class="text-sm font-semibold text-brand-900 group-hover:text-brand-700">{{ $category->name }}</div>
                <div class="mt-1 text-xs text-gray-500">{{ $category->active_products_count }} {{ \Illuminate\Support\Str::plural('item', $category->active_products_count) }}</div>
            </a>
        @endforeach
    </div>
</section>

{{-- FEATURED PRODUCTS --}}
<section class="border-y border-brand-100 bg-[#fbf8f2] py-16">
    <div class="mx-auto max-w-7xl px-4 sm:px-6 lg:px-8">
        <div class="mb-8 flex flex-wrap items-end justify-between gap-4">
            <div>
                <p class="mb-2 text-xs font-bold uppercase tracking-[0.18em] text-brand-600">Made with meaning</p>
                <h2 class="font-display text-3xl font-bold text-brand-900 sm:text-4xl">Featured finds</h2>
                <p class="mt-2 text-sm text-gray-500 sm:text-base">Handpicked creations from our artisan community.</p>
            </div>
            <a href="{{ route('products.index') }}" class="inline-flex items-center gap-2 text-sm font-semibold text-brand-700 transition hover:text-brand-900">
                View all products <i class="fa-solid fa-arrow-right text-xs" aria-hidden="true"></i>
            </a>
        </div>

        <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-4 gap-6">
            @foreach($featuredProducts as $product)
                <a href="{{ route('products.show', $product->slug) }}"
                       class="group overflow-hidden rounded-2xl border border-brand-100 bg-white shadow-sm transition duration-300 hover:-translate-y-1 hover:shadow-xl">

                    {{-- Product Image --}}
                    <div class="relative aspect-[4/3] overflow-hidden bg-gradient-to-br from-brand-100 to-brand-200">
                        @if($product->images && count($product->images) > 0)
                            <img src="{{ asset('storage/' . $product->images[0]) }}"
                                 alt="{{ $product->name }}"
                                 class="h-full w-full object-cover transition duration-500 group-hover:scale-105">
                        @else
                            <div class="w-full h-full flex items-center justify-center">
                                <i class="fa-solid fa-image text-4xl text-brand-400/60" aria-hidden="true"></i>
                            </div>
                        @endif
                        {{-- Category badge --}}
                        <span class="absolute left-3 top-3 rounded-full bg-white/95 px-3 py-1 text-[11px] font-semibold text-brand-800 shadow-sm">
                            {{ $product->category->name }}
                        </span>
                    </div>

                    <div class="p-5">
                        <h3 class="mb-1 line-clamp-1 font-display text-lg font-bold text-brand-900 transition group-hover:text-brand-700">{{ $product->name }}</h3>
                        <p class="mb-3 text-xs text-gray-500">Made by <span class="font-medium text-gray-700">{{ $product->artisan->name }}</span></p>

                        {{-- Rating --}}
                        @if($product->review_count > 0)
                            <div class="flex items-center gap-1 mb-2">
                                <i class="fa-solid fa-star text-amber-500 text-xs" aria-hidden="true"></i>
                                <span class="text-xs font-semibold text-gray-700">{{ number_format($product->average_rating, 1) }}</span>
                                <span class="text-gray-400 text-xs">({{ $product->review_count }})</span>
                            </div>
                        @endif

                        <div class="mt-4 flex items-center justify-between border-t border-brand-50 pt-3">
                            <span class="text-lg font-bold text-brand-800">₱{{ number_format($product->price, 2) }}</span>
                            @if($product->stock <= 5 && $product->stock > 0)
                                <span class="rounded-full bg-amber-50 px-2.5 py-1 text-[11px] font-semibold text-amber-700">Only {{ $product->stock }} left</span>
                            @endif
                        </div>
                    </div>
                </a>
            @endforeach
        </div>
    </div>
</section>

{{-- CULTURAL STORIES TEASER --}}
<section class="mx-auto max-w-7xl px-4 py-16 sm:px-6 lg:px-8">
    <div class="mb-8 flex flex-wrap items-end justify-between gap-4">
        <div>
            <p class="mb-2 text-xs font-bold uppercase tracking-[0.18em] text-brand-600">More than what meets the eye</p>
            <h2 class="font-display text-3xl font-bold text-brand-900 sm:text-4xl">Stories in every stitch</h2>
            <p class="mt-2 text-sm text-gray-500 sm:text-base">Meet the traditions and people behind the craft.</p>
        </div>
        <a href="{{ route('cultural.index') }}" class="inline-flex items-center gap-2 text-sm font-semibold text-brand-700 transition hover:text-brand-900">
            Explore all stories <i class="fa-solid fa-arrow-right text-xs" aria-hidden="true"></i>
        </a>
    </div>

    <div class="grid grid-cols-1 gap-5 md:grid-cols-3">
        @foreach($featuredStories as $story)
            <a href="{{ route('cultural.show', $story->slug) }}"
               class="group overflow-hidden rounded-2xl border border-brand-100 bg-white shadow-sm transition duration-300 hover:-translate-y-1 hover:shadow-xl">
                <div class="relative h-52 overflow-hidden bg-gradient-to-br from-brand-700 to-brand-900">
                    @if($story->cover_image)
                        <img src="{{ asset('storage/' . $story->cover_image) }}" alt="{{ $story->title }}"
                             class="h-full w-full object-cover transition duration-500 group-hover:scale-105">
                    @else
                        <div class="flex h-full items-center justify-center text-accent/70">
                            <i class="fa-solid fa-book-open text-5xl" aria-hidden="true"></i>
                        </div>
                    @endif
                    <div class="absolute inset-0 bg-gradient-to-t from-black/90 via-black/20 to-transparent"></div>
                    <div class="absolute inset-x-0 bottom-0 p-5">
                        <p class="text-xs font-semibold uppercase tracking-wider text-accent">{{ $story->tribe_community }}</p>
                        <h3 class="mt-2 font-display text-xl font-bold leading-tight text-white group-hover:text-accent">{{ $story->title }}</h3>
                    </div>
                </div>
                <div class="p-5">
                    <p class="line-clamp-3 text-sm leading-6 text-gray-600">{{ Str::limit(strip_tags($story->story), 150) }}</p>
                    <div class="mt-4 flex items-center gap-2 border-t border-brand-50 pt-4 text-xs text-gray-500">
                        <img src="{{ $story->author->profile_photo_url }}" alt="" class="h-7 w-7 rounded-full object-cover">
                        <span class="font-medium text-gray-700">{{ $story->author->name }}</span>
                        <span class="ml-auto"><i class="fa-solid fa-location-dot mr-1 text-brand-500" aria-hidden="true"></i>{{ $story->location }}</span>
                    </div>
                </div>
            </a>
        @endforeach
    </div>
</section>

@guest
{{-- CTA SECTION — only show to visitors not yet logged in --}}
<section class="px-4 pb-16 sm:px-6 lg:px-8">
    <div class="relative mx-auto max-w-6xl overflow-hidden rounded-[2rem] bg-accent px-6 py-12 text-center sm:px-12 sm:py-16">
        <div class="pointer-events-none absolute -right-10 -top-20 h-64 w-64 rounded-full border border-brand-900/10"></div>
        <div class="pointer-events-none absolute -right-1 top-0 h-40 w-40 rounded-full border border-brand-900/10"></div>
        <div class="relative mx-auto max-w-3xl">
        <p class="mb-3 text-xs font-bold uppercase tracking-[0.2em] text-brand-800">Your craft has a place here</p>
        <h2 class="font-display mb-4 text-3xl font-bold text-brand-900 sm:text-4xl">Are you a Mindanaoan artisan?</h2>
        <p class="mx-auto mb-8 max-w-2xl text-sm leading-7 text-brand-800 sm:text-base">
            Join KulturaBiz and bring your handmade creations to customers across the Philippines and beyond. Share your story, sell your craft, and preserve your heritage.
        </p>
        <a href="{{ route('register') }}"
           class="inline-flex items-center gap-3 rounded-full bg-brand-900 px-8 py-3.5 font-bold text-white shadow-lg transition hover:-translate-y-0.5 hover:bg-brand-800">
            Start your artisan journey <i class="fa-solid fa-arrow-right text-sm" aria-hidden="true"></i>
        </a>
        </div>
    </div>
</section>
@endguest

@endsection