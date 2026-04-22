@extends('layouts.app')

@section('title', 'Shopping Cart')

@section('content')

<div class="max-w-5xl mx-auto px-4 py-8">
    <h1 class="font-display text-3xl font-bold text-brand-800 mb-8">Shopping Cart</h1>

    @if(empty($cart))
        <div class="text-center py-24">
            <div class="text-7xl mb-4">🛒</div>
            <h3 class="text-xl font-semibold text-gray-600 mb-2">Your cart is empty</h3>
            <p class="text-gray-400 mb-6">Browse our collection of handcrafted products</p>
            <a href="{{ route('products.index') }}"
               class="bg-brand-700 text-white font-bold px-8 py-3 rounded-full hover:bg-brand-800 transition">
                Browse Products
            </a>
        </div>
    @else
        <div class="grid grid-cols-1 lg:grid-cols-3 gap-8">

            {{-- Cart Items --}}
            <div class="lg:col-span-2 space-y-4">
                @foreach($cart as $productId => $item)
                    <div class="bg-white rounded-2xl shadow-sm border border-gray-100 p-5 flex gap-4">
                        <img src="{{ $item['image'] }}" alt="{{ $item['name'] }}"
                             class="w-24 h-24 object-cover rounded-xl bg-brand-50">

                        <div class="flex-1">
                            <h3 class="font-semibold text-gray-900">{{ $item['name'] }}</h3>
                            <p class="text-sm text-gray-400 mb-3">by {{ $item['artisan'] }}</p>

                            <div class="flex items-center justify-between">
                                <span class="font-bold text-brand-700">₱{{ number_format($item['price'], 2) }}</span>

                                <div class="flex items-center gap-3">
                                    {{-- Update quantity --}}
                                    <form method="POST" action="{{ route('cart.update', $productId) }}" class="flex items-center gap-2">
                                        @csrf
                                        @method('PATCH')
                                        <input type="number" name="quantity" value="{{ $item['quantity'] }}"
                                               min="1" max="99"
                                               class="w-16 border border-gray-200 rounded-lg px-2 py-1 text-sm text-center focus:outline-none focus:ring-2 focus:ring-brand-400"
                                               onchange="this.form.submit()">
                                    </form>

                                    {{-- Remove --}}
                                    <form method="POST" action="{{ route('cart.remove', $productId) }}">
                                        @csrf
                                        @method('DELETE')
                                        <button type="submit" class="text-red-400 hover:text-red-600 text-sm transition">
                                            🗑 Remove
                                        </button>
                                    </form>
                                </div>
                            </div>

                            <div class="text-right text-sm font-semibold text-gray-700 mt-2">
                                Subtotal: ₱{{ number_format($item['price'] * $item['quantity'], 2) }}
                            </div>
                        </div>
                    </div>
                @endforeach

                {{-- Clear Cart --}}
                <div class="text-right">
                    <form method="POST" action="{{ route('cart.clear') }}">
                        @csrf
                        @method('DELETE')
                        <button type="submit" class="text-sm text-gray-400 hover:text-red-500 transition"
                                onclick="return confirm('Clear your entire cart?')">
                            Clear Cart
                        </button>
                    </form>
                </div>
            </div>

            {{-- Order Summary --}}
            <div class="lg:col-span-1">
                <div class="bg-white rounded-2xl shadow-sm border border-gray-100 p-6 sticky top-24">
                    <h3 class="font-display text-lg font-bold text-gray-900 mb-5">Order Summary</h3>

                    <div class="space-y-3 text-sm mb-5">
                        <div class="flex justify-between text-gray-600">
                            <span>Subtotal ({{ collect($cart)->sum('quantity') }} items)</span>
                            <span>₱{{ number_format($total, 2) }}</span>
                        </div>
                        <div class="flex justify-between text-gray-600">
                            <span>Shipping Fee</span>
                            <span>₱150.00</span>
                        </div>
                        <div class="border-t border-gray-100 pt-3 flex justify-between font-bold text-gray-900 text-base">
                            <span>Total</span>
                            <span class="text-brand-700">₱{{ number_format($total + 150, 2) }}</span>
                        </div>
                    </div>

                    <a href="{{ route('checkout') }}"
                       class="block w-full text-center bg-brand-700 text-white font-bold py-3 rounded-xl hover:bg-brand-800 transition hover:shadow-lg">
                        Proceed to Checkout
                    </a>

                    <a href="{{ route('products.index') }}"
                       class="block text-center text-sm text-brand-600 hover:underline mt-3">
                        ← Continue Shopping
                    </a>

                    <div class="mt-5 pt-5 border-t border-gray-100 space-y-2 text-xs text-gray-400">
                        <p>🔒 Secure checkout</p>
                        <p>🚚 Estimated delivery: 5-10 business days</p>
                        <p>💳 Cash on Delivery available</p>
                    </div>
                </div>
            </div>
        </div>
    @endif
</div>

@endsection