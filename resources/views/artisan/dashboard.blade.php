@extends('layouts.app')

@section('title', 'Artisan Dashboard')

@section('content')

<div class="flex min-h-screen">

    {{-- ── Sidebar ─────────────────────────────────────────── --}}
    <aside class="w-56 bg-brand-800 text-white shrink-0 min-h-screen pt-6 hidden lg:block">
        <div class="px-5 mb-8">
            <div class="w-12 h-12 rounded-full overflow-hidden mb-3 border-2 border-accent">
                <img src="{{ auth()->user()->profile_photo_url }}"
                     alt="{{ auth()->user()->name }}"
                     class="w-full h-full object-cover">
            </div>
            <p class="font-semibold text-sm">{{ auth()->user()->name }}</p>
            <p class="text-brand-300 text-xs mt-0.5">{{ auth()->user()->shop_name }}</p>
        </div>
        <nav class="space-y-1 px-3">
            @foreach([
                ['artisan.dashboard',       'gauge',         'Dashboard'],
                ['artisan.products.index',  'box',           'My Products'],
                ['artisan.products.create', 'plus',          'Add Product'],
                ['artisan.orders',          'cart-shopping', 'Orders'],
            ] as [$route, $icon, $label])
                <a href="{{ route($route) }}"
                   class="flex items-center gap-3 px-3 py-2 rounded-lg text-sm transition
                          {{ request()->routeIs($route) ? 'bg-brand-700 text-white font-semibold' : 'text-brand-200 hover:bg-brand-700' }}">
                    <i class="fa-solid fa-{{ $icon }} w-4 text-center"></i>
                    {{ $label }}
                </a>
            @endforeach
        </nav>
    </aside>

    {{-- ── Main Content ────────────────────────────────────── --}}
    <div class="flex-1 bg-gray-50 p-6 lg:p-8">

        {{-- Header --}}
        <div class="flex items-center justify-between mb-8">
            <div>
                <h1 class="font-display text-2xl font-bold text-gray-900">
                    Welcome back, {{ auth()->user()->name }}! 
                </h1>
                <p class="text-gray-400 text-sm mt-1">
                    {{ auth()->user()->tribe }} Artisan • {{ auth()->user()->region }}
                </p>
            </div>
            <a href="{{ route('artisan.products.create') }}"
               class="bg-brand-700 text-white font-bold px-5 py-2.5 rounded-xl hover:bg-brand-800 transition flex items-center gap-2 shadow-sm">
                <i class="fa-solid fa-plus"></i>
                Add Product
            </a>
        </div>

        {{-- ── STAT CARDS ─────────────────────────────────── --}}
        <div class="grid grid-cols-2 lg:grid-cols-4 gap-4 mb-8">
            @foreach([
                ['Total Products',       $totalProducts, 'box', 'text-brown-900',   'bg-blue-50'],
                ['Active Products',      $activeProducts, 'circle-check',  'text-brown-900',  'bg-green-50'],
                ['Total Orders',         $totalOrders, 'cart-shopping', 'text-brown-900', 'bg-purple-50'],
                ['Revenue (Delivered)',  '₱'.number_format($totalRevenue, 2),'peso-sign',     'text-brown-900',  'bg-brand-50'],
            ] as [$label, $value, $icon, $color, $bg])
                <div class="bg-white rounded-2xl shadow-sm border border-gray-100 p-5">
                    <div class="flex items-center justify-between mb-2">
                        <span class="text-gray-400 text-xs">{{ $label }}</span>
                        <div class="w-9 h-9 {{ $bg }} rounded-lg flex items-center justify-center">
                            <i class="fa-solid fa-{{ $icon }} text-gray-900 text-lg"></i>
                        </div>
                    </div>
                    <div class="font-display text-2xl font-bold {{ $color }}">{{ $value }}</div>
                </div>
            @endforeach
        </div>

        {{-- ── STOCK STATUS SECTION ────────────────────────── --}}
        <div class="bg-white rounded-2xl shadow-sm border border-gray-100 p-6 mb-8">

            <div class="flex items-center justify-between mb-5">
                <h2 class="font-semibold text-gray-900 flex items-center gap-2">
                    <i class="fa-solid fa-warehouse text-brand-600"></i>
                    Product Stock Status
                </h2>
                <a href="{{ route('artisan.products.index') }}"
                   class="text-brand-600 text-sm hover:underline flex items-center gap-1">
                    Manage all <i class="fa-solid fa-arrow-right text-xs"></i>
                </a>
            </div>

            {{-- Stock Summary Badges --}}
            <div class="flex flex-wrap gap-3 mb-5">
                <div class="flex items-center gap-2 bg-green-50 border border-green-200 rounded-xl px-4 py-2">
                    <i class="fa-solid fa-circle-check text-green-500"></i>
                    <span class="text-sm font-semibold text-green-700">
                        {{ $inStockCount }} In Stock
                    </span>
                </div>
                <div class="flex items-center gap-2 bg-orange-50 border border-orange-200 rounded-xl px-4 py-2">
                    <i class="fa-solid fa-triangle-exclamation text-orange-500"></i>
                    <span class="text-sm font-semibold text-orange-700">
                        {{ $lowStockCount }} Low Stock
                    </span>
                </div>
                <div class="flex items-center gap-2 bg-red-50 border border-red-200 rounded-xl px-4 py-2">
                    <i class="fa-solid fa-circle-xmark text-red-500"></i>
                    <span class="text-sm font-semibold text-red-700">
                        {{ $outOfStockCount }} Out of Stock
                    </span>
                </div>
            </div>

            {{-- Product Stock Table --}}
            @if($allProducts->count() > 0)
                <div class="overflow-x-auto">
                    <table class="w-full text-sm">
                        <thead>
                            <tr class="text-left text-gray-400 text-xs uppercase border-b border-gray-100">
                                <th class="pb-3 font-medium">Product</th>
                                <th class="pb-3 font-medium">Category</th>
                                <th class="pb-3 font-medium text-center">Stock Qty</th>
                                <th class="pb-3 font-medium text-center">Status</th>
                                <th class="pb-3 font-medium text-center">Action</th>
                            </tr>
                        </thead>
                        <tbody class="divide-y divide-gray-50">
                            @foreach($allProducts as $product)
                                <tr class="hover:bg-gray-50 transition">

                                    {{-- Product Name + Image --}}
                                    <td class="py-3">
                                        <div class="flex items-center gap-3">
                                            {{-- Product thumbnail --}}
                                            <div class="w-12 h-12 rounded-xl overflow-hidden bg-brand-50 shrink-0 border border-gray-100">
                                                @if($product->images && count($product->images) > 0)
                                                    <img src="{{ asset('storage/' . $product->images[0]) }}"
                                                         alt="{{ $product->name }}"
                                                         class="w-full h-full object-cover">
                                                @else
                                                    <div class="w-full h-full flex items-center justify-center">
                                                        <i class="fa-solid fa-image text-brand-300 text-lg"></i>
                                                    </div>
                                                @endif
                                            </div>
                                            {{-- Name --}}
                                            <div>
                                                <p class="font-medium text-gray-900 max-w-xs truncate">
                                                    {{ $product->name }}
                                                </p>
                                                <p class="text-xs text-gray-400 mt-0.5">
                                                    ₱{{ number_format($product->price, 2) }}
                                                </p>
                                            </div>
                                        </div>
                                    </td>

                                    {{-- Category --}}
                                    <td class="py-3 text-gray-500">
                                        {{ $product->category->name }}
                                    </td>

                                    {{-- Stock Quantity --}}
                                    <td class="py-3 text-center">
                                        <span class="font-bold text-lg
                                            {{ $product->stock === 0
                                                ? 'text-red-500'
                                                : ($product->stock <= 5
                                                    ? 'text-orange-500'
                                                    : 'text-green-600') }}">
                                            {{ $product->stock }}
                                        </span>
                                    </td>

                                    {{-- Stock Status Badge --}}
                                    <td class="py-3 text-center">
                                        @if($product->stock === 0)
                                            <span class="inline-flex items-center gap-1 bg-red-100 text-red-700 text-xs font-bold px-3 py-1 rounded-full">
                                                <i class="fa-solid fa-circle-xmark"></i>
                                                Out of Stock
                                            </span>
                                        @elseif($product->stock <= 5)
                                            <span class="inline-flex items-center gap-1 bg-orange-100 text-orange-700 text-xs font-bold px-3 py-1 rounded-full">
                                                <i class="fa-solid fa-triangle-exclamation"></i>
                                                Low Stock
                                            </span>
                                        @else
                                            <span class="inline-flex items-center gap-1 bg-green-100 text-green-700 text-xs font-bold px-3 py-1 rounded-full">
                                                <i class="fa-solid fa-circle-check"></i>
                                                In Stock
                                            </span>
                                        @endif
                                    </td>

                                    {{-- Action --}}
                                    <td class="py-3 text-center">
                                        <a href="{{ route('artisan.products.edit', $product->id) }}"
                                           class="inline-flex items-center gap-1 bg-brand-50 text-brand-700 text-xs font-semibold px-3 py-1.5 rounded-lg hover:bg-brand-100 transition">
                                            <i class="fa-solid fa-pen"></i>
                                            Update Stock
                                        </a>
                                    </td>
                                </tr>
                            @endforeach
                        </tbody>
                    </table>
                </div>
            @else
                <div class="text-center py-10 text-gray-400">
                    <i class="fa-solid fa-box-open text-4xl mb-3"></i>
                    <p class="text-sm">No products yet.</p>
                    <a href="{{ route('artisan.products.create') }}"
                       class="mt-3 inline-block text-brand-600 text-sm hover:underline">
                        Add your first product →
                    </a>
                </div>
            @endif
        </div>

        {{-- ── RECENT ORDERS ───────────────────────────────── --}}
        <div class="bg-white rounded-2xl shadow-sm border border-gray-100 p-6">
            <div class="flex items-center justify-between mb-5">
                <h2 class="font-semibold text-gray-900 flex items-center gap-2">
                    <i class="fa-solid fa-clock-rotate-left text-brand-600"></i>
                    Recent Orders for Your Products
                </h2>
                <a href="{{ route('artisan.orders') }}"
                   class="text-brand-600 text-sm hover:underline flex items-center gap-1">
                    View all <i class="fa-solid fa-arrow-right text-xs"></i>
                </a>
            </div>

            @if($recentOrders->count() > 0)
                <div class="space-y-4">
                    @foreach($recentOrders as $order)
                        <div class="flex items-center gap-4 p-4 bg-gray-50 rounded-xl hover:bg-gray-100 transition">
                            <div class="flex-1 min-w-0">
                                <div class="flex items-center gap-2 mb-1">
                                    <span class="font-mono text-xs font-bold text-brand-700">
                                        {{ $order->order_number }}
                                    </span>
                                    <span class="{{ $order->status_badge }}">
                                        {{ $order->status_label }}
                                    </span>
                                </div>
                                <p class="text-sm text-gray-600 truncate">
                                    by {{ $order->customer->name }} •
                                    {{ $order->items->count() }} item(s)
                                </p>
                                <p class="text-xs text-gray-400 mt-0.5">
                                    {{ $order->created_at->diffForHumans() }}
                                </p>
                            </div>
                            <div class="text-right shrink-0">
                                <p class="font-bold text-brand-700">
                                    {{ $order->formatted_total }}
                                </p>
                                @if($order->status === 'pending')
                                    <form method="POST"
                                          action="{{ route('artisan.orders.status', $order->id) }}"
                                          class="mt-1">
                                        @csrf
                                        @method('PATCH')
                                        <input type="hidden" name="status" value="processing">
                                        <button class="text-xs bg-blue-100 text-blue-700 px-3 py-1 rounded-lg font-semibold hover:bg-blue-200 transition">
                                            <i class="fa-solid fa-gear mr-1"></i>
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
                    <i class="fa-solid fa-cart-shopping text-4xl mb-3"></i>
                    <p class="text-sm">No orders yet.</p>
                    <p class="text-xs mt-1">Share your products to start selling!</p>
                </div>
            @endif
        </div>

    </div>{{-- end main content --}}
</div>{{-- end flex --}}

@endsection
<!-- @extends('layouts.app')

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
                ['artisan.dashboard', '', 'Dashboard'],
                ['artisan.products.index', '', 'My Products'],
                ['artisan.products.create', '', 'Add Product'],
                ['artisan.orders', '', 'Orders'],
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
                <h1 class="font-display text-2xl font-bold text-gray-900">Welcome back, {{ auth()->user()->name }}! </h1>
                <p class="text-gray-400 text-sm mt-1">{{ auth()->user()->tribe }} Artisan • {{ auth()->user()->region }}</p>
            </div>
            <a href="{{ route('artisan.products.create') }}"
               class="bg-brand-700 text-white font-bold px-5 py-2.5 rounded-xl hover:bg-brand-800 transition flex items-center gap-2">
                 Add Product
            </a>
        </div>

        {{-- Stats --}}
        <div class="grid grid-cols-2 lg:grid-cols-4 gap-4 mb-8">
            @foreach([
                ['Total Products',      $totalProducts,                       'fa-box',           'text-brown-900',   'bg-blue-50'],
                ['Active Products',     $activeProducts,                      'fa-circle-check',  'text-gray-900',  'bg-green-50'],
                ['Total Orders',        $totalOrders,                         'fa-cart-shopping', 'text-gray-900', 'bg-purple-50'],
                ['Revenue (Delivered)', '₱'.number_format($totalRevenue, 2),  'fa-peso-sign',     'text-gray-900',  'bg-brand-50'],
            ] as [$label, $value, $icon, $color, $bg])
                <div class="bg-white rounded-2xl shadow-sm border border-gray-100 p-5">
                    <div class="flex items-center justify-between mb-2">
                        <span class="text-gray-400 text-xs">{{ $label }}</span>
                        <div class="w-9 h-9 {{ $bg }} rounded-lg flex items-center justify-center">
                            <i class="fa-solid {{ $icon }} {{ $color }} text-lg"></i>
                        </div>
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
                    <div class="text-4xl mb-2"></div>
                    <p>No orders yet. Share your products to start selling!</p>
                </div>
            @endif
        </div>
    </div>
</div>

@endsection -->
