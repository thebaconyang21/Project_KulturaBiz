@extends('layouts.app')

@section('title', 'Manage Users')

@section('content')

<div class="flex min-h-screen">

    {{-- Sidebar --}}
    <aside class="w-56 bg-brand-900 text-white shrink-0 min-h-screen pt-6 hidden lg:block">
        <div class="px-5 mb-8">
            <p class="text-brand-300 text-xs font-bold uppercase tracking-widest mb-1">Administration</p>
            <p class="text-white font-semibold text-sm">{{ auth()->user()->name }}</p>
        </div>
        <nav class="space-y-1 px-3">
            @foreach([
                ['admin.dashboard', '', 'Dashboard'],
                ['admin.users',     '', 'Users'],
                ['admin.products',  '', 'Products'],
                ['admin.categories','', 'Categories'],
                ['admin.orders',    '', 'Orders'],
            ] as [$route, $icon, $label])
                <a href="{{ route($route) }}"
                   class="flex items-center gap-3 px-3 py-2 rounded-lg text-sm transition
                          {{ request()->routeIs($route) ? 'bg-brand-700 text-white font-semibold' : 'text-brand-200 hover:bg-brand-800 hover:text-white' }}">
                    {{ $icon }} {{ $label }}
                </a>
            @endforeach
        </nav>
    </aside>

    <div class="flex-1 bg-gray-50 p-8">
        <div class="flex items-center justify-between mb-8">
            <h1 class="font-display text-2xl font-bold text-gray-900">Manage Users</h1>
        </div>

        {{-- Filters --}}
        <form method="GET" action="{{ route('admin.users') }}" class="bg-white rounded-2xl border border-gray-100 shadow-sm p-5 mb-6 flex flex-wrap gap-3">
            <input type="text" name="search" value="{{ request('search') }}" placeholder="Search by name or email…"
                   class="border border-gray-200 rounded-lg px-3 py-2 text-sm focus:outline-none focus:ring-2 focus:ring-brand-400 w-64">
            <select name="role" class="border border-gray-200 rounded-lg px-3 py-2 text-sm focus:outline-none focus:ring-2 focus:ring-brand-400">
                <option value="">All Roles</option>
                <option value="artisan"  @selected(request('role') === 'artisan')>Artisans</option>
                <option value="customer" @selected(request('role') === 'customer')>Customers</option>
            </select>
            <button type="submit" class="bg-brand-700 text-white px-4 py-2 rounded-lg text-sm font-semibold hover:bg-brand-800 transition">Filter</button>
            @if(request()->anyFilled(['search','role']))
                <a href="{{ route('admin.users') }}" class="text-sm text-gray-400 hover:text-red-500 self-center">Clear</a>
            @endif
        </form>

        <div class="bg-white rounded-2xl shadow-sm border border-gray-100 overflow-hidden">
            <table class="w-full text-sm">
                <thead class="bg-gray-50 border-b border-gray-100">
                    <tr class="text-left text-gray-500 text-xs uppercase">
                        <th class="px-5 py-3 font-medium">User</th>
                        <th class="px-5 py-3 font-medium">Role</th>
                        <th class="px-5 py-3 font-medium">Status</th>
                        <th class="px-5 py-3 font-medium">Tribe / Region</th>
                        <th class="px-5 py-3 font-medium">Joined</th>
                        <th class="px-5 py-3 font-medium">Actions</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-gray-50">
                    @forelse($users as $user)
                        <tr class="hover:bg-gray-50 transition">
                            <td class="px-5 py-3">
                                <div class="flex items-center gap-3">
                                    <img src="{{ $user->profile_photo_url }}" alt="" class="w-9 h-9 rounded-full">
                                    <div>
                                        <p class="font-medium text-gray-900">{{ $user->name }}</p>
                                        <p class="text-xs text-gray-400">{{ $user->email }}</p>
                                    </div>
                                </div>
                            </td>
                            <td class="px-5 py-3">
                                <span class="{{ $user->role === 'artisan' ? 'badge-primary' : 'badge-secondary' }}">
                                    {{ ucfirst($user->role) }}
                                </span>
                            </td>
                            <td class="px-5 py-3">
                                <span class="{{ match($user->status) {
                                    'approved' => 'badge-success',
                                    'pending'  => 'badge-warning',
                                    'rejected' => 'badge-danger',
                                    default    => 'badge-secondary'
                                } }}">
                                    {{ ucfirst($user->status) }}
                                </span>
                            </td>
                            <td class="px-5 py-3 text-gray-500 text-xs">
                                {{ $user->tribe ?? '—' }} / {{ $user->region ?? '—' }}
                            </td>
                            <td class="px-5 py-3 text-gray-400 text-xs">
                                {{ $user->created_at->format('M d, Y') }}
                            </td>
                            <td class="px-5 py-3">
                                <div class="flex items-center gap-2">
                                    @if($user->role === 'artisan' && $user->status === 'pending')
                                        <form method="POST" action="{{ route('admin.users.approve', $user->id) }}">
                                            @csrf @method('PATCH')
                                            <button class="badge-success cursor-pointer hover:bg-green-200 transition">✅ Approve</button>
                                        </form>
                                        <form method="POST" action="{{ route('admin.users.reject', $user->id) }}">
                                            @csrf @method('PATCH')
                                            <button class="badge-danger cursor-pointer hover:bg-red-200 transition">❌ Reject</button>
                                        </form>
                                    @endif
                                    <form method="POST" action="{{ route('admin.users.delete', $user->id) }}"
                                          onsubmit="return confirm('Delete {{ $user->name }}? This cannot be undone.')">
                                        @csrf @method('DELETE')
                                        <button class="text-xs text-red-400 hover:text-red-600 transition">🗑 Delete</button>
                                    </form>
                                </div>
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="6" class="text-center py-12 text-gray-400">No users found.</td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
            <div class="px-5 py-4 border-t border-gray-100">
                {{ $users->links() }}
            </div>
        </div>
    </div>
</div>

@endsection
