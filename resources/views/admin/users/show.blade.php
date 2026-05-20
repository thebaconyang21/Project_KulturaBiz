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
                ['admin.cultural.index', 'book-open', 'Cultural Stories'],
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

            {{-- Top accent bar --}}
            <div class="h-3 bg-gradient-to-r from-brand-700 to-brand-500"></div>

            <div class="px-6 pb-6">
                <div class="flex flex-col sm:flex-row items-start sm:items-end gap-4 mt-4 mb-5">
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
    </div>
</div>

@endsection