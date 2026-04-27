@extends('layouts.app')

@section('title', 'Order Confirmed!')

@section('content')

<div class="max-w-2xl mx-auto px-4 py-16 text-center">

    {{-- Success Animation --}}
    <div class="w-24 h-24 bg-green-100 rounded-full flex items-center justify-center mx-auto mb-6 text-5xl">
        
    </div>

    <h1 class="font-display text-3xl font-bold text-gray-900 mb-3">Order Confirmed!</h1>
    <p class="text-gray-500 mb-8">
        Thank you for supporting Mindanaoan artisans. Your order has been placed successfully!
    </p>

    {{-- Order Card --}}
    <div class="bg-white rounded-2xl shadow-sm border border-gray-100 p-6 text-left mb-8">
        <div class="flex justify-between items-start mb-4">
            <div>
                <p class="text-sm text-gray-500">Order Number</p>
                <p class="font-bold text-xl text-brand-700">{{ $order->order_number }}</p>
            </div>
            <span class="badge-warning">{{ $order->status_label }}</span>
        </div>

        {{-- Items --}}
        <div class="space-y-3 mb-5">
            @foreach($order->items as $item)
                <div class="flex justify-between text-sm">
                    <span class="text-gray-700">{{ $item->product_name }} × {{ $item->quantity }}</span>
                    <span class="font-medium">₱{{ number_format($item->subtotal, 2) }}</span>
                </div>
            @endforeach
        </div>

        <div class="border-t border-gray-100 pt-4 space-y-1 text-sm">
            <div class="flex justify-between text-gray-500">
                <span>Subtotal</span><span>₱{{ number_format($order->subtotal, 2) }}</span>
            </div>
            <div class="flex justify-between text-gray-500">
                <span>Shipping</span><span>₱{{ number_format($order->shipping_fee, 2) }}</span>
            </div>
            <div class="flex justify-between font-bold text-gray-900 text-base pt-1">
                <span>Total</span><span class="text-brand-700">{{ $order->formatted_total }}</span>
            </div>
        </div>

        {{-- Logistics info --}}
        <div class="mt-5 bg-brand-50 rounded-xl p-4 text-sm">
            <p class="font-semibold text-brand-800 mb-2">Delivery Information</p>
            <p class="text-gray-600">Courier: <strong>{{ $order->courier_name }}</strong></p>
            <p class="text-gray-600">Tracking: <strong>{{ $order->tracking_number }}</strong></p>
            <p class="text-gray-600">Estimated Arrival: <strong>{{ $order->estimated_delivery->format('F d, Y') }}</strong></p>
            <p class="text-gray-600">Payment: <strong>{{ strtoupper($order->payment_method) }}</strong></p>
        </div>
    </div>

    <div class="flex flex-col sm:flex-row gap-3 justify-center">
        <a href="{{ route('orders.track', $order->id) }}"
           class="bg-brand-700 text-white font-bold px-8 py-3 rounded-full hover:bg-brand-800 transition">
            Track My Order
        </a>
        <a href="{{ route('products.index') }}"
           class="border border-brand-300 text-brand-700 font-bold px-8 py-3 rounded-full hover:bg-brand-50 transition">
            Continue Shopping
        </a>
    </div>

    <p class="text-xs text-gray-400 mt-8">
        A confirmation summary has been added to your order history.
    </p>
</div>

@endsection