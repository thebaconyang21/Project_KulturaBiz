@extends('layouts.app')

@section('title', 'Artisan Dashboard')

@section('content')

<div class="flex min-h-screen">
    {{-- Artisan Sidebar --}}
    <aside class="w-56 bg-brand-800 text-white shrink-0 min-h-screen pt-6 hidden lg:block">
        <div class="px-5 mb-8">
            <div class="w-12 h-12 rounded-full overflow-hidden mb-3">
                <img src="{{ auth()->user()->profile_photo_url }}" alt="" class="w-full h-full object-cover">
            </div>
            <p class="font-semibold text-sm">{{ auth()->user()->name }}</p>
            <p class="text-brand-300 text-xs">{{ auth()->user()->shop_name }}</p>
        </div>
        <nav class="space-y-1 px-3">
            @foreach([
                ['artisan.dashboard',       '📊', 'Dashboard'],
                ['artisan.products.index',  '📦', 'My Products'],
                ['artisan.products.create', '➕', 'Add Product'],
                ['artisan.orders',          '🛒', 'Orders'],
            ] as [$route, $icon, $label])
                <a href="{{ route($route) }}"
                   class="flex items-center gap-3 px-3 py-2 rounded-lg text-sm transition
                          {{ request()->routeIs($route) ? 'bg-brand-700 text-white font-semibold' : 'text-brand-200 hover:bg-brand-700' }}">
                    {{ $icon }} {{ $label }}
                </a>
            @endforeach
        </nav>
    </aside>

    <div class="flex-1 bg-gray-50 p-8">
        <div class="flex items-center justify-between mb-8">
            <div>
                <h1 class="font-display text-2xl font-bold text-gray-900">Welcome back, {{ auth()->user()->name }}! 👋</h1>
                <p class="text-gray-400 text-sm mt-1">{{ auth()->user()->tribe }} Artisan • {{ auth()->user()->region }}</p>
            </div>
            <a href="{{ route('artisan.products.create') }}"
               class="bg-brand-700 text-white font-bold px-5 py-2.5 rounded-xl hover:bg-brand-800 transition flex items-center gap-2">
                ➕ Add Product
            </a>
        </div>

        {{-- Stats --}}
        <div class="grid grid-cols-2 lg:grid-cols-4 gap-4 mb-8">
            @foreach([
                ['Total Products',  $totalProducts,  '📦', 'text-blue-600',  'bg-blue-50'],
                ['Active Products', $activeProducts, '✅', 'text-green-600', 'bg-green-50'],
                ['Total Orders',   $totalOrders,    '🛒', 'text-purple-600', 'bg-purple-50'],
                ['Revenue (Delivered)', '₱'.number_format($totalRevenue, 2), '💰', 'text-brand-600', 'bg-brand-50'],
            ] as [$label, $value, $icon, $color, $bg])
                <div class="bg-white rounded-2xl shadow-sm border border-gray-100 p-5">
                    <div class="flex items-center justify-between mb-2">
                        <span class="text-gray-400 text-xs">{{ $label }}</span>
                        <div class="w-8 h-8 {{ $bg }} rounded-lg flex items-center justify-center">{{ $icon }}</div>
                    </div>
                    <div class="font-display text-2xl font-bold {{ $color }}">{{ $value }}</div>
                </div>
            @endforeach
        </div>

        {{-- Recent Orders --}}
        <div class="bg-white rounded-2xl shadow-sm border border-gray-100 p-6">
            <div class="flex items-center justify-between mb-5">
                <h2 class="font-semibold text-gray-900">Recent Orders for Your Products</h2>
                <a href="{{ route('artisan.orders') }}" class="text-brand-600 text-sm hover:underline">View all →</a>
            </div>

            @if($recentOrders->count() > 0)
                <div class="space-y-4">
                    @foreach($recentOrders as $order)
                        <div class="flex items-center gap-4 p-4 bg-gray-50 rounded-xl">
                            <div class="flex-1">
                                <div class="flex items-center gap-2 mb-1">
                                    <span class="font-mono text-xs font-bold text-brand-700">{{ $order->order_number }}</span>
                                    <span class="{{ $order->status_badge }}">{{ $order->status_label }}</span>
                                </div>
                                <p class="text-sm text-gray-600">
                                    by {{ $order->customer->name }} •
                                    {{ $order->items->count() }} item(s)
                                </p>
                                <p class="text-xs text-gray-400 mt-0.5">{{ $order->created_at->diffForHumans() }}</p>
                            </div>
                            <div class="text-right">
                                <p class="font-bold text-brand-700">{{ $order->formatted_total }}</p>
                                @if($order->status === 'pending')
                                    <form method="POST" action="{{ route('artisan.orders.status', $order->id) }}" class="mt-1">
                                        @csrf @method('PATCH')
                                        <input type="hidden" name="status" value="processing">
                                        <button class="text-xs bg-blue-100 text-blue-700 px-2 py-0.5 rounded font-medium hover:bg-blue-200 transition">
                                            Mark Processing
                                        </button>
                                    </form>
                                @endif
                            </div>
                        </div>
                    @endforeach
                </div>
            @else
                <div class="text-center py-10 text-gray-400">
                    <div class="text-4xl mb-2">📦</div>
                    <p>No orders yet. Share your products to start selling!</p>
                </div>
            @endif
        </div>
    </div>
</div>

@endsection
