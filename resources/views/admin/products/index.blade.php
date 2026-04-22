@extends('layouts.app')

@section('title', 'Manage Products')

@section('content')

<div class="flex min-h-screen">
    <aside class="w-56 bg-brand-900 text-white shrink-0 min-h-screen pt-6 hidden lg:block">
        <div class="px-5 mb-8">
            <p class="text-brand-300 text-xs font-bold uppercase tracking-widest mb-1">Administration</p>
            <p class="text-white font-semibold text-sm">{{ auth()->user()->name }}</p>
        </div>
        <nav class="space-y-1 px-3">
            @foreach([['admin.dashboard','','Dashboard'],['admin.users','','Users'],['admin.products','','Products'],['admin.categories','','Categories'],['admin.orders','','Orders']] as [$r,$i,$l])
                <a href="{{ route($r) }}" class="flex items-center gap-3 px-3 py-2 rounded-lg text-sm transition {{ request()->routeIs($r) ? 'bg-brand-700 text-white font-semibold' : 'text-brand-200 hover:bg-brand-800' }}">{{ $i }} {{ $l }}</a>
            @endforeach
        </nav>
    </aside>

    <div class="flex-1 bg-gray-50 p-8">
        <h1 class="font-display text-2xl font-bold text-gray-900 mb-8">Manage Products</h1>

        {{-- Filter bar --}}
        <form method="GET" class="bg-white rounded-2xl border border-gray-100 shadow-sm p-5 mb-6 flex flex-wrap gap-3">
            <input type="text" name="search" value="{{ request('search') }}" placeholder="Search products…"
                   class="border border-gray-200 rounded-lg px-3 py-2 text-sm focus:outline-none focus:ring-2 focus:ring-brand-400 w-56">
            <select name="category" class="border border-gray-200 rounded-lg px-3 py-2 text-sm focus:outline-none focus:ring-2 focus:ring-brand-400">
                <option value="">All Categories</option>
                @foreach($categories as $cat)
                    <option value="{{ $cat->id }}" @selected(request('category') == $cat->id)>{{ $cat->name }}</option>
                @endforeach
            </select>
            <button type="submit" class="bg-brand-700 text-white px-4 py-2 rounded-lg text-sm font-semibold hover:bg-brand-800">Filter</button>
        </form>

        <div class="bg-white rounded-2xl shadow-sm border border-gray-100 overflow-hidden">
            <table class="w-full text-sm">
                <thead class="bg-gray-50 border-b border-gray-100">
                    <tr class="text-left text-gray-500 text-xs uppercase">
                        <th class="px-5 py-3 font-medium">Product</th>
                        <th class="px-5 py-3 font-medium">Category</th>
                        <th class="px-5 py-3 font-medium">Artisan</th>
                        <th class="px-5 py-3 font-medium">Price</th>
                        <th class="px-5 py-3 font-medium">Stock</th>
                        <th class="px-5 py-3 font-medium">Status</th>
                        <th class="px-5 py-3 font-medium">Actions</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-gray-50">
                    @forelse($products as $product)
                        <tr class="hover:bg-gray-50">
                            <td class="px-5 py-3">
                                <div class="flex items-center gap-3">
                                    <div class="w-10 h-10 bg-brand-100 rounded-lg overflow-hidden">
                                        @if($product->images && count($product->images) > 0)
                                            <img src="{{ asset('storage/' . $product->images[0]) }}" class="w-full h-full object-cover">
                                        @else
                                            <div class="w-full h-full flex items-center justify-center text-lg">🎨</div>
                                        @endif
                                    </div>
                                    <div>
                                        <p class="font-medium text-gray-900 max-w-xs truncate">{{ $product->name }}</p>
                                        <p class="text-xs text-gray-400">⭐ {{ number_format($product->average_rating, 1) }} ({{ $product->review_count }})</p>
                                    </div>
                                </div>
                            </td>
                            <td class="px-5 py-3 text-gray-500">{{ $product->category->name }}</td>
                            <td class="px-5 py-3 text-gray-500">{{ $product->artisan->name }}</td>
                            <td class="px-5 py-3 font-semibold text-brand-700">₱{{ number_format($product->price, 2) }}</td>
                            <td class="px-5 py-3">
                                <span class="{{ $product->stock > 0 ? 'text-green-600' : 'text-red-500' }} font-medium">
                                    {{ $product->stock }}
                                </span>
                            </td>
                            <td class="px-5 py-3">
                                <span class="{{ match($product->status) { 'active' => 'badge-success', 'inactive' => 'badge-secondary', default => 'badge-warning' } }}">
                                    {{ ucfirst($product->status) }}
                                </span>
                            </td>
                            <td class="px-5 py-3">
                                <div class="flex gap-2">
                                    <a href="{{ route('products.show', $product->slug) }}"
                                       class="text-xs text-brand-600 hover:underline">View</a>
                                    <form method="POST" action="{{ route('admin.products.delete', $product->id) }}"
                                          onsubmit="return confirm('Delete this product permanently?')">
                                        @csrf @method('DELETE')
                                        <button class="text-xs text-red-400 hover:text-red-600">🗑 Delete</button>
                                    </form>
                                </div>
                            </td>
                        </tr>
                    @empty
                        <tr><td colspan="7" class="text-center py-12 text-gray-400">No products found.</td></tr>
                    @endforelse
                </tbody>
            </table>
            <div class="px-5 py-4 border-t border-gray-100">{{ $products->links() }}</div>
        </div>
    </div>
</div>

@endsection
