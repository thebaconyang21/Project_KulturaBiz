@extends('layouts.app')

@section('title', 'Manage Categories')

@section('content')

<div class="flex min-h-screen">
    
    <!-- <aside class="w-56 bg-brand-900 text-white shrink-0 min-h-screen pt-6 hidden lg:block">
        <div class="px-5 mb-8">
            <p class="text-brand-300 text-xs font-bold uppercase tracking-widest mb-1">Administration</p>
        </div>
        <nav class="space-y-1 px-3">
            @foreach([['admin.dashboard','','Dashboard'],['admin.users','','Users'],['admin.products','','Products'],['admin.categories','','Categories'],['admin.orders','','Orders']] as [$r,$i,$l])
                <a href="{{ route($r) }}" class="flex items-center gap-3 px-3 py-2 rounded-lg text-sm transition {{ request()->routeIs($r) ? 'bg-brand-700 text-white font-semibold' : 'text-brand-200 hover:bg-brand-800' }}">{{ $i }} {{ $l }}</a>
            @endforeach
        </nav>
    </aside> -->
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
        <h1 class="font-display text-2xl font-bold text-gray-900 mb-8">Manage Categories</h1>

        <div class="grid grid-cols-1 lg:grid-cols-3 gap-6">

            {{-- Add New Category --}}
            <div class="lg:col-span-1">
                <div class="bg-white rounded-2xl shadow-sm border border-gray-100 p-6">
                    <h2 class="font-semibold text-gray-900 mb-5">Add New Category</h2>
                    <form method="POST" action="{{ route('admin.categories.store') }}" class="space-y-4">
                        @csrf
                        <div>
                            <label class="block text-sm font-medium text-gray-700 mb-1">Category Name *</label>
                            <input type="text" name="name" required placeholder="e.g., Pottery"
                                   class="w-full border border-gray-200 rounded-xl px-4 py-2.5 text-sm focus:outline-none focus:ring-2 focus:ring-brand-400 @error('name') border-red-400 @enderror">
                            @error('name')<p class="text-red-500 text-xs mt-1">{{ $message }}</p>@enderror
                        </div>
                        <div>
                            <label class="block text-sm font-medium text-gray-700 mb-1">Description</label>
                            <textarea name="description" rows="3" placeholder="Brief description of this category…"
                                      class="w-full border border-gray-200 rounded-xl px-4 py-2.5 text-sm focus:outline-none focus:ring-2 focus:ring-brand-400"></textarea>
                        </div>
                        <button type="submit" class="w-full bg-brand-700 text-white font-bold py-2.5 rounded-xl hover:bg-brand-800 transition">
                            Add Category
                        </button>
                    </form>
                </div>
            </div>

            {{-- Category List --}}
            <div class="lg:col-span-2">
                <div class="bg-white rounded-2xl shadow-sm border border-gray-100 overflow-hidden">
                    <table class="w-full text-sm">
                        <thead class="bg-gray-50 border-b border-gray-100">
                            <tr class="text-left text-gray-500 text-xs uppercase">
                                <th class="px-5 py-3 font-medium">Category</th>
                                <th class="px-5 py-3 font-medium">Slug</th>
                                <th class="px-5 py-3 font-medium">Products</th>
                                <th class="px-5 py-3 font-medium">Actions</th>
                            </tr>
                        </thead>
                        <tbody class="divide-y divide-gray-50">
                            @forelse($categories as $category)
                                <tr class="hover:bg-gray-50">
                                    <td class="px-5 py-3">
                                        <p class="font-medium text-gray-900">{{ $category->name }}</p>
                                        @if($category->description)
                                            <p class="text-xs text-gray-400 mt-0.5">{{ Str::limit($category->description, 60) }}</p>
                                        @endif
                                    </td>
                                    <td class="px-5 py-3 text-gray-400 font-mono text-xs">{{ $category->slug }}</td>
                                    <td class="px-5 py-3">
                                        <span class="badge-secondary">{{ $category->products_count }} products</span>
                                    </td>
                                    <td class="px-5 py-3">
                                        <button onclick="openDeleteModal('{{ route('admin.categories.delete', $category->id) }}', 'Are you sure you want to delete the category {{ addslashes($category->name) }}? This cannot be undone.')"
                                                class="inline-flex items-center gap-1 bg-red-50 text-red-600 text-xs font-semibold px-3 py-1.5 rounded-lg border border-red-200 hover:bg-red-100 transition whitespace-nowrap">
                                            <i class="fa-solid fa-trash"></i> Delete
                                        </button>
                                    </td>
                                </tr>
                            @empty
                                <tr><td colspan="4" class="text-center py-12 text-gray-400">No categories yet.</td></tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
    </div>
</div>

@endsection
