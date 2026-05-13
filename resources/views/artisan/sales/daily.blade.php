@extends('layouts.app')

@section('title', 'Daily Sales Report')

@section('content')

<div class="flex min-h-screen">

    {{-- Sidebar --}}
    @include('artisan.partials.sidebar')

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