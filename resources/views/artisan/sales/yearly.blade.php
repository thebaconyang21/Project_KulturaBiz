@extends('layouts.app')

@section('title', 'Yearly Sales Report')

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
            <button @click="salesOpen = !salesOpen"
                    class="w-full flex items-center gap-3 px-3 py-2 rounded-lg text-sm bg-brand-700 text-white font-semibold">
                <i class="fa-solid fa-chart-line w-4 text-center"></i>
                <span class="flex-1 text-left">Sales</span>
                <i class="fa-solid fa-chevron-down text-xs" :class="salesOpen ? 'rotate-180' : ''"></i>
            </button>
            <div x-show="salesOpen" class="ml-4 space-y-1 border-l border-brand-600 pl-3 mt-1">
                <a href="{{ route('artisan.sales.daily') }}" class="flex items-center gap-3 px-3 py-2 rounded-lg text-sm transition text-brand-300 hover:bg-brand-700 hover:text-white">
                    <i class="fa-solid fa-calendar-day w-4 text-center text-xs"></i> Daily
                </a>
                <a href="{{ route('artisan.sales.monthly') }}" class="flex items-center gap-3 px-3 py-2 rounded-lg text-sm transition text-brand-300 hover:bg-brand-700 hover:text-white">
                    <i class="fa-solid fa-calendar-days w-4 text-center text-xs"></i> Monthly
                </a>
                <a href="{{ route('artisan.sales.yearly') }}" class="flex items-center gap-3 px-3 py-2 rounded-lg text-sm bg-brand-600 text-white font-semibold">
                    <i class="fa-solid fa-calendar w-4 text-center text-xs"></i> Yearly
                </a>
            </div>
        </nav>
    </aside>

    {{-- Main Content --}}
    <div class="flex-1 bg-gray-50 p-6 lg:p-8">

        <div class="flex items-center justify-between mb-8">
            <div>
                <h1 class="font-display text-2xl font-bold text-gray-900 flex items-center gap-3">
                    <i class="fa-solid fa-calendar text-brand-600"></i>
                    Yearly Sales Report
                </h1>
                <p class="text-gray-400 text-sm mt-1">{{ now()->format('Y') }} — This Year</p>
            </div>
        </div>

        {{-- Summary Cards --}}
        <div class="grid grid-cols-1 md:grid-cols-3 gap-4 mb-8">
            <div class="bg-white rounded-2xl shadow-sm border border-gray-100 p-5">
                <div class="flex items-center justify-between mb-2">
                    <span class="text-gray-400 text-xs">This Year's Revenue</span>
                    <div class="w-9 h-9 bg-brand-50 rounded-lg flex items-center justify-center">
                        <i class="fa-solid fa-peso-sign text-gray-900"></i>
                    </div>
                </div>
                <div class="font-display text-2xl font-bold text-brand-600">
                    ₱{{ number_format($yearRevenue, 2) }}
                </div>
            </div>
            <div class="bg-white rounded-2xl shadow-sm border border-gray-100 p-5">
                <div class="flex items-center justify-between mb-2">
                    <span class="text-gray-400 text-xs">Orders This Year</span>
                    <div class="w-9 h-9 bg-blue-50 rounded-lg flex items-center justify-center">
                        <i class="fa-solid fa-cart-shopping text-gray-900"></i>
                    </div>
                </div>
                <div class="font-display text-2xl font-bold text-blue-600">
                    {{ $yearOrders }}
                </div>
            </div>
            <div class="bg-white rounded-2xl shadow-sm border border-gray-100 p-5">
                <div class="flex items-center justify-between mb-2">
                    <span class="text-gray-400 text-xs">Items Sold This Year</span>
                    <div class="w-9 h-9 bg-green-50 rounded-lg flex items-center justify-center">
                        <i class="fa-solid fa-box text-gray-900"></i>
                    </div>
                </div>
                <div class="font-display text-2xl font-bold text-green-600">
                    {{ $yearItemsSold }}
                </div>
            </div>
        </div>

        {{-- Monthly Breakdown Chart --}}
        <div class="bg-white rounded-2xl shadow-sm border border-gray-100 p-6 mb-6">
            <h2 class="font-semibold text-gray-900 mb-6 flex items-center gap-2">
                <i class="fa-solid fa-chart-bar text-brand-600"></i>
                Month by Month — {{ now()->format('Y') }}
            </h2>

            @if(count($monthlyBreakdown) > 0)
                <div class="space-y-3">
                    @foreach($monthlyBreakdown as $month)
                        <div class="flex items-center gap-4">
                            <span class="text-sm text-gray-500 w-10 shrink-0">{{ $month['month'] }}</span>
                            <div class="flex-1 bg-gray-100 rounded-full h-5 relative">
                                @php
                                    $maxRevenue = collect($monthlyBreakdown)->max('revenue');
                                    $width = $maxRevenue > 0 ? ($month['revenue'] / $maxRevenue) * 100 : 0;
                                @endphp
                                <div class="bg-brand-600 h-5 rounded-full transition-all duration-700 flex items-center justify-end pr-2"
                                     style="width: {{ max(1, $width) }}%">
                                </div>
                            </div>
                            <span class="text-sm font-bold text-brand-700 w-32 text-right shrink-0">
                                ₱{{ number_format($month['revenue'], 2) }}
                            </span>
                            <span class="text-xs text-gray-400 w-16 text-right shrink-0">
                                {{ $month['orders'] }} orders
                            </span>
                        </div>
                    @endforeach
                </div>
            @else
                <p class="text-gray-400 text-sm text-center py-6">No data available for this year.</p>
            @endif
        </div>

        {{-- Top Selling Products This Year --}}
        <div class="bg-white rounded-2xl shadow-sm border border-gray-100 p-6">
            <h2 class="font-semibold text-gray-900 mb-5 flex items-center gap-2">
                <i class="fa-solid fa-trophy text-brand-600"></i>
                Top Selling Products — {{ now()->format('Y') }}
            </h2>

            @if($topProducts->count() > 0)
                <div class="space-y-4">
                    @foreach($topProducts as $i => $product)
                        <div class="flex items-center gap-4 p-4 bg-gray-50 rounded-xl">
                            {{-- Rank --}}
                            <div class="w-8 h-8 rounded-full flex items-center justify-center font-bold text-sm shrink-0
                                {{ $loop->first ? 'bg-yellow-400 text-yellow-900' : ($loop->iteration === 2 ? 'bg-gray-300 text-gray-700' : ($loop->iteration === 3 ? 'bg-amber-600 text-white' : 'bg-brand-100 text-brand-700')) }}">
                                {{ $loop->iteration }}
                            </div>
                            {{-- Product image --}}
                            <div class="w-12 h-12 rounded-xl overflow-hidden bg-brand-50 border border-gray-100 shrink-0">
                                @if($product->images && count($product->images) > 0)
                                    <img src="{{ asset('storage/' . $product->images[0]) }}" alt="" class="w-full h-full object-cover">
                                @else
                                    <div class="w-full h-full flex items-center justify-center">
                                        <i class="fa-solid fa-image text-brand-300"></i>
                                    </div>
                                @endif
                            </div>
                            {{-- Name --}}
                            <div class="flex-1 min-w-0">
                                <p class="font-semibold text-gray-900 truncate">{{ $product->name }}</p>
                                <p class="text-xs text-gray-400">₱{{ number_format($product->price, 2) }} per unit</p>
                            </div>
                            {{-- Stats --}}
                            <div class="text-right shrink-0">
                                <p class="font-bold text-brand-700">{{ $product->total_sold ?? 0 }} sold</p>
                                <p class="text-xs text-gray-400">
                                    ₱{{ number_format(($product->total_sold ?? 0) * $product->price, 2) }}
                                </p>
                            </div>
                        </div>
                    @endforeach
                </div>
            @else
                <div class="text-center py-16 text-gray-400">
                    <i class="fa-solid fa-trophy text-5xl mb-4"></i>
                    <p class="font-medium text-gray-500">No sales data for this year yet.</p>
                </div>
            @endif
        </div>

    </div>
</div>

@endsection