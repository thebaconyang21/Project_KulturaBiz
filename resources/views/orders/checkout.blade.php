@extends('layouts.app')

@section('title', 'Checkout')

@section('content')

<div class="max-w-5xl mx-auto px-4 py-8">
    <h1 class="font-display text-3xl font-bold text-brand-800 mb-8">Checkout</h1>

    <form method="POST" action="{{ route('orders.place') }}">
        @csrf

        <div class="grid grid-cols-1 lg:grid-cols-3 gap-8">

            {{-- Checkout Form --}}
            <div class="lg:col-span-2 space-y-6">

                {{-- Delivery Details --}}
                <div class="bg-white rounded-2xl shadow-sm border border-gray-100 p-6">
                    <h2 class="font-semibold text-lg text-gray-900 mb-5">Delivery Details</h2>

                    <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                        <div class="md:col-span-2">
                            <label class="block text-sm font-medium text-gray-700 mb-1">Recipient Name *</label>
                            <input type="text" name="recipient_name" value="{{ old('recipient_name', auth()->user()->name) }}" required
                                   class="w-full border border-gray-200 rounded-xl px-4 py-2.5 text-sm focus:outline-none focus:ring-2 focus:ring-brand-400 @error('recipient_name') border-red-400 @enderror">
                            @error('recipient_name')<p class="text-red-500 text-xs mt-1">{{ $message }}</p>@enderror
                        </div>

                        <div class="md:col-span-2">
                            <label class="block text-sm font-medium text-gray-700 mb-1">Delivery Address *</label>
                            <textarea name="delivery_address" rows="2" required
                                      class="w-full border border-gray-200 rounded-xl px-4 py-2.5 text-sm focus:outline-none focus:ring-2 focus:ring-brand-400 @error('delivery_address') border-red-400 @enderror"
                                      placeholder="House No., Street, Barangay">{{ old('delivery_address', auth()->user()->address) }}</textarea>
                            @error('delivery_address')<p class="text-red-500 text-xs mt-1">{{ $message }}</p>@enderror
                        </div>

                        <div>
                            <label class="block text-sm font-medium text-gray-700 mb-1">City / Municipality *</label>
                            <input type="text" name="city" value="{{ old('city') }}" required
                                   class="w-full border border-gray-200 rounded-xl px-4 py-2.5 text-sm focus:outline-none focus:ring-2 focus:ring-brand-400">
                            @error('city')<p class="text-red-500 text-xs mt-1">{{ $message }}</p>@enderror
                        </div>

                        <div>
                            <label class="block text-sm font-medium text-gray-700 mb-1">Province *</label>
                            <input type="text" name="province" value="{{ old('province') }}" required
                                   class="w-full border border-gray-200 rounded-xl px-4 py-2.5 text-sm focus:outline-none focus:ring-2 focus:ring-brand-400">
                            @error('province')<p class="text-red-500 text-xs mt-1">{{ $message }}</p>@enderror
                        </div>

                        <div>
                            <label class="block text-sm font-medium text-gray-700 mb-1">Postal Code</label>
                            <input type="text" name="postal_code" value="{{ old('postal_code') }}"
                                   class="w-full border border-gray-200 rounded-xl px-4 py-2.5 text-sm focus:outline-none focus:ring-2 focus:ring-brand-400">
                        </div>

                        <div>
                            <label class="block text-sm font-medium text-gray-700 mb-1">Contact Number *</label>
                            <input type="text" name="contact_number" value="{{ old('contact_number', auth()->user()->phone) }}" required
                                   class="w-full border border-gray-200 rounded-xl px-4 py-2.5 text-sm focus:outline-none focus:ring-2 focus:ring-brand-400"
                                   placeholder="09XXXXXXXXX">
                            @error('contact_number')<p class="text-red-500 text-xs mt-1">{{ $message }}</p>@enderror
                        </div>
                    </div>
                </div>

                {{-- Payment Method --}}
                <div class="bg-white rounded-2xl shadow-sm border border-gray-100 p-6" x-data="{ payment: 'cod' }">
                    <h2 class="font-semibold text-lg text-gray-900 mb-5">Payment Method</h2>

                    <div class="space-y-3">
                        @foreach([
                            ['cod', '', 'Cash on Delivery', 'Pay when your order arrives'],
                            ['gcash', '', 'GCash', 'Mobile wallet payment (simulation)'],
                            ['bank_transfer', '', 'Bank Transfer', 'Direct bank deposit (simulation)'],
                        ] as [$value, $icon, $label, $desc])
                            <label class="cursor-pointer">
                                <input type="radio" name="payment_method" value="{{ $value }}" x-model="payment" class="sr-only">
                                <div :class="payment === '{{ $value }}' ? 'border-brand-500 bg-brand-50' : 'border-gray-200 hover:border-gray-300'"
                                     class="border-2 rounded-xl p-4 flex items-center gap-4 transition">
                                    <span class="text-2xl">{{ $icon }}</span>
                                    <div>
                                        <div class="font-semibold text-sm text-gray-900">{{ $label }}</div>
                                        <div class="text-xs text-gray-500">{{ $desc }}</div>
                                    </div>
                                    <div class="ml-auto" x-show="payment === '{{ $value }}'">
                                        <span class="w-5 h-5 bg-brand-600 rounded-full flex items-center justify-center text-white text-xs">✓</span>
                                    </div>
                                </div>
                            </label>
                        @endforeach
                    </div>
                </div>

                {{-- Notes --}}
                <div class="bg-white rounded-2xl shadow-sm border border-gray-100 p-6">
                    <h2 class="font-semibold text-lg text-gray-900 mb-3">Order Notes (Optional)</h2>
                    <textarea name="notes" rows="3"
                              class="w-full border border-gray-200 rounded-xl px-4 py-2.5 text-sm focus:outline-none focus:ring-2 focus:ring-brand-400"
                              placeholder="Any special instructions for the artisan or delivery...">{{ old('notes') }}</textarea>
                </div>
            </div>

            {{-- Order Summary Sidebar --}}
            <div class="lg:col-span-1">
                <div class="bg-white rounded-2xl shadow-sm border border-gray-100 p-6 sticky top-24">
                    <h3 class="font-display text-lg font-bold text-gray-900 mb-5">Order Summary</h3>

                    {{-- Items --}}
                    <div class="space-y-3 mb-4">
                        @foreach($cart as $item)
                            <div class="flex gap-3 text-sm">
                                <img src="{{ $item['image'] }}" alt="{{ $item['name'] }}"
                                     class="w-12 h-12 rounded-lg object-cover bg-brand-50">
                                <div class="flex-1">
                                    <div class="font-medium line-clamp-1">{{ $item['name'] }}</div>
                                    <div class="text-gray-400 text-xs">Qty: {{ $item['quantity'] }}</div>
                                </div>
                                <div class="font-semibold">₱{{ number_format($item['price'] * $item['quantity'], 2) }}</div>
                            </div>
                        @endforeach
                    </div>

                    <div class="border-t border-gray-100 pt-4 space-y-2 text-sm">
                        <div class="flex justify-between text-gray-600">
                            <span>Subtotal</span>
                            <span>₱{{ number_format($subtotal, 2) }}</span>
                        </div>
                        <div class="flex justify-between text-gray-600">
                            <span>Shipping Fee</span>
                            <span>₱{{ number_format($shippingFee, 2) }}</span>
                        </div>
                        <div class="flex justify-between font-bold text-base text-gray-900 pt-2 border-t border-gray-100">
                            <span>Total</span>
                            <span class="text-brand-700">₱{{ number_format($total, 2) }}</span>
                        </div>
                    </div>

                    <button type="submit"
                            class="block w-full text-center bg-brand-700 text-white font-bold py-3 rounded-xl hover:bg-brand-800 transition hover:shadow-lg mt-5">
                        Place Order
                    </button>

                    <p class="text-xs text-gray-400 text-center mt-3">
                        By placing your order, you agree to our terms of service.
                    </p>
                </div>
            </div>
        </div>
    </form>
</div>

@endsection