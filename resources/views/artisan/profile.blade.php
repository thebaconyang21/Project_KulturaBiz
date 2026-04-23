@extends('layouts.app')

@section('title', $artisan->shop_name ?? $artisan->name)

@section('content')

{{-- Hero Banner --}}
<div class="bg-brand-700 hero-pattern py-16">
    <div class="max-w-5xl mx-auto px-4">
        <div class="flex flex-col md:flex-row items-start gap-8">
            <img src="{{ $artisan->profile_photo_url }}" alt="{{ $artisan->name }}"
                 class="w-28 h-28 rounded-2xl border-4 border-accent object-cover shrink-0">
            <div class="text-white">
                <p class="text-accent text-xs font-bold uppercase tracking-widest mb-2">✦ Artisan Profile</p>
                <h1 class="font-display text-3xl font-bold mb-1">{{ $artisan->shop_name ?? $artisan->name }}</h1>
                <p class="text-brand-200 text-sm mb-3">by {{ $artisan->name }}</p>
                <div class="flex flex-wrap gap-3 text-sm text-brand-100">
                    <span> {{ $artisan->tribe }} Artisan</span>
                    <span> {{ $artisan->region }}</span>
                    <span> {{ $products->total() }} Products</span>
                </div>
                @if($artisan->bio)
                    <p class="mt-4 text-brand-100 max-w-xl text-sm leading-relaxed">{{ $artisan->bio }}</p>
                @endif
            </div>
        </div>
    </div>
</div>

<div class="max-w-5xl mx-auto px-4 py-10">

    {{-- Cultural Stories --}}
    @if($stories->count() > 0)
        <div class="mb-12">
            <h2 class="font-display text-2xl font-bold text-gray-900 mb-5">Cultural Stories</h2>
            <div class="grid grid-cols-1 md:grid-cols-3 gap-4">
                @foreach($stories as $story)
                    <a href="{{ route('cultural.show', $story->slug) }}"
                       class="group bg-brand-700 text-white rounded-2xl p-5 hover:shadow-lg transition hover:-translate-y-1">
                        <div class="text-accent text-xs font-bold uppercase tracking-widest mb-2">{{ $story->tribe_community }}</div>
                        <h3 class="font-display font-bold text-sm leading-tight group-hover:text-accent transition">{{ $story->title }}</h3>
                    </a>
                @endforeach
            </div>
        </div>
    @endif

    {{-- Products --}}
    <div>
        <h2 class="font-display text-2xl font-bold text-gray-900 mb-5">Products by {{ $artisan->name }}</h2>

        @if($products->count() > 0)
            <div class="grid grid-cols-2 md:grid-cols-3 lg:grid-cols-4 gap-5">
                @foreach($products as $product)
                    <a href="{{ route('products.show', $product->slug) }}"
                       class="bg-white rounded-2xl shadow-sm border border-gray-100 overflow-hidden hover:shadow-md hover:-translate-y-1 transition-all group">
                        <div class="h-44 bg-gradient-to-br from-brand-100 to-brand-200">
                            @if($product->images && count($product->images) > 0)
                                <img src="{{ asset('storage/' . $product->images[0]) }}" alt="{{ $product->name }}"
                                     class="w-full h-full object-cover group-hover:scale-105 transition-transform duration-300">
                            @else
                                <div class="w-full h-full flex items-center justify-center text-4xl opacity-30"></div>
                            @endif
                        </div>
                        <div class="p-3">
                            <h3 class="font-medium text-gray-900 text-sm line-clamp-1">{{ $product->name }}</h3>
                            <p class="text-brand-700 font-bold text-sm mt-1">₱{{ number_format($product->price, 2) }}</p>
                        </div>
                    </a>
                @endforeach
            </div>
            <div class="mt-8">{{ $products->links() }}</div>
        @else
            <div class="text-center py-12 text-gray-400">
                <p>No active products yet.</p>
            </div>
        @endif
    </div>
</div>

@endsection
