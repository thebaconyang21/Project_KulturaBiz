@extends('layouts.app')

@section('title', 'Track Order #' . $order->order_number)

@section('content')

<div class="max-w-3xl mx-auto px-4 py-8">

    {{-- Header --}}
    <div class="flex items-center gap-4 mb-8">
        <a href="{{ route('orders.mine') }}" class="text-brand-600 hover:text-brand-800 text-sm">← My Orders</a>
        <div>
            <h1 class="font-display text-2xl font-bold text-brand-800">Track Order</h1>
            <p class="text-gray-500 text-sm">#{{ $order->order_number }}</p>
        </div>
    </div>

    {{-- Status Banner --}}
    <div class="bg-brand-700 text-white rounded-2xl p-6 mb-8">
        <div class="flex items-center justify-between mb-4">
            <div>
                <p class="text-brand-200 text-sm">Current Status</p>
                <p class="font-display text-2xl font-bold mt-1">{{ $order->status_label }}</p>
            </div>
            <div class="text-right">
                <p class="text-brand-200 text-sm">Courier</p>
                <p class="font-semibold">{{ $order->courier_name ?? 'TBD' }}</p>
                @if($order->tracking_number)
                    <p class="text-brand-300 text-xs mt-1">{{ $order->tracking_number }}</p>
                @endif
            </div>
        </div>
        @if($order->estimated_delivery && $order->status !== 'delivered' && $order->status !== 'cancelled')
            <div class="bg-white/10 rounded-xl px-4 py-2 text-sm">
                Estimated delivery: <strong>{{ $order->estimated_delivery->format('F d, Y') }}</strong>
            </div>
        @endif
        @if($order->status === 'delivered' && $order->delivered_at)
            <div class="bg-green-500/20 rounded-xl px-4 py-2 text-sm text-green-200">
                Delivered on {{ $order->delivered_at->format('F d, Y') }}
            </div>
        @endif
    </div>

    {{-- TRACKING STEPS --}}
    <div class="bg-white rounded-2xl shadow-sm border border-gray-100 p-6 mb-8">
        <h2 class="font-semibold text-gray-900 mb-6">Order Progress</h2>

        <div class="relative">
            {{-- Connector line --}}
            <div class="absolute left-6 top-6 bottom-6 w-0.5 bg-gray-200"></div>

            <div class="space-y-6">
                @foreach($order->tracking_steps as $step)
                    <div class="flex items-start gap-4">
                        {{-- Icon circle --}}
                        <div class="relative z-10 w-12 h-12 rounded-full flex items-center justify-center text-lg shrink-0
                            {{ $step['current'] ? 'bg-brand-700 text-white ring-4 ring-brand-200' : ($step['completed'] ? 'bg-brand-100 text-brand-700' : 'bg-gray-100 text-gray-400') }}">
                            {{ $step['icon'] }}
                        </div>
                        <div class="pt-2">
                            <p class="font-semibold {{ $step['completed'] ? 'text-gray-900' : 'text-gray-400' }}">
                                {{ $step['label'] }}
                            </p>
                            @if($step['time'])
                                <p class="text-xs text-gray-400 mt-0.5">{{ \Carbon\Carbon::parse($step['time'])->format('M d, Y — h:i A') }}</p>
                            @elseif(!$step['completed'])
                                <p class="text-xs text-gray-300 mt-0.5">Pending</p>
                            @endif
                        </div>
                        @if($step['current'])
                            <span class="ml-auto mt-2 bg-brand-100 text-brand-700 text-xs font-bold px-2 py-1 rounded-full">CURRENT</span>
                        @endif
                    </div>
                @endforeach
            </div>
        </div>
    </div>

    {{-- ORDER ITEMS --}}
    <div class="bg-white rounded-2xl shadow-sm border border-gray-100 p-6 mb-8">
        <h2 class="font-semibold text-gray-900 mb-4">Items Ordered</h2>
        <div class="space-y-4">
            @foreach($order->items as $item)
                <div class="flex gap-4 py-3 border-b border-gray-50 last:border-0">
                    <div class="w-16 h-16 bg-brand-50 rounded-xl overflow-hidden">
                        @if($item->product && $item->product->images)
                            <img src="{{ asset('storage/' . $item->product->images[0]) }}" alt="{{ $item->product_name }}"
                                 class="w-full h-full object-cover">
                        @else
                            <div class="w-full h-full flex items-center justify-center text-2xl opacity-30">🎨</div>
                        @endif
                    </div>
                    <div class="flex-1">
                        <p class="font-medium text-gray-900">{{ $item->product_name }}</p>
                        @if($item->product)
                            <p class="text-xs text-gray-400">by {{ $item->product->artisan->name }}</p>
                        @endif
                        <p class="text-sm text-gray-500 mt-1">Qty: {{ $item->quantity }} × ₱{{ number_format($item->price, 2) }}</p>
                    </div>
                    <div class="text-right">
                        <p class="font-bold text-brand-700">₱{{ number_format($item->subtotal, 2) }}</p>
                    </div>
                </div>
            @endforeach
        </div>

        {{-- Total --}}
        <div class="mt-4 pt-4 border-t border-gray-100 space-y-1 text-sm">
            <div class="flex justify-between text-gray-500">
                <span>Subtotal</span><span>₱{{ number_format($order->subtotal, 2) }}</span>
            </div>
            <div class="flex justify-between text-gray-500">
                <span>Shipping</span><span>₱{{ number_format($order->shipping_fee, 2) }}</span>
            </div>
            <div class="flex justify-between font-bold text-gray-900 text-base pt-2">
                <span>Total</span><span class="text-brand-700">{{ $order->formatted_total }}</span>
            </div>
        </div>
    </div>

    {{-- DELIVERY ADDRESS --}}
    <div class="bg-white rounded-2xl shadow-sm border border-gray-100 p-6 mb-8">
        <h2 class="font-semibold text-gray-900 mb-4">Delivery Address</h2>
        <div class="text-sm text-gray-600 space-y-1">
            <p class="font-semibold text-gray-900">{{ $order->recipient_name }}</p>
            <p>{{ $order->delivery_address }}</p>
            <p>{{ $order->city }}, {{ $order->province }} {{ $order->postal_code }}</p>
            <p>📞 {{ $order->contact_number }}</p>
        </div>
    </div>

    {{-- REVIEW SECTION (if delivered) --}}
    @if($order->status === 'delivered')
        <div class="bg-green-50 border border-green-200 rounded-2xl p-6">
            <h2 class="font-semibold text-gray-900 mb-4">⭐ Rate Your Purchase</h2>
            @foreach($order->items as $item)
                @php $existingReview = $order->reviews->where('product_id', $item->product_id)->first(); @endphp
                <div class="mb-4 p-4 bg-white rounded-xl border border-green-100">
                    <p class="font-medium text-sm mb-3">{{ $item->product_name }}</p>
                    @if($existingReview)
                        <div class="text-accent">{{ $existingReview->stars }}</div>
                        <p class="text-xs text-green-600 mt-1">You've reviewed this product</p>
                    @else
                        <form method="POST" action="{{ route('orders.review', [$order->id, $item->product_id]) }}" x-data="{ rating: 5 }">
                            @csrf
                            <div class="flex gap-1 mb-3 text-2xl cursor-pointer">
                                @for($s = 1; $s <= 5; $s++)
                                    <span @click="rating = {{ $s }}" :class="rating >= {{ $s }} ? 'text-accent' : 'text-gray-300'"
                                          class="transition">★</span>
                                @endfor
                            </div>
                            <input type="hidden" name="rating" :value="rating">
                            <input type="text" name="title" placeholder="Review title (optional)"
                                   class="w-full border border-gray-200 rounded-lg px-3 py-2 text-sm mb-2 focus:outline-none focus:ring-2 focus:ring-brand-400">
                            <textarea name="comment" rows="2" placeholder="Share your experience..."
                                      class="w-full border border-gray-200 rounded-lg px-3 py-2 text-sm mb-2 focus:outline-none focus:ring-2 focus:ring-brand-400"></textarea>
                            <button type="submit" class="bg-brand-700 text-white text-sm font-semibold px-4 py-2 rounded-lg hover:bg-brand-800 transition">
                                Submit Review
                            </button>
                        </form>
                    @endif
                </div>
            @endforeach
        </div>
    @endif

</div>

@endsection