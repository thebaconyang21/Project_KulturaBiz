@extends('layouts.app')

@section('title', 'Add New Product')

@section('content')

<div class="flex min-h-screen">
    <aside class="w-56 bg-brand-800 text-white shrink-0 min-h-screen pt-6 hidden lg:block">
        <div class="px-5 mb-8">
            <p class="font-semibold text-sm">{{ auth()->user()->name }}</p>
            <p class="text-brand-300 text-xs">{{ auth()->user()->shop_name }}</p>
        </div>
        <nav class="space-y-1 px-3">
            @foreach([['artisan.dashboard','','Dashboard'],['artisan.products.index','','My Products'],['artisan.products.create','','Add Product'],['artisan.orders','','Orders']] as [$r,$i,$l])
                <a href="{{ route($r) }}" class="flex items-center gap-3 px-3 py-2 rounded-lg text-sm transition {{ request()->routeIs($r) ? 'bg-brand-700 text-white font-semibold' : 'text-brand-200 hover:bg-brand-700' }}">{{ $i }} {{ $l }}</a>
            @endforeach
        </nav>
    </aside>

    <div class="flex-1 bg-gray-50 p-8">
        <div class="max-w-3xl">
            <div class="flex items-center gap-3 mb-8">
                <a href="{{ route('artisan.products.index') }}" class="text-brand-600 hover:text-brand-800 text-sm">← My Products</a>
                <h1 class="font-display text-2xl font-bold text-gray-900">Add New Product</h1>
            </div>

            @if($errors->any())
                <div class="bg-red-50 border border-red-200 text-red-700 rounded-xl p-4 mb-6 text-sm">
                    @foreach($errors->all() as $error)<p>• {{ $error }}</p>@endforeach
                </div>
            @endif

            <form method="POST" action="{{ route('artisan.products.store') }}" enctype="multipart/form-data" class="space-y-6">
                @csrf

                {{-- Basic Info --}}
                <div class="bg-white rounded-2xl shadow-sm border border-gray-100 p-6 space-y-4">
                    <h2 class="font-semibold text-gray-900">Basic Information</h2>

                    <div>
                        <label class="block text-sm font-medium text-gray-700 mb-1">Product Name *</label>
                        <input type="text" name="name" value="{{ old('name') }}" required
                               class="w-full border border-gray-200 rounded-xl px-4 py-2.5 text-sm focus:outline-none focus:ring-2 focus:ring-brand-400"
                               placeholder="e.g., Traditional Maranao Malong">
                    </div>

                    <div>
                        <label class="block text-sm font-medium text-gray-700 mb-1">Category *</label>
                        <select name="category_id" required
                                class="w-full border border-gray-200 rounded-xl px-4 py-2.5 text-sm focus:outline-none focus:ring-2 focus:ring-brand-400">
                            <option value="">Select a category</option>
                            @foreach($categories as $cat)
                                <option value="{{ $cat->id }}" @selected(old('category_id') == $cat->id)>{{ $cat->name }}</option>
                            @endforeach
                        </select>
                    </div>

                    <div>
                        <label class="block text-sm font-medium text-gray-700 mb-1">Description *</label>
                        <textarea name="description" rows="4" required
                                  class="w-full border border-gray-200 rounded-xl px-4 py-2.5 text-sm focus:outline-none focus:ring-2 focus:ring-brand-400"
                                  placeholder="Describe your product in detail — craftsmanship, size, use, care instructions…">{{ old('description') }}</textarea>
                    </div>

                    <div class="grid grid-cols-2 gap-4">
                        <div>
                            <label class="block text-sm font-medium text-gray-700 mb-1">Price (₱) *</label>
                            <input type="number" name="price" value="{{ old('price') }}" min="1" step="0.01" required
                                   class="w-full border border-gray-200 rounded-xl px-4 py-2.5 text-sm focus:outline-none focus:ring-2 focus:ring-brand-400"
                                   placeholder="e.g., 1500.00">
                        </div>
                        <div>
                            <label class="block text-sm font-medium text-gray-700 mb-1">Stock Quantity *</label>
                            <input type="number" name="stock" value="{{ old('stock', 1) }}" min="0" required
                                   class="w-full border border-gray-200 rounded-xl px-4 py-2.5 text-sm focus:outline-none focus:ring-2 focus:ring-brand-400">
                        </div>
                    </div>
                </div>

                {{-- Product Images --}}
                <div class="bg-white rounded-2xl shadow-sm border border-gray-100 p-6" x-data="{ previews: [] }">
                    <h2 class="font-semibold text-gray-900 mb-4">Product Images</h2>
                    <label class="block border-2 border-dashed border-gray-200 rounded-xl p-8 text-center cursor-pointer hover:border-brand-400 transition">
                        <input type="file" name="images[]" multiple accept="image/*" class="hidden"
                               @change="previews = Array.from($event.target.files).map(f => URL.createObjectURL(f))">
                        <div class="text-4xl mb-2">📸</div>
                        <p class="text-gray-500 text-sm">Click to upload product images</p>
                        <p class="text-gray-400 text-xs mt-1">PNG, JPG, WEBP up to 2MB each. Multiple images supported.</p>
                    </label>
                    {{-- Preview --}}
                    <div class="flex flex-wrap gap-3 mt-4" x-show="previews.length > 0">
                        <template x-for="(src, i) in previews" :key="i">
                            <img :src="src" class="w-20 h-20 object-cover rounded-xl border border-gray-200">
                        </template>
                    </div>
                </div>

                {{-- Cultural Heritage --}}
                <div class="bg-brand-50 border border-brand-100 rounded-2xl p-6 space-y-4">
                    <div>
                        <h2 class="font-semibold text-brand-800">Cultural Heritage Documentation</h2>
                        <p class="text-brand-600 text-xs mt-1">Share the cultural story and heritage of your craft. This helps buyers connect with the deeper meaning of your work.</p>
                    </div>

                    <div>
                        <label class="block text-sm font-medium text-brand-700 mb-1">Origin Location in Mindanao</label>
                        <input type="text" name="origin_location" value="{{ old('origin_location', auth()->user()->region) }}"
                               class="w-full border border-brand-200 rounded-xl px-4 py-2.5 text-sm focus:outline-none focus:ring-2 focus:ring-brand-400 bg-white"
                               placeholder="e.g., Marawi City, Lanao del Sur">
                    </div>

                    <div>
                        <label class="block text-sm font-medium text-brand-700 mb-1">Materials Used</label>
                        <input type="text" name="materials_used" value="{{ old('materials_used') }}"
                               class="w-full border border-brand-200 rounded-xl px-4 py-2.5 text-sm focus:outline-none focus:ring-2 focus:ring-brand-400 bg-white"
                               placeholder="e.g., Abaca fiber, natural dyes, bamboo">
                    </div>

                    <div>
                        <label class="block text-sm font-medium text-brand-700 mb-1">Cultural Background / Story</label>
                        <textarea name="cultural_background" rows="4"
                                  class="w-full border border-brand-200 rounded-xl px-4 py-2.5 text-sm focus:outline-none focus:ring-2 focus:ring-brand-400 bg-white"
                                  placeholder="Tell the story of this craft — its history, tradition, and significance in your community…">{{ old('cultural_background') }}</textarea>
                    </div>

                    <div>
                        <label class="block text-sm font-medium text-brand-700 mb-1">Tribe / Community</label>
                        <input type="text" name="tribe_community" value="{{ old('tribe_community', auth()->user()->tribe) }}"
                               class="w-full border border-brand-200 rounded-xl px-4 py-2.5 text-sm focus:outline-none focus:ring-2 focus:ring-brand-400 bg-white"
                               placeholder="e.g., Maranao, T'boli, Higaonon">
                    </div>
                </div>

                <div class="flex gap-3">
                    <button type="submit"
                            class="bg-brand-700 text-white font-bold px-8 py-3 rounded-xl hover:bg-brand-800 transition hover:shadow-lg">
                         Publish Product
                    </button>
                    <a href="{{ route('artisan.products.index') }}"
                       class="border border-gray-200 text-gray-600 font-semibold px-8 py-3 rounded-xl hover:bg-gray-50 transition">
                        Cancel
                    </a>
                </div>
            </form>
        </div>
    </div>
</div>

@endsection
