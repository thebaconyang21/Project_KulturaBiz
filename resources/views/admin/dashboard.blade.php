@extends('layouts.app')

@section('title', 'Admin Dashboard')

@section('content')

<div class="flex min-h-screen">

    {{-- Admin Sidebar --}}
    <aside class="w-56 bg-brand-900 text-white shrink-0 min-h-screen pt-6 hidden lg:block">
        <div class="px-5 mb-8">
            <p class="text-brand-300 text-xs font-bold uppercase tracking-widest mb-1">Administration</p>
            <p class="text-white font-semibold text-sm">{{ auth()->user()->name }}</p>
        </div>
        <nav class="space-y-1 px-3">
            @foreach([
                ['admin.dashboard', '', 'Dashboard'],
                ['admin.users', '', 'Users'],
                ['admin.products', '', 'Products'],
                ['admin.categories', '', 'Categories'],
                ['admin.orders', '', 'Orders'],
            ] as [$route, $icon, $label])
                <a href="{{ route($route) }}"
                   class="flex items-center gap-3 px-3 py-2 rounded-lg text-sm transition
                          {{ request()->routeIs($route) ? 'bg-brand-700 text-white font-semibold' : 'text-brand-200 hover:bg-brand-800 hover:text-white' }}">
                    <i class="fa-solid fa-{{ $icon }} text-xl"></i> {{ $label }}
                </a>
            @endforeach
        </nav>
    </aside>

    {{-- Main Content --}}
    <div class="flex-1 bg-gray-50 p-8">
        <div class="flex items-center justify-between mb-8">
            <h1 class="font-display text-2xl font-bold text-gray-900">Dashboard Overview</h1>
            <span class="text-sm text-gray-400">{{ now()->format('F d, Y') }}</span>
        </div>

        {{-- STATS CARDS --}}
        <div class="grid grid-cols-2 lg:grid-cols-4 gap-4 mb-8">
            @foreach([
                ['Total Revenue', '₱' . number_format($totalRevenue, 2), 'peso-sign', 'text-green-600', 'bg-green-50'],
                ['Total Orders', $totalOrders, 'box', 'text-blue-600', 'bg-blue-50'],
                ['Artisans', $totalArtisans,  'palette', 'text-brand-600', 'bg-brand-50'],
                ['Customers', $totalCustomers, 'users', 'text-purple-600', 'bg-purple-50'],
            ] as [$label, $value, $icon, $color, $bg])
                <div class="bg-white rounded-2xl shadow-sm border border-gray-100 p-5">
                    <div class="flex items-center justify-between mb-3">
                        <span class="text-gray-500 text-sm">{{ $label }}</span>
                        <div class="w-10 h-10 {{ $bg }} rounded-xl flex items-center justify-center text-xl"><i class="fa-solid fa-{{ $icon }} text-xl"></i></div>
                    </div>
                    <div class="font-display text-2xl font-bold {{ $color }}">{{ $value }}</div>
                </div>
            @endforeach
        </div>

        {{-- Pending Artisan Approvals Alert --}}
        @if($pendingArtisans > 0)
            <div class="bg-amber-50 border border-amber-200 rounded-2xl p-4 mb-8 flex items-center justify-between">
                <div class="flex items-center gap-3">
                    <span class="text-2xl">⚠️</span>
                    <div>
                        <p class="font-semibold text-amber-800">{{ $pendingArtisans }} Artisan(s) Awaiting Approval</p>
                        <p class="text-amber-600 text-sm">Review and approve artisan registrations</p>
                    </div>
                </div>
                <a href="{{ route('admin.users', ['role' => 'artisan']) }}"
                   class="bg-amber-500 text-white text-sm font-bold px-4 py-2 rounded-lg hover:bg-amber-600 transition">
                    Review Now
                </a>
            </div>
        @endif

        <div class="grid grid-cols-1 lg:grid-cols-2 gap-6">

            {{-- Orders by Status --}}
            <div class="bg-white rounded-2xl shadow-sm border border-gray-100 p-6">
                <h2 class="font-semibold text-gray-900 mb-4">Orders by Status</h2>
                @foreach(['pending' => '', 'processing' => '', 'shipped' => '', 'delivered' => '', 'cancelled' => ''] as $status => $icon)
                    @php $count = $ordersByStatus[$status] ?? 0; @endphp
                    <div class="flex items-center gap-3 py-2 border-b border-gray-50 last:border-0">
                        <span class="text-lg"><i class="fa-solid fa-{{ $icon }} text-xl"></i></span>
                        <span class="flex-1 text-sm text-gray-600 capitalize">{{ $status }}</span>
                        <span class="font-bold text-gray-900">{{ $count }}</span>
                        <div class="w-24 bg-gray-100 rounded-full h-2">
                            <div class="bg-brand-500 h-2 rounded-full" style="width: {{ $totalOrders > 0 ? ($count / $totalOrders * 100) : 0 }}%"></div>
                        </div>
                    </div>
                @endforeach
            </div>

            {{-- Top Products --}}
            <div class="bg-white rounded-2xl shadow-sm border border-gray-100 p-6">
                <h2 class="font-semibold text-gray-900 mb-4">Top Selling Products</h2>
                @forelse($topProducts as $i => $product)
                    <div class="flex items-center gap-3 py-2 border-b border-gray-50 last:border-0">
                        <span class="w-6 h-6 bg-brand-100 text-brand-700 rounded-full flex items-center justify-center text-xs font-bold">{{ $i+1 }}</span>
                        <div class="flex-1 min-w-0">
                            <p class="text-sm font-medium text-gray-900 truncate">{{ $product->name }}</p>
                            <p class="text-xs text-gray-400">{{ $product->artisan->name ?? 'Unknown' }}</p>
                        </div>
                        <span class="text-sm font-bold text-brand-700">{{ $product->total_sold ?? 0 }} sold</span>
                    </div>
                @empty
                    <p class="text-gray-400 text-sm text-center py-4">No sales data yet.</p>
                @endforelse
            </div>

            {{-- Recent Orders --}}
            <div class="bg-white rounded-2xl shadow-sm border border-gray-100 p-6 lg:col-span-2">
                <div class="flex items-center justify-between mb-4">
                    <h2 class="font-semibold text-gray-900">Recent Orders</h2>
                    <a href="{{ route('admin.orders') }}" class="text-brand-600 text-sm hover:underline">View all →</a>
                </div>
                <div class="overflow-x-auto">
                    <table class="w-full text-sm">
                        <thead>
                            <tr class="text-left text-gray-400 text-xs uppercase border-b border-gray-100">
                                <th class="pb-3 font-medium">Order</th>
                                <th class="pb-3 font-medium">Customer</th>
                                <th class="pb-3 font-medium">Amount</th>
                                <th class="pb-3 font-medium">Status</th>
                                <th class="pb-3 font-medium">Date</th>
                                <th class="pb-3 font-medium">Action</th>
                            </tr>
                        </thead>
                        <tbody class="divide-y divide-gray-50">
                            @foreach($recentOrders as $order)
                                <tr class="hover:bg-gray-50">
                                    <td class="py-3 font-mono text-brand-700 font-semibold">{{ $order->order_number }}</td>
                                    <td class="py-3 text-gray-700">{{ $order->customer->name }}</td>
                                    <td class="py-3 font-semibold">{{ $order->formatted_total }}</td>
                                    <td class="py-3"><span class="{{ $order->status_badge }}">{{ $order->status_label }}</span></td>
                                    <td class="py-3 text-gray-400">{{ $order->created_at->format('M d') }}</td>
                                    <td class="py-3">
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
                            @endforeach
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
    </div>
</div>

@endsection