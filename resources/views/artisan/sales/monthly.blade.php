@extends('layouts.app')

@section('title', 'Monthly Sales Report')

@section('content')

<div class="flex min-h-screen">

    {{-- Sidebar --}}
    @include('artisan.partials.sidebar')

    {{-- Main Content --}}
    <div class="flex-1 bg-gray-50 p-6 lg:p-8">

        <div class="flex items-center justify-between mb-8">
            <div>
                <h1 class="font-display text-2xl font-bold text-gray-900 flex items-center gap-3">
                    <i class="fa-solid fa-calendar-days text-brand-600"></i>
                    Monthly Sales Report
                </h1>
                <p class="text-gray-400 text-sm mt-1">{{ now()->format('F Y') }} — This Month</p>
            </div>
        </div>

        {{-- Summary Cards --}}
        <div class="grid grid-cols-1 md:grid-cols-3 gap-4 mb-8">
            <div class="bg-white rounded-2xl shadow-sm border border-gray-100 p-5">
                <div class="flex items-center justify-between mb-2">
                    <span class="text-gray-400 text-xs">This Month's Revenue</span>
                    <div class="w-9 h-9 bg-brand-50 rounded-lg flex items-center justify-center">
                        <i class="fa-solid fa-peso-sign text-gray-900"></i>
                    </div>
                </div>
                <div class="font-display text-2xl font-bold text-brand-600">
                    ₱{{ number_format($monthRevenue, 2) }}
                </div>
            </div>
            <div class="bg-white rounded-2xl shadow-sm border border-gray-100 p-5">
                <div class="flex items-center justify-between mb-2">
                    <span class="text-gray-400 text-xs">Orders This Month</span>
                    <div class="w-9 h-9 bg-blue-50 rounded-lg flex items-center justify-center">
                        <i class="fa-solid fa-cart-shopping text-gray-900"></i>
                    </div>
                </div>
                <div class="font-display text-2xl font-bold text-blue-600">
                    {{ $monthOrders }}
                </div>
            </div>
            <div class="bg-white rounded-2xl shadow-sm border border-gray-100 p-5">
                <div class="flex items-center justify-between mb-2">
                    <span class="text-gray-400 text-xs">Items Sold This Month</span>
                    <div class="w-9 h-9 bg-green-50 rounded-lg flex items-center justify-center">
                        <i class="fa-solid fa-box text-gray-900"></i>
                    </div>
                </div>
                <div class="font-display text-2xl font-bold text-green-600">
                    {{ $monthItemsSold }}
                </div>
            </div>
        </div>

        {{-- Weekly Breakdown --}}
        <div class="bg-white rounded-2xl shadow-sm border border-gray-100 p-6 mb-6">
            <h2 class="font-semibold text-gray-900 mb-5 flex items-center gap-2">
                <i class="fa-solid fa-chart-bar text-brand-600"></i>
                Week by Week Breakdown — {{ now()->format('F Y') }}
            </h2>

            @if(count($weeklyBreakdown) > 0)
                <div class="space-y-3">
                    @foreach($weeklyBreakdown as $week)
                        <div class="flex items-center gap-4">
                            <span class="text-sm text-gray-500 w-20 shrink-0">Week {{ $loop->iteration }}</span>
                            <div class="flex-1 bg-gray-100 rounded-full h-4 relative">
                                <div class="bg-brand-600 h-4 rounded-full transition-all duration-500"
                                     style="width: {{ $monthRevenue > 0 ? min(100, ($week['revenue'] / $monthRevenue) * 100) : 0 }}%">
                                </div>
                            </div>
                            <span class="text-sm font-bold text-brand-700 w-28 text-right shrink-0">
                                ₱{{ number_format($week['revenue'], 2) }}
                            </span>
                        </div>
                    @endforeach
                </div>
            @else
                <p class="text-gray-400 text-sm text-center py-6">No data available for this month.</p>
            @endif
        </div>

        {{-- Monthly Orders Table --}}
        <div class="bg-white rounded-2xl shadow-sm border border-gray-100 p-6">
            <h2 class="font-semibold text-gray-900 mb-5 flex items-center gap-2">
                <i class="fa-solid fa-list text-brand-600"></i>
                All Orders This Month
            </h2>

            @if($monthlySales->count() > 0)
                <div class="overflow-x-auto">
                    <table class="w-full text-sm">
                        <thead>
                            <tr class="text-left text-gray-400 text-xs uppercase border-b border-gray-100">
                                <th class="pb-3 font-medium">Order #</th>
                                <th class="pb-3 font-medium">Customer</th>
                                <th class="pb-3 font-medium">Date</th>
                                <th class="pb-3 font-medium">Items</th>
                                <th class="pb-3 font-medium">Payment</th>
                                <th class="pb-3 font-medium">Status</th>
                                <th class="pb-3 font-medium text-right">Amount</th>
                            </tr>
                        </thead>
                        <tbody class="divide-y divide-gray-50">
                            @foreach($monthlySales as $order)
                                <tr class="hover:bg-gray-50 transition">
                                    <td class="py-3 font-mono text-xs font-bold text-brand-700">{{ $order->order_number }}</td>
                                    <td class="py-3 text-gray-700">{{ optional($order->customer)->name ?? 'Unknown Customer' }}</td>
                                    <td class="py-3 text-gray-400 text-xs">{{ $order->created_at->format('M d') }}</td>
                                    <td class="py-3 text-gray-500">{{ $order->items->count() }}</td>
                                    <td class="py-3 text-xs uppercase font-medium text-gray-500">{{ $order->payment_method }}</td>
                                    <td class="py-3"><span class="{{ $order->status_badge }}">{{ $order->status_label }}</span></td>
                                    <td class="py-3 text-right font-bold text-brand-700">{{ $order->formatted_total }}</td>
                                </tr>
                            @endforeach
                        </tbody>
                        <tfoot class="border-t-2 border-gray-200">
                            <tr>
                                <td colspan="6" class="py-3 font-bold text-gray-900">Total</td>
                                <td class="py-3 text-right font-bold text-brand-700 text-base">₱{{ number_format($monthRevenue, 2) }}</td>
                            </tr>
                        </tfoot>
                    </table>
                </div>
            @else
                <div class="text-center py-16 text-gray-400">
                    <i class="fa-solid fa-chart-bar text-5xl mb-4"></i>
                    <p class="font-medium text-gray-500">No sales this month yet.</p>
                </div>
            @endif
        </div>

    </div>
</div>

@endsection