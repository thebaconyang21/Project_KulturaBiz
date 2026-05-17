@extends('layouts.app')

@section('title', $user->name . ' — User Details')

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
                ['admin.dashboard', 'gauge', 'Dashboard'],
                ['admin.users', 'users', 'Users'],
                ['admin.products', 'box', 'Products'],
                ['admin.categories', 'layer-group', 'Categories'],
                ['admin.orders', 'cart-shopping', 'Orders'],
            ] as [$route, $icon, $label])
                <a href="{{ route($route) }}"
                class="flex items-center gap-3 px-3 py-2 rounded-lg text-sm transition
                        {{ request()->routeIs($route) ? 'bg-brand-700 text-white font-semibold' : 'text-brand-200 hover:bg-brand-800 hover:text-white' }}">
                    <i class="fa-solid fa-{{ $icon }} text-xl"></i> {{ $label }}
                </a>
            @endforeach
        </nav>
    </aside>

    <div class="flex-1 bg-gray-50 p-8">

        {{-- Back button --}}
        <div class="mb-6">
            <a href="{{ route('admin.users') }}"
               class="inline-flex items-center gap-2 text-brand-600 hover:text-brand-800 text-sm font-medium transition">
                <i class="fa-solid fa-arrow-left"></i>
                Back to Manage Users
            </a>
        </div>

        {{-- Profile Header --}}
        <div class="bg-white rounded-2xl shadow-sm border border-gray-100 overflow-hidden mb-6">

            {{-- Cover banner --}}
            <div class="h-36 relative overflow-hidden">
                @if($user->cover_photo)
                    <img src="{{ asset('storage/' . $user->cover_photo) }}"
                         class="w-full h-full object-cover">
                @else
                    <div class="w-full h-full bg-gradient-to-br from-brand-700 to-brand-900"></div>
                @endif
            </div>

            <div class="px-6 pb-6">
                <div class="flex flex-col sm:flex-row items-start sm:items-end gap-4 -mt-12 mb-5">
                    {{-- Profile photo --}}
                    <img src="{{ $user->profile_photo_url }}"
                         alt="{{ $user->name }}"
                         class="w-24 h-24 rounded-2xl object-cover border-4 border-white shadow-lg shrink-0">

                    <div class="sm:mb-1 flex-1">
                        <div class="flex flex-wrap items-center gap-2 mb-1">
                            <h1 class="font-display text-2xl font-bold text-gray-900">{{ $user->name }}</h1>
                            {{-- Role badge --}}
                            <span class="{{ $user->isArtisan() ? 'bg-brand-100 text-brand-700' : 'bg-blue-100 text-blue-700' }} text-xs font-bold px-3 py-1 rounded-full">
                                {{ ucfirst($user->role) }}
                            </span>
                            {{-- Status badge --}}
                            <span class="{{ match($user->status) {
                                'approved' => 'bg-green-100 text-green-700',
                                'pending'  => 'bg-amber-100 text-amber-700',
                                'rejected' => 'bg-red-100 text-red-700',
                                default    => 'bg-gray-100 text-gray-700'
                            } }} text-xs font-bold px-3 py-1 rounded-full">
                                {{ ucfirst($user->status) }}
                            </span>
                        </div>
                        <p class="text-gray-500 text-sm">{{ $user->email }}</p>
                        @if($user->phone)
                            <p class="text-gray-400 text-sm mt-0.5">
                                <i class="fa-solid fa-phone text-xs mr-1"></i>{{ $user->phone }}
                            </p>
                        @endif
                    </div>

                    {{-- Action buttons --}}
                    <div class="flex gap-2 sm:mb-1">
                        @if($user->isArtisan() && $user->status === 'pending')
                            <form method="POST" action="{{ route('admin.users.approve', $user->id) }}">
                                @csrf @method('PATCH')
                                <button class="inline-flex items-center gap-2 bg-green-600 text-white font-semibold px-4 py-2 rounded-xl hover:bg-green-700 transition shadow-sm">
                                    <i class="fa-solid fa-circle-check"></i> Approve
                                </button>
                            </form>
                            <form method="POST" action="{{ route('admin.users.reject', $user->id) }}">
                                @csrf @method('PATCH')
                                <button class="inline-flex items-center gap-2 bg-red-50 text-red-600 font-semibold px-4 py-2 rounded-xl border border-red-200 hover:bg-red-100 transition">
                                    <i class="fa-solid fa-circle-xmark"></i> Reject
                                </button>
                            </form>
                        @elseif($user->isArtisan() && $user->status === 'approved')
                            <span class="inline-flex items-center gap-2 bg-green-50 text-green-700 font-semibold px-4 py-2 rounded-xl border border-green-200">
                                <i class="fa-solid fa-circle-check"></i> Approved Artisan
                            </span>
                        @endif

                        <form method="POST" action="{{ route('admin.users.delete', $user->id) }}"
                              onsubmit="return confirm('Delete {{ $user->name }}? This cannot be undone.')">
                            @csrf @method('DELETE')
                            <button class="inline-flex items-center gap-2 bg-red-50 text-red-600 font-semibold px-4 py-2 rounded-xl border border-red-200 hover:bg-red-100 transition">
                                <i class="fa-solid fa-trash"></i> Delete User
                            </button>
                        </form>
                    </div>
                </div>

                {{-- Info grid --}}
                <div class="grid grid-cols-2 md:grid-cols-4 gap-4">
                    @if($user->isArtisan())
                        <div class="bg-brand-50 rounded-xl p-4">
                            <p class="text-xs text-brand-500 mb-1">Shop Name</p>
                            <p class="font-semibold text-brand-800 text-sm">{{ $user->shop_name ?? '—' }}</p>
                        </div>
                        <div class="bg-brand-50 rounded-xl p-4">
                            <p class="text-xs text-brand-500 mb-1">Tribe</p>
                            <p class="font-semibold text-brand-800 text-sm">{{ $user->tribe ?? '—' }}</p>
                        </div>
                        <div class="bg-brand-50 rounded-xl p-4">
                            <p class="text-xs text-brand-500 mb-1">Region</p>
                            <p class="font-semibold text-brand-800 text-sm">{{ $user->region ?? '—' }}</p>
                        </div>
                        <div class="bg-brand-50 rounded-xl p-4">
                            <p class="text-xs text-brand-500 mb-1">Member Since</p>
                            <p class="font-semibold text-brand-800 text-sm">{{ $user->created_at->format('M d, Y') }}</p>
                        </div>
                    @else
                        <div class="bg-gray-50 rounded-xl p-4">
                            <p class="text-xs text-gray-400 mb-1">Address</p>
                            <p class="font-semibold text-gray-700 text-sm">{{ $user->address ?? '—' }}</p>
                        </div>
                        <div class="bg-gray-50 rounded-xl p-4">
                            <p class="text-xs text-gray-400 mb-1">Member Since</p>
                            <p class="font-semibold text-gray-700 text-sm">{{ $user->created_at->format('M d, Y') }}</p>
                        </div>
                        <div class="bg-gray-50 rounded-xl p-4">
                            <p class="text-xs text-gray-400 mb-1">Total Orders</p>
                            <p class="font-semibold text-gray-700 text-sm">{{ $orders->count() }}</p>
                        </div>
                    @endif
                </div>
            </div>
        </div>

        {{-- Bio --}}
        @if($user->bio)
            <div class="bg-white rounded-2xl shadow-sm border border-gray-100 p-6 mb-6">
                <h2 class="font-semibold text-gray-900 mb-3 flex items-center gap-2">
                    <i class="fa-solid fa-pen-to-square text-brand-600"></i>
                    Bio
                </h2>
                <p class="text-gray-600 text-sm leading-relaxed">{{ $user->bio }}</p>
            </div>
        @endif

        {{-- ARTISAN: Stats + Products --}}
        @if($user->isArtisan())

            {{-- Sales stats --}}
            <div class="grid grid-cols-1 md:grid-cols-3 gap-4 mb-6">
                <div class="bg-white rounded-2xl shadow-sm border border-gray-100 p-5">
                    <p class="text-xs text-gray-400 mb-1">Total Products</p>
                    <p class="font-display text-2xl font-bold text-brown-900">{{ $products->count() }}</p>
                </div>
                <div class="bg-white rounded-2xl shadow-sm border border-gray-100 p-5">
                    <p class="text-xs text-gray-400 mb-1">Total Orders</p>
                    <p class="font-display text-2xl font-bold text-brown-900">{{ $totalOrders }}</p>
                </div>
                <div class="bg-white rounded-2xl shadow-sm border border-gray-100 p-5">
                    <p class="text-xs text-gray-400 mb-1">Revenue (Delivered)</p>
                    <p class="font-display text-2xl font-bold text-brown-900">₱{{ number_format($totalSales, 2) }}</p>
                </div>
            </div>

            {{-- Products table --}}
            <div class="bg-white rounded-2xl shadow-sm border border-gray-100 p-6">
                <h2 class="font-semibold text-gray-900 mb-5 flex items-center gap-2">
                    <i class="fa-solid fa-box text-brand-600"></i>
                    Products by {{ $user->name }}
                </h2>

                @if($products->count() > 0)
                    <div class="overflow-x-auto">
                        <table class="w-full text-sm">
                            <thead>
                                <tr class="text-left text-gray-400 text-xs uppercase border-b border-gray-100">
                                    <th class="pb-3 font-medium">Product</th>
                                    <th class="pb-3 font-medium">Category</th>
                                    <th class="pb-3 font-medium">Price</th>
                                    <th class="pb-3 font-medium text-center">Stock</th>
                                    <th class="pb-3 font-medium">Status</th>
                                    <th class="pb-3 font-medium">Action</th>
                                </tr>
                            </thead>
                            <tbody class="divide-y divide-gray-50">
                                @foreach($products as $product)
                                    <tr class="hover:bg-gray-50">
                                        <td class="py-3">
                                            <div class="flex items-center gap-3">
                                                <div class="w-10 h-10 bg-brand-100 rounded-lg overflow-hidden shrink-0">
                                                    @if($product->images && count($product->images) > 0)
                                                        <img src="{{ asset('storage/' . $product->images[0]) }}"
                                                             class="w-full h-full object-cover">
                                                    @else
                                                        <div class="w-full h-full flex items-center justify-center">
                                                            <i class="fa-solid fa-image text-brand-300"></i>
                                                        </div>
                                                    @endif
                                                </div>
                                                <p class="font-medium text-gray-900 max-w-xs truncate">{{ $product->name }}</p>
                                            </div>
                                        </td>
                                        <td class="py-3 text-gray-500">{{ $product->category->name }}</td>
                                        <td class="py-3 font-semibold text-brand-700">₱{{ number_format($product->price, 2) }}</td>
                                        <td class="py-3 text-center font-bold {{ $product->stock === 0 ? 'text-red-500' : ($product->stock <= 5 ? 'text-orange-500' : 'text-green-600') }}">
                                            {{ $product->stock }}
                                        </td>
                                        <td class="py-3">
                                            <span class="{{ match($product->status) {
                                                'active'       => 'bg-green-100 text-green-700',
                                                'inactive'     => 'bg-gray-100 text-gray-600',
                                                'out_of_stock' => 'bg-red-100 text-red-600',
                                                default        => 'bg-gray-100 text-gray-600'
                                            } }} text-xs font-semibold px-2 py-1 rounded-full">
                                                {{ ucfirst(str_replace('_', ' ', $product->status)) }}
                                            </span>
                                        </td>
                                        <td class="py-3">
                                            <a href="{{ route('products.show', $product->slug) }}"
                                               target="_blank"
                                               class="inline-flex items-center gap-1 bg-brand-50 text-brand-700 text-xs font-semibold px-3 py-1.5 rounded-lg border border-brand-200 hover:bg-brand-100 transition">
                                                <i class="fa-solid fa-eye"></i> View
                                            </a>
                                        </td>
                                    </tr>
                                @endforeach
                            </tbody>
                        </table>
                    </div>
                @else
                    <div class="text-center py-10 text-gray-400">
                        <i class="fa-solid fa-box-open text-4xl mb-3"></i>
                        <p class="text-sm">This artisan has no products yet.</p>
                    </div>
                @endif
            </div>

        @endif

        {{-- CUSTOMER: Order history --}}
        @if($user->isCustomer() && $orders->count() > 0)
            <div class="bg-white rounded-2xl shadow-sm border border-gray-100 p-6">
                <h2 class="font-semibold text-gray-900 mb-5 flex items-center gap-2">
                    <i class="fa-solid fa-cart-shopping text-brand-600"></i>
                    Order History
                </h2>
                <div class="space-y-3">
                    @foreach($orders as $order)
                        <div class="flex items-center justify-between p-4 bg-gray-50 rounded-xl">
                            <div>
                                <p class="font-mono text-xs font-bold text-brand-700">{{ $order->order_number }}</p>
                                <p class="text-xs text-gray-400 mt-0.5">{{ $order->created_at->format('M d, Y') }} • {{ $order->items->count() }} item(s)</p>
                            </div>
                            <div class="text-right">
                                <p class="font-bold text-brand-700">₱{{ number_format($order->total_amount, 2) }}</p>
                                <span class="{{ $order->status_badge }} mt-1 inline-block">{{ $order->status_label }}</span>
                            </div>
                        </div>
                    @endforeach
                </div>
            </div>
        @endif

    </div>
</div>

@endsection