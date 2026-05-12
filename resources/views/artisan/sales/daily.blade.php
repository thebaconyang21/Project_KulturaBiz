@extends('layouts.app')

@section('title', 'Daily Sales Report')

@section('content')

<div class="flex min-h-screen">

    {{-- Sidebar --}}
    <aside class="w-56 bg-brand-800 text-white shrink-0 min-h-screen pt-6 hidden lg:block"
           x-data="{ salesOpen: true }">
        <div class="px-5 mb-8">
            <div class="w-12 h-12 rounded-full overflow-hidden mb-3 border-2 border-accent">
                <img src="https://ui-avatars.com/api/?name={{ urlencode(auth()->user()->name) }}"
                        alt=""
                        class="w-full h-full object-cover">
            </div>
            <p class="font-semibold text-sm">{{ auth()->user()->name }}</p>
            <p class="text-brand-300 text-xs mt-0.5">{{ auth()->user()->shop_name ?? 'My Shop' }}</p>
        </div>
        <nav class="space-y-1 px-3">
            <a href="{{ route('artisan.dashboard') }}" class="flex items-center gap-3 px-3 py-2 rounded-lg text-sm transition text-brand-200 hover:bg-brand-700">
                <i class="fa-solid fa-gauge w-4 text-center"></i> Dashboard
            </a>
            <a href="{{ route('artisan.products.index') }}" class="flex items-center gap-3 px-3 py-2 rounded-lg text-sm transition text-brand-200 hover:bg-brand-700">
                <i class="fa-solid fa-box w-4 text-center"></i> My Products
            </a>
            <a href="{{ route('artisan.products.create') }}" class="flex items-center gap-3 px-3 py-2 rounded-lg text-sm transition text-brand-200 hover:bg-brand-700">
                <i class="fa-solid fa-plus w-4 text-center"></i> Add Product
            </a>
            <a href="{{ route('artisan.orders') }}" class="flex items-center gap-3 px-3 py-2 rounded-lg text-sm transition text-brand-200 hover:bg-brand-700">
                <i class="fa-solid fa-cart-shopping w-4 text-center"></i> Orders
            </a>

            {{-- Sales with Submenu --}}
            <button @click="salesOpen = !salesOpen"
                    class="w-full flex items-center gap-3 px-3 py-2 rounded-lg text-sm transition bg-brand-700 text-white font-semibold">
                <i class="fa-solid fa-chart-line w-4 text-center"></i>
                <span class="flex-1 text-left">Sales</span>
                <i class="fa-solid fa-chevron-down text-xs transition-transform duration-200" :class="salesOpen ? 'rotate-180' : ''"></i>
            </button>

            <div x-show="salesOpen" class="ml-4 space-y-1 border-l border-brand-600 pl-3 mt-1">
                <a href="{{ route('artisan.sales.daily') }}"
                   class="flex items-center gap-3 px-3 py-2 rounded-lg text-sm transition bg-brand-600 text-white font-semibold">
                    <i class="fa-solid fa-calendar-day w-4 text-center text-xs"></i> Daily
                </a>
                <a href="{{ route('artisan.sales.monthly') }}"
                   class="flex items-center gap-3 px-3 py-2 rounded-lg text-sm transition text-brand-300 hover:bg-brand-700 hover:text-white">
                    <i class="fa-solid fa-calendar-days w-4 text-center text-xs"></i> Monthly
                </a>
                <a href="{{ route('artisan.sales.yearly') }}"
                   class="flex items-center gap-3 px-3 py-2 rounded-lg text-sm transition text-brand-300 hover:bg-brand-700 hover:text-white">
                    <i class="fa-solid fa-calendar w-4 text-center text-xs"></i> Yearly
                </a>
            </div>
        </nav>
    </aside>

    {{-- Main Content --}}
    <div class="flex-1 bg-gray-50 p-6 lg:p-8">

        {{-- Header --}}
        <div class="flex items-center justify-between mb-8">
            <div>
                <h1 class="font-display text-2xl font-bold text-gray-900 flex items-center gap-3">
                    <i class="fa-solid fa-calendar-day text-brand-600"></i>
                    Daily Sales Report
                </h1>
                <p class="text-gray-400 text-sm mt-1">{{ now()->format('F d, Y') }} — Today</p>
            </div>
        </div>

        {{-- Summary Cards --}}
        <div class="grid grid-cols-1 md:grid-cols-3 gap-4 mb-8">
            <div class="bg-white rounded-2xl shadow-sm border border-gray-100 p-5">
                <div class="flex items-center justify-between mb-2">
                    <span class="text-gray-400 text-xs">Today's Revenue</span>
                    <div class="w-9 h-9 bg-brand-50 rounded-lg flex items-center justify-center">
                        <i class="fa-solid fa-peso-sign text-gray-900"></i>
                    </div>
                </div>
                <div class="font-display text-2xl font-bold text-brand-600">
                    ₱{{ number_format($todayRevenue, 2) }}
                </div>
            </div>
            <div class="bg-white rounded-2xl shadow-sm border border-gray-100 p-5">
                <div class="flex items-center justify-between mb-2">
                    <span class="text-gray-400 text-xs">Orders Today</span>
                    <div class="w-9 h-9 bg-blue-50 rounded-lg flex items-center justify-center">
                        <i class="fa-solid fa-cart-shopping text-gray-900"></i>
                    </div>
                </div>
                <div class="font-display text-2xl font-bold text-blue-600">
                    {{ $todayOrders }}
                </div>
            </div>
            <div class="bg-white rounded-2xl shadow-sm border border-gray-100 p-5">
                <div class="flex items-center justify-between mb-2">
                    <span class="text-gray-400 text-xs">Items Sold Today</span>
                    <div class="w-9 h-9 bg-green-50 rounded-lg flex items-center justify-center">
                        <i class="fa-solid fa-box text-gray-900"></i>
                    </div>
                </div>
                <div class="font-display text-2xl font-bold text-green-600">
                    {{ $todayItemsSold }}
                </div>
            </div>
        </div>

        {{-- Daily Sales Table --}}
        <div class="bg-white rounded-2xl shadow-sm border border-gray-100 p-6">
            <h2 class="font-semibold text-gray-900 mb-5 flex items-center gap-2">
                <i class="fa-solid fa-list text-brand-600"></i>
                Today's Orders
            </h2>

            @if($dailySales->count() > 0)
                <div class="overflow-x-auto">
                    <table class="w-full text-sm">
                        <thead>
                            <tr class="text-left text-gray-400 text-xs uppercase border-b border-gray-100">
                                <th class="pb-3 font-medium">Order #</th>
                                <th class="pb-3 font-medium">Customer</th>
                                <th class="pb-3 font-medium">Items</th>
                                <th class="pb-3 font-medium">Payment</th>
                                <th class="pb-3 font-medium">Status</th>
                                <th class="pb-3 font-medium text-right">Amount</th>
                            </tr>
                        </thead>
                        <tbody class="divide-y divide-gray-50">
                            @foreach($dailySales as $order)
                                <tr class="hover:bg-gray-50 transition">
                                    <td class="py-3 font-mono text-xs font-bold text-brand-700">
                                        {{ $order->order_number }}
                                    </td>
                                    <td class="py-3 text-gray-700">{{ optional($order->customer)->name ?? 'Unknown Customer' }}</td>
                                    <td class="py-3 text-gray-500">{{ $order->items->count() }} item(s)</td>
                                    <td class="py-3 text-xs uppercase font-medium text-gray-500">
                                        {{ $order->payment_method }}
                                    </td>
                                    <td class="py-3">
                                        <span class="{{ $order->status_badge }}">{{ $order->status_label }}</span>
                                    </td>
                                    <td class="py-3 text-right font-bold text-brand-700">
                                        {{ $order->formatted_total }}
                                    </td>
                                </tr>
                            @endforeach
                        </tbody>
                        <tfoot class="border-t-2 border-gray-200">
                            <tr>
                                <td colspan="5" class="py-3 font-bold text-gray-900">Total</td>
                                <td class="py-3 text-right font-bold text-brand-700 text-base">
                                    ₱{{ number_format($todayRevenue, 2) }}
                                </td>
                            </tr>
                        </tfoot>
                    </table>
                </div>
            @else
                <div class="text-center py-16 text-gray-400">
                    <i class="fa-solid fa-chart-bar text-5xl mb-4"></i>
                    <p class="font-medium text-gray-500">No sales today yet.</p>
                    <p class="text-sm mt-1">Orders placed today will appear here.</p>
                </div>
            @endif
        </div>

    </div>
</div>

@endsection