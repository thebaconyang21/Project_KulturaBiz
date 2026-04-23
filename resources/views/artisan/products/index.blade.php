@extends('layouts.app')

@section('title', 'My Products')

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
        <div class="flex items-center justify-between mb-8">
            <h1 class="font-display text-2xl font-bold text-gray-900">My Products</h1>
            <a href="{{ route('artisan.products.create') }}"
               class="bg-brand-700 text-white font-bold px-5 py-2.5 rounded-xl hover:bg-brand-800 transition">
                Add Product
            </a>
        </div>

        @if($products->count() > 0)
            <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-3 gap-5">
                @foreach($products as $product)
                    <div class="bg-white rounded-2xl shadow-sm border border-gray-100 overflow-hidden hover:shadow-md transition">
                        <div class="h-44 bg-gradient-to-br from-brand-100 to-brand-200 relative">
                            @if($product->images && count($product->images) > 0)
                                <img src="{{ asset('storage/' . $product->images[0]) }}" alt="{{ $product->name }}"
                                     class="w-full h-full object-cover">
                            @else
                                <div class="w-full h-full flex items-center justify-center text-5xl opacity-30"></div>
                            @endif
                            <span class="{{ match($product->status) { 'active' => 'badge-success', 'inactive' => 'badge-secondary', default => 'badge-warning' } }} absolute top-3 right-3">
                                {{ ucfirst($product->status) }}
                            </span>
                        </div>
                        <div class="p-4">
                            <h3 class="font-semibold text-gray-900 mb-1 truncate">{{ $product->name }}</h3>
                            <p class="text-xs text-gray-400 mb-2">{{ $product->category->name }}</p>
                            <div class="flex items-center justify-between mb-4">
                                <span class="font-bold text-brand-700">₱{{ number_format($product->price, 2) }}</span>
                                <span class="text-xs text-gray-500">Stock: {{ $product->stock }}</span>
                            </div>
                            <div class="flex gap-2">
                                <a href="{{ route('artisan.products.edit', $product->id) }}"
                                   class="flex-1 text-center bg-brand-50 text-brand-700 font-semibold text-sm py-2 rounded-lg hover:bg-brand-100 transition">
                                    Edit
                                </a>
                                <a href="{{ route('products.show', $product->slug) }}"
                                   class="flex-1 text-center bg-gray-50 text-gray-600 font-semibold text-sm py-2 rounded-lg hover:bg-gray-100 transition">
                                    👁 View
                                </a>
                                <form method="POST" action="{{ route('artisan.products.destroy', $product->id) }}"
                                      onsubmit="return confirm('Delete {{ $product->name }}?')">
                                    @csrf @method('DELETE')
                                    <button class="bg-red-50 text-red-500 font-semibold text-sm px-3 py-2 rounded-lg hover:bg-red-100 transition">
                                        🗑
                                    </button>
                                </form>
                            </div>
                        </div>
                    </div>
                @endforeach
            </div>
            <div class="mt-8">{{ $products->links() }}</div>
        @else
            <div class="text-center py-24">
                <div class="text-7xl mb-4"></div>
                <h3 class="text-xl font-semibold text-gray-600 mb-2">No products yet</h3>
                <p class="text-gray-400 mb-6">Start sharing your handcrafted products with the world!</p>
                <a href="{{ route('artisan.products.create') }}"
                   class="bg-brand-700 text-white font-bold px-8 py-3 rounded-full hover:bg-brand-800 transition">
                    Create Your First Product
                </a>
            </div>
        @endif
    </div>
</div>

@endsection
