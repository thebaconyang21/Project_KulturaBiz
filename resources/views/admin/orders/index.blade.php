@extends('layouts.app')

@section('title', 'Manage Orders')

@section('content')

<div class="flex min-h-screen">
    <aside class="w-56 bg-brand-900 text-white shrink-0 min-h-screen pt-6 hidden lg:block">
        <div class="px-5 mb-8">
            <p class="text-brand-300 text-xs font-bold uppercase tracking-widest mb-1">Administration</p>
        </div>
        <nav class="space-y-1 px-3">
            @foreach([['admin.dashboard','','Dashboard'],['admin.users','','Users'],['admin.products','','Products'],['admin.categories','','Categories'],['admin.orders','','Orders']] as [$r,$i,$l])
                <a href="{{ route($r) }}" class="flex items-center gap-3 px-3 py-2 rounded-lg text-sm transition {{ request()->routeIs($r) ? 'bg-brand-700 text-white font-semibold' : 'text-brand-200 hover:bg-brand-800' }}">{{ $i }} {{ $l }}</a>
            @endforeach
        </nav>
    </aside>

    <div class="flex-1 bg-gray-50 p-8">
        <h1 class="font-display text-2xl font-bold text-gray-900 mb-8">Manage Orders</h1>

        {{-- Status filter --}}
        <div class="flex flex-wrap gap-2 mb-6">
            <a href="{{ route('admin.orders') }}"
               class="px-4 py-1.5 rounded-full text-sm font-medium transition {{ !request('status') ? 'bg-brand-700 text-white' : 'bg-white border border-gray-200 text-gray-600 hover:border-brand-400' }}">
               All
            </a>
            @foreach(['pending','processing','shipped','delivered','cancelled'] as $s)
                <a href="{{ route('admin.orders', ['status' => $s]) }}"
                   class="px-4 py-1.5 rounded-full text-sm font-medium capitalize transition {{ request('status') === $s ? 'bg-brand-700 text-white' : 'bg-white border border-gray-200 text-gray-600 hover:border-brand-400' }}">
                    {{ $s }}
                </a>
            @endforeach
        </div>

        <div class="bg-white rounded-2xl shadow-sm border border-gray-100 overflow-hidden">
            <table class="w-full text-sm">
                <thead class="bg-gray-50 border-b border-gray-100">
                    <tr class="text-left text-gray-500 text-xs uppercase">
                        <th class="px-5 py-3 font-medium">Order #</th>
                        <th class="px-5 py-3 font-medium">Customer</th>
                        <th class="px-5 py-3 font-medium">Items</th>
                        <th class="px-5 py-3 font-medium">Total</th>
                        <th class="px-5 py-3 font-medium">Payment</th>
                        <th class="px-5 py-3 font-medium">Status</th>
                        <th class="px-5 py-3 font-medium">Date</th>
                        <th class="px-5 py-3 font-medium">Update</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-gray-50">
                    @forelse($orders as $order)
                        <tr class="hover:bg-gray-50">
                            <td class="px-5 py-3 font-mono font-bold text-brand-700 text-xs">{{ $order->order_number }}</td>
                            <td class="px-5 py-3">
                                <p class="font-medium text-gray-900">{{ $order->customer->name }}</p>
                                <p class="text-xs text-gray-400">{{ $order->city }}, {{ $order->province }}</p>
                            </td>
                            <td class="px-5 py-3 text-gray-500">{{ $order->items->count() }} item(s)</td>
                            <td class="px-5 py-3 font-bold text-brand-700">{{ $order->formatted_total }}</td>
                            <td class="px-5 py-3">
                                <span class="text-xs uppercase font-mono text-gray-500">{{ $order->payment_method }}</span><br>
                                <span class="{{ $order->payment_status === 'paid' ? 'text-green-600' : 'text-amber-500' }} text-xs font-medium">
                                    {{ ucfirst($order->payment_status) }}
                                </span>
                            </td>
                            <td class="px-5 py-3"><span class="{{ $order->status_badge }}">{{ $order->status_label }}</span></td>
                            <td class="px-5 py-3 text-gray-400 text-xs">{{ $order->created_at->format('M d, Y') }}</td>
                            <td class="px-5 py-3">
                                <form method="POST" action="{{ route('admin.orders.status', $order->id) }}">
                                    @csrf @method('PATCH')
                                    <select name="status" onchange="this.form.submit()"
                                            class="text-xs border border-gray-200 rounded-lg px-2 py-1 focus:outline-none focus:ring-1 focus:ring-brand-400">
                                        @foreach(['pending','processing','shipped','delivered','cancelled'] as $s)
                                            <option value="{{ $s }}" @selected($order->status === $s)>{{ ucfirst($s) }}</option>
                                        @endforeach
                                    </select>
                                </form>
                            </td>
                        </tr>
                    @empty
                        <tr><td colspan="8" class="text-center py-12 text-gray-400">No orders found.</td></tr>
                    @endforelse
                </tbody>
            </table>
            <div class="px-5 py-4 border-t border-gray-100">{{ $orders->links() }}</div>
        </div>
    </div>
</div>

@endsection
