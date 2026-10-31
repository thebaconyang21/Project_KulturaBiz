@extends('layouts.app')

@section('title', 'Complete Payment')

@section('content')

<div class="min-h-screen bg-gray-50 flex items-center justify-center py-12 px-4">
    <div class="w-full max-w-md">

        <div class="text-center mb-6">
            <div class="inline-flex items-center gap-2 bg-amber-100 border border-amber-300 rounded-full px-4 py-2 text-sm font-bold text-amber-900 mb-4">
                DEMO ONLY — NO REAL PAYMENT
            </div>
            <h1 class="font-display text-2xl font-bold text-gray-900">Payment Demo</h1>
            <p class="text-sm text-gray-600 mt-2">This page does not connect to a payment provider. No money will be charged.</p>
        </div>

        {{-- Order Summary Card --}}
        <div class="bg-white rounded-2xl border border-gray-200 p-5 mb-5 shadow-sm">
            <div class="flex items-center gap-3 mb-4">
                <span class="text-2xl">⬡</span>
                <div>
                    <p class="font-semibold text-gray-900">KulturaBiz</p>
                    <p class="text-xs text-gray-400">Order #{{ $order->order_number }}</p>
                </div>
                <div class="ml-auto text-right">
                    <p class="font-bold text-2xl text-gray-900">₱{{ number_format($order->total_amount, 2) }}</p>
                    <p class="text-xs text-gray-400">PHP</p>
                </div>
            </div>

            <div class="border-t border-gray-100 pt-3 space-y-1 text-sm text-gray-500">
                @foreach($order->items as $item)
                    <div class="flex justify-between">
                        <span>{{ $item->product_name }} ×{{ $item->quantity }}</span>
                        <span>₱{{ number_format($item->subtotal, 2) }}</span>
                    </div>
                @endforeach
                <div class="flex justify-between text-gray-400">
                    <span>Shipping</span>
                    <span>₱{{ number_format($order->shipping_fee, 2) }}</span>
                </div>
            </div>
        </div>

        {{-- Payment Method Form --}}
        <div class="bg-white rounded-2xl border border-gray-200 p-6 shadow-sm">

            {{-- Method display --}}
            <div class="flex items-center gap-3 mb-6 p-4 bg-gray-50 rounded-xl border border-gray-200">
                <span class="text-3xl">{{ $methodInfo['icon'] }}</span>
                <div>
                    <p class="font-semibold text-gray-900">{{ $methodInfo['label'] }}</p>
                    <p class="text-xs text-amber-700 font-medium">Demo only — this order will remain unpaid</p>
                </div>
            </div>

            <div class="bg-amber-50 border border-amber-200 rounded-xl p-4 mb-6 text-sm text-amber-900">
                Do not enter payment details or send money. This demo does not initiate a GCash, Maya, or bank transfer.
            </div>

            {{-- Demo-only action; it must not confirm or settle the order. --}}
            <form method="POST" action="{{ route('payments.process', $order->id) }}" id="payment-form">
                @csrf

                <button type="submit"
                        class="w-full bg-blue-600 text-white font-bold py-4 rounded-xl hover:bg-blue-700 transition-all text-lg">
                    Run demo — no charge
                </button>
            </form>

            <p class="text-center text-xs text-amber-700 mt-4">
                Submitting this demo will not mark the order as paid.
            </p>
        </div>

        <p class="text-center text-xs text-gray-400 mt-4">
            <a href="{{ route('orders.mine') }}" class="hover:text-gray-600">← Back to my orders</a>
        </p>
    </div>
</div>

@endsection