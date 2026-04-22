@extends('layouts.app')

@section('title', $story->title)

@section('content')

<div class="max-w-4xl mx-auto px-4 py-10">

    {{-- Breadcrumb --}}
    <nav class="text-sm text-gray-400 mb-8">
        <a href="{{ route('home') }}" class="hover:text-brand-600">Home</a> /
        <a href="{{ route('cultural.index') }}" class="hover:text-brand-600">Cultural Stories</a> /
        <span class="text-gray-600">{{ $story->title }}</span>
    </nav>

    {{-- Header --}}
    <div class="mb-8">
        <div class="flex flex-wrap gap-2 mb-4">
            <span class="bg-brand-100 text-brand-700 text-xs font-bold px-3 py-1 rounded-full uppercase tracking-wider">
                {{ $story->tribe_community }}
            </span>
            <span class="bg-gray-100 text-gray-600 text-xs px-3 py-1 rounded-full">
                📍 {{ $story->location }}
            </span>
            @if($story->is_featured)
                <span class="bg-accent text-brand-900 text-xs font-bold px-3 py-1 rounded-full">⭐ Featured</span>
            @endif
        </div>

        <h1 class="font-display text-4xl font-bold text-gray-900 leading-tight mb-5">{{ $story->title }}</h1>

        {{-- Author --}}
        <div class="flex items-center gap-3">
            <img src="{{ $story->author->profile_photo_url }}" alt="{{ $story->author->name }}"
                 class="w-12 h-12 rounded-full">
            <div>
                <a href="{{ route('artisan.profile', $story->author->id) }}"
                   class="font-semibold text-gray-900 hover:text-brand-700 transition">
                    {{ $story->author->name }}
                </a>
                <p class="text-sm text-gray-500">{{ $story->author->tribe }} Artisan • {{ $story->author->region }}</p>
            </div>
            <span class="ml-auto text-xs text-gray-400">{{ $story->created_at->format('F d, Y') }}</span>
        </div>
    </div>

    {{-- Cover Image --}}
    @if($story->cover_image)
        <div class="rounded-2xl overflow-hidden mb-8 h-72">
            <img src="{{ asset('storage/' . $story->cover_image) }}" alt="{{ $story->title }}"
                 class="w-full h-full object-cover">
        </div>
    @else
        <div class="bg-gradient-to-br from-brand-700 to-brand-900 rounded-2xl mb-8 h-48 hero-pattern"></div>
    @endif

    {{-- Story Body --}}
    <div class="prose prose-lg max-w-none mb-10">
        <div class="text-gray-700 leading-relaxed text-lg whitespace-pre-line">{{ $story->story }}</div>
    </div>

    {{-- Cultural Details --}}
    <div class="bg-brand-50 border border-brand-100 rounded-2xl p-6 mb-10">
        <h2 class="font-semibold text-brand-800 mb-4">🏛️ Heritage Details</h2>
        <div class="grid grid-cols-1 md:grid-cols-2 gap-4 text-sm">
            @if($story->cultural_significance)
                <div>
                    <p class="font-semibold text-brand-700 mb-1">Cultural Significance</p>
                    <p class="text-gray-600">{{ $story->cultural_significance }}</p>
                </div>
            @endif
            @if($story->historical_background)
                <div>
                    <p class="font-semibold text-brand-700 mb-1">Historical Background</p>
                    <p class="text-gray-600">{{ $story->historical_background }}</p>
                </div>
            @endif
            <div>
                <p class="font-semibold text-brand-700 mb-1">Tribe / Community</p>
                <p class="text-gray-600">{{ $story->tribe_community }}</p>
            </div>
            <div>
                <p class="font-semibold text-brand-700 mb-1">Location in Mindanao</p>
                <p class="text-gray-600">{{ $story->location }}</p>
            </div>
        </div>
    </div>

    {{-- Linked Product --}}
    @if($story->product)
        <div class="bg-white border border-gray-100 rounded-2xl p-5 shadow-sm mb-10">
            <h2 class="font-semibold text-gray-900 mb-4">🎨 See the Craft in the Marketplace</h2>
            <a href="{{ route('products.show', $story->product->slug) }}"
               class="flex items-center gap-4 hover:bg-gray-50 rounded-xl p-3 transition group">
                <div class="w-20 h-20 bg-brand-100 rounded-xl overflow-hidden shrink-0">
                    @if($story->product->images)
                        <img src="{{ asset('storage/' . $story->product->images[0]) }}" class="w-full h-full object-cover">
                    @else
                        <div class="w-full h-full flex items-center justify-center text-3xl opacity-30">🎨</div>
                    @endif
                </div>
                <div>
                    <p class="font-semibold text-gray-900 group-hover:text-brand-700 transition">{{ $story->product->name }}</p>
                    <p class="text-brand-700 font-bold">₱{{ number_format($story->product->price, 2) }}</p>
                </div>
                <span class="ml-auto text-brand-600 text-sm">View Product →</span>
            </a>
        </div>
    @endif

    {{-- Related Stories --}}
    @if($relatedStories->count() > 0)
        <div>
            <h2 class="font-display text-2xl font-bold text-gray-900 mb-6">More from the {{ $story->tribe_community }} People</h2>
            <div class="grid grid-cols-1 md:grid-cols-3 gap-5">
                @foreach($relatedStories as $related)
                    <a href="{{ route('cultural.show', $related->slug) }}"
                       class="group bg-brand-700 text-white rounded-2xl overflow-hidden hover:shadow-lg hover:-translate-y-1 transition-all p-5">
                        <div class="text-accent text-xs font-bold uppercase tracking-widest mb-2">{{ $related->tribe_community }}</div>
                        <h3 class="font-display font-bold leading-tight group-hover:text-accent transition">{{ $related->title }}</h3>
                        <p class="text-brand-300 text-xs mt-3 line-clamp-2">{{ Str::limit($related->story, 100) }}</p>
                    </a>
                @endforeach
            </div>
        </div>
    @endif
</div>

@endsection
