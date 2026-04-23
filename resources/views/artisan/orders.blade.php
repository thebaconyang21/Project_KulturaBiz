@extends('layouts.app')

@section('title', 'My Orders')

@section('content')

<div class="flex min-h-screen">
    <aside class="w-56 bg-brand-800 text-white shrink-0 min-h-screen pt-6 hidden lg:block">
        <div class="px-5 mb-8">
            <p class="font-semibold text-sm">{{ auth()->user()->name }}</p>
            <p class="text-brand-300 text-xs">{{ auth()->user()->shop_name }}</p>
        </div>
        <nav class="space-y-1 px-3">
            @foreach([['artisan.dashboard','','Dashboard'],['artisan.products.index','','My Products'],['artisan.products.create','','Add Product'],['artisan.orders','🛒','Orders']] as [$r,$i,$l])
                <a href="{{ route($r) }}" class="flex items-center gap-3 px-3 py-2 rounded-lg text-sm transition {{ request()->routeIs($r) ? 'bg-brand-700 text-white font-semibold' : 'text-brand-200 hover:bg-brand-700' }}">{{ $i }} {{ $l }}</a>
            @endforeach
        </nav>
    </aside>

    <div class="flex-1 bg-gray-50 p-8">
        <h1 class="font-display text-2xl font-bold text-gray-900 mb-8">Orders for My Products</h1>

        @if($orders->count() > 0)
            <div class="space-y-4">
                @foreach($orders as $order)
                    <div class="bg-white rounded-2xl shadow-sm border border-gray-100 p-6">
                        <div class="flex flex-wrap items-start justify-between gap-3 mb-4">
                            <div>
                                <span class="font-mono font-bold text-brand-700">{{ $order->order_number }}</span>
                                <span class="ml-3 {{ $order->status_badge }}">{{ $order->status_label }}</span>
                                <p class="text-xs text-gray-400 mt-1">
                                    {{ $order->created_at->format('M d, Y') }} •
                                    Customer: {{ $order->customer->name }} •
                                     {{ $order->contact_number }}
                                </p>
                            </div>
                            <div class="text-right">
                                <p class="font-bold text-brand-700">{{ $order->formatted_total }}</p>
                                <p class="text-xs text-gray-400">{{ strtoupper($order->payment_method) }}</p>
                            </div>
                        </div>

                        {{-- Items from this artisan --}}
                        <div class="bg-gray-50 rounded-xl p-4 mb-4">
                            <p class="text-xs font-semibold text-gray-500 uppercase mb-3">Your Items in This Order</p>
                            @foreach($order->items as $item)
                                <div class="flex items-center gap-3 mb-2 last:mb-0">
                                    <div class="w-10 h-10 bg-brand-100 rounded-lg overflow-hidden shrink-0">
                                        @if($item->product && $item->product->images)
                                            <img src="{{ asset('storage/' . $item->product->images[0]) }}" class="w-full h-full object-cover">
                                        @else
                                            <div class="w-full h-full flex items-center justify-center text-lg"></div>
                                        @endif
                                    </div>
                                    <div class="flex-1 text-sm">
                                        <span class="font-medium">{{ $item->product_name }}</span>
                                        <span class="text-gray-400 ml-2">× {{ $item->quantity }}</span>
                                    </div>
                                    <span class="font-semibold text-sm">₱{{ number_format($item->subtotal, 2) }}</span>
                                </div>
                            @endforeach
                        </div>

                        {{-- Delivery address --}}
                        <p class="text-xs text-gray-500 mb-3">
                             {{ $order->delivery_address }}, {{ $order->city }}, {{ $order->province }}
                        </p>

                        {{-- Status Action --}}
                        @if($order->status === 'pending')
                            <form method="POST" action="{{ route('artisan.orders.status', $order->id) }}" class="inline">
                                @csrf @method('PATCH')
                                <input type="hidden" name="status" value="processing">
                                <button class="bg-blue-600 text-white text-sm font-bold px-4 py-2 rounded-lg hover:bg-blue-700 transition">
                                     Mark as Processing
                                </button>
                            </form>
                        @elseif($order->status === 'processing')
                            <span class="text-xs text-blue-600 font-medium bg-blue-50 px-3 py-1.5 rounded-lg">
                                 You're currently processing this order. Shipping will be handled by admin.
                            </span>
                        @endif
                    </div>
                @endforeach
            </div>
            <div class="mt-8">{{ $orders->links() }}</div>
        @else
            <div class="text-center py-20">
                <div class="text-6xl mb-4"></div>
                <h3 class="text-xl font-semibold text-gray-600 mb-2">No orders yet</h3>
                <p class="text-gray-400">Orders for your products will appear here.</p>
            </div>
        @endif
    </div>
</div>

@endsection
