@extends('layouts.app')

@section('title', 'Complete Payment')

@section('content')

<div class="min-h-screen bg-gray-50 flex items-center justify-center py-12 px-4">
    <div class="w-full max-w-md">

        {{-- PayMongo-style header --}}
        <div class="text-center mb-6">
            <div class="inline-flex items-center gap-2 bg-white border border-gray-200 rounded-full px-4 py-2 text-sm text-gray-600 mb-4">
                🔒 Secure Payment · Powered by
                <span class="font-bold text-blue-600">PayMongo</span>
                <span class="text-xs text-gray-400">(Simulated)</span>
            </div>
            <h1 class="font-display text-2xl font-bold text-gray-900">Complete Your Payment</h1>
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
        <div class="bg-white rounded-2xl border border-gray-200 p-6 shadow-sm" x-data="paymentForm()">

            {{-- Method display --}}
            <div class="flex items-center gap-3 mb-6 p-4 bg-gray-50 rounded-xl border border-gray-200">
                <span class="text-3xl">{{ $methodInfo['icon'] }}</span>
                <div>
                    <p class="font-semibold text-gray-900">{{ $methodInfo['label'] }}</p>
                    <p class="text-xs text-gray-400">{{ $methodInfo['provider'] === 'paymongo' ? 'Processed via PayMongo' : 'Standard transfer' }}</p>
                </div>
            </div>

            {{-- GCash fields --}}
            @if($order->payment_method === 'gcash')
                <div class="space-y-4 mb-6">
                    <div>
                        <label class="block text-sm font-medium text-gray-700 mb-1">GCash Mobile Number</label>
                        <input type="text" x-model="phone" placeholder="09XXXXXXXXX"
                               class="w-full border border-gray-200 rounded-xl px-4 py-3 text-sm focus:outline-none focus:ring-2 focus:ring-blue-400">
                    </div>
                    <div class="bg-blue-50 border border-blue-200 rounded-xl p-3 text-sm text-blue-700">
                        📱 In production, you would be redirected to GCash to approve the payment. This simulation skips that step.
                    </div>
                </div>
            @endif

            {{-- Maya fields --}}
            @if($order->payment_method === 'maya')
                <div class="space-y-4 mb-6">
                    <div>
                        <label class="block text-sm font-medium text-gray-700 mb-1">Maya Mobile Number</label>
                        <input type="text" x-model="phone" placeholder="09XXXXXXXXX"
                               class="w-full border border-gray-200 rounded-xl px-4 py-3 text-sm focus:outline-none focus:ring-2 focus:ring-green-400">
                    </div>
                </div>
            @endif

            {{-- Bank Transfer fields --}}
            @if($order->payment_method === 'bank_transfer')
                <div class="bg-amber-50 border border-amber-200 rounded-xl p-4 mb-6 text-sm">
                    <p class="font-semibold text-amber-800 mb-2">Bank Transfer Details</p>
                    <div class="space-y-1 text-amber-700">
                        <p><strong>Bank:</strong> BDO Unibank</p>
                        <p><strong>Account Name:</strong> KulturaBiz Inc.</p>
                        <p><strong>Account No:</strong> 1234-5678-90</p>
                        <p><strong>Reference:</strong> {{ $order->order_number }}</p>
                        <p><strong>Amount:</strong> ₱{{ number_format($order->total_amount, 2) }}</p>
                    </div>
                </div>
            @endif

            {{-- Simulated pay button --}}
            <form method="POST" action="{{ route('payments.process', $order->id) }}">
                @csrf
                <input type="hidden" name="intent_id" value="{{ request('intent_id') }}">

                <button type="submit" x-bind:disabled="processing"
                        @click="processing = true"
                        class="w-full bg-blue-600 text-white font-bold py-4 rounded-xl hover:bg-blue-700 transition-all disabled:opacity-60 disabled:cursor-wait text-lg">
                    <span x-show="!processing">
                        @if($order->payment_method === 'gcash') 📱 Pay with GCash
                        @elseif($order->payment_method === 'maya') 💳 Pay with Maya
                        @elseif($order->payment_method === 'bank_transfer') ✅ Confirm Transfer
                        @else 💳 Pay ₱{{ number_format($order->total_amount, 2) }}
                        @endif
                    </span>
                    <span x-show="processing">⏳ Processing…</span>
                </button>
            </form>

            <p class="text-center text-xs text-gray-400 mt-4">
                🔒 This is a simulated payment page. No real money is charged.
                @if($methodInfo['provider'] === 'paymongo')
                    In production, users would be redirected to PayMongo's secure hosted page.
                @endif
            </p>
        </div>

        <p class="text-center text-xs text-gray-400 mt-4">
            <a href="{{ route('orders.mine') }}" class="hover:text-gray-600">← Back to my orders</a>
        </p>
    </div>
</div>

@section('scripts')
<script>
function paymentForm() {
    return { processing: false, phone: '' };
}
</script>
@endsection

@endsection