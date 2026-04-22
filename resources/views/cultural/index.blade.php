@extends('layouts.app')

@section('title', 'Cultural Stories')

@section('content')

{{-- Hero --}}
<section class="bg-brand-800 hero-pattern text-white py-16">
    <div class="max-w-7xl mx-auto px-4 text-center">
        <p class="text-accent text-xs font-bold tracking-widest uppercase mb-3">✦ Heritage Documentation</p>
        <h1 class="font-display text-4xl font-bold mb-3">Cultural Stories of Mindanao</h1>
        <p class="text-brand-200 max-w-xl mx-auto text-sm leading-relaxed">
            Each product carries centuries of tradition. Explore the living heritage of Mindanao's indigenous peoples — their craft, their stories, their wisdom.
        </p>
    </div>
</section>

<div class="max-w-7xl mx-auto px-4 py-12">

    {{-- Tribe Filter --}}
    @if($tribes->count() > 0)
        <div class="flex flex-wrap gap-2 mb-8">
            <a href="{{ route('cultural.index') }}"
               class="px-4 py-1.5 rounded-full text-sm font-medium transition {{ !request('tribe') ? 'bg-brand-700 text-white' : 'bg-white border border-gray-200 text-gray-600 hover:border-brand-400' }}">
               All Communities
            </a>
            @foreach($tribes as $tribe)
                <a href="{{ route('cultural.index', ['tribe' => $tribe]) }}"
                   class="px-4 py-1.5 rounded-full text-sm font-medium transition capitalize {{ request('tribe') === $tribe ? 'bg-brand-700 text-white' : 'bg-white border border-gray-200 text-gray-600 hover:border-brand-400' }}">
                    {{ $tribe }}
                </a>
            @endforeach
        </div>
    @endif

    {{-- Stories Grid --}}
    @if($stories->count() > 0)
        <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-6">
            @foreach($stories as $story)
                <a href="{{ route('cultural.show', $story->slug) }}"
                   class="group bg-white rounded-2xl shadow-sm border border-gray-100 overflow-hidden hover:shadow-lg hover:-translate-y-1 transition-all duration-200">

                    {{-- Story Cover --}}
                    <div class="h-48 bg-gradient-to-br from-brand-700 to-brand-900 relative overflow-hidden">
                        @if($story->cover_image)
                            <img src="{{ asset('storage/' . $story->cover_image) }}" alt="{{ $story->title }}"
                                 class="w-full h-full object-cover opacity-70 group-hover:opacity-80 transition">
                        @endif
                        <div class="absolute inset-0 p-5 flex flex-col justify-end">
                            <span class="text-accent text-xs font-bold tracking-widest uppercase">
                                {{ $story->tribe_community }}
                            </span>
                        </div>
                        @if($story->is_featured)
                            <span class="absolute top-3 right-3 bg-accent text-brand-900 text-xs font-bold px-2 py-0.5 rounded-full">
                                ⭐ Featured
                            </span>
                        @endif
                    </div>

                    <div class="p-5">
                        <h2 class="font-display text-lg font-bold text-gray-900 mb-2 group-hover:text-brand-700 transition leading-tight">
                            {{ $story->title }}
                        </h2>
                        <p class="text-gray-500 text-sm line-clamp-3 mb-4">
                            {{ Str::limit(strip_tags($story->story), 160) }}
                        </p>
                        <div class="flex items-center gap-2 text-xs text-gray-400 border-t border-gray-50 pt-3">
                            <img src="{{ $story->author->profile_photo_url }}" alt="" class="w-6 h-6 rounded-full">
                            {{ $story->author->name }}
                            <span class="mx-1">•</span>
                            📍 {{ $story->location }}
                        </div>
                    </div>
                </a>
            @endforeach
        </div>

        <div class="mt-10">{{ $stories->links() }}</div>
    @else
        <div class="text-center py-20 text-gray-400">
            <div class="text-6xl mb-4">📖</div>
            <h3 class="text-xl font-semibold mb-2">No stories found</h3>
            <p class="text-sm">Be the first artisan to share your cultural story!</p>
        </div>
    @endif
</div>

@endsection
