@extends('layouts.app')

@section('title', 'My Orders')

@section('content')

<div class="max-w-4xl mx-auto px-4 py-8">
    <h1 class="font-display text-3xl font-bold text-brand-800 mb-8">My Orders</h1>

    @if($orders->count() > 0)
        <div class="space-y-4">
            @foreach($orders as $order)
                <div class="bg-white rounded-2xl shadow-sm border border-gray-100 p-6 hover:shadow-md transition">
                    <div class="flex flex-wrap items-start justify-between gap-3 mb-4">
                        <div>
                            <p class="font-bold text-gray-900">#{{ $order->order_number }}</p>
                            <p class="text-xs text-gray-400 mt-1">{{ $order->created_at->format('M d, Y') }}</p>
                        </div>
                        <div class="flex items-center gap-3">
                            <span class="{{ $order->status_badge }}">{{ $order->status_label }}</span>
                            <span class="font-bold text-brand-700">{{ $order->formatted_total }}</span>
                        </div>
                    </div>

                    {{-- Items preview --}}
                    <div class="flex flex-wrap gap-2 mb-4">
                        @foreach($order->items->take(3) as $item)
                            <div class="flex items-center gap-2 bg-gray-50 rounded-lg px-3 py-1.5 text-sm">
                                <span class="text-gray-600">{{ $item->product_name }}</span>
                                <span class="text-gray-400">×{{ $item->quantity }}</span>
                            </div>
                        @endforeach
                        @if($order->items->count() > 3)
                            <span class="text-xs text-gray-400 px-2 py-1.5">+{{ $order->items->count() - 3 }} more</span>
                        @endif
                    </div>

                    <div class="flex gap-3">
                        <a href="{{ route('orders.track', $order->id) }}"
                           class="bg-brand-700 text-white text-sm font-semibold px-4 py-2 rounded-lg hover:bg-brand-800 transition">
                            Track Order
                        </a>
                        @if($order->courier_name)
                            <span class="text-xs text-gray-400 self-center">via {{ $order->courier_name }}</span>
                        @endif
                    </div>
                </div>
            @endforeach
        </div>

        <div class="mt-8">{{ $orders->links() }}</div>
    @else
        <div class="text-center py-20">
            <div class="text-6xl mb-4">📦</div>
            <h3 class="text-xl font-semibold text-gray-600 mb-2">No orders yet</h3>
            <p class="text-gray-400 mb-6">Start shopping to see your orders here.</p>
            <a href="{{ route('products.index') }}"
               class="bg-brand-700 text-white font-bold px-8 py-3 rounded-full hover:bg-brand-800 transition">
                Browse Products
            </a>
        </div>
    @endif
</div>

@endsection