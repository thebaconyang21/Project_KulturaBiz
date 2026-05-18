@extends('layouts.app')

@section('title', 'Manage Cultural Stories')

@section('content')
<div class="flex min-h-screen">
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
                {{ request()->routeIs($route) 
                        ? 'bg-brand-700 text-white font-semibold' 
                        : 'text-brand-200 hover:bg-brand-800 hover:text-white' }}">

                    <i class="fa-solid fa-{{ $icon }} text-xl"></i>

                    {{ $label }}
                </a>

            @endforeach
        </nav>
    </aside>

    <div class="flex-1 bg-gray-50 p-8">
        <h1 class="font-display text-2xl font-bold text-gray-900 mb-2">Manage Cultural Stories</h1>
        <p class="text-gray-400 text-sm mb-6">Toggle which stories appear as featured on the homepage. Maximum 3 featured stories recommended.</p>

        {{-- Featured count --}}
        @php $featuredCount = $stories->where('is_featured', true)->count(); @endphp
        <div class="bg-brand-50 border border-brand-200 rounded-xl px-4 py-3 mb-6 flex items-center gap-3">
            <i class="fa-solid fa-star text-brand-600"></i>
            <span class="text-sm text-brand-800 font-medium">
                Currently <strong>{{ $featuredCount }}</strong> featured {{ Str::plural('story', $featuredCount) }} showing on the homepage.
            </span>
        </div>

        <div class="bg-white rounded-2xl shadow-sm border border-gray-100 overflow-hidden">
            <table class="w-full text-sm">
                <thead class="bg-gray-50 border-b border-gray-100">
                    <tr class="text-left text-gray-500 text-xs uppercase">
                        <th class="px-5 py-3 font-medium">Story</th>
                        <th class="px-5 py-3 font-medium">Tribe</th>
                        <th class="px-5 py-3 font-medium">Author</th>
                        <th class="px-5 py-3 font-medium">Published</th>
                        <th class="px-5 py-3 font-medium text-center">Featured</th>
                        <th class="px-5 py-3 font-medium">Action</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-gray-50">
                    @forelse($stories as $story)
                        <tr class="hover:bg-gray-50 transition">
                            <td class="px-5 py-3">
                                <p class="font-medium text-gray-900 max-w-xs truncate">{{ $story->title }}</p>
                                <p class="text-xs text-gray-400 mt-0.5">{{ $story->location }}</p>
                            </td>
                            <td class="px-5 py-3">
                                <span class="bg-brand-100 text-brand-700 text-xs font-semibold px-2 py-1 rounded-full">
                                    {{ $story->tribe_community }}
                                </span>
                            </td>
                            <td class="px-5 py-3 text-gray-500">{{ $story->author->name ?? '—' }}</td>
                            <td class="px-5 py-3">
                                <span class="{{ $story->is_published ? 'bg-green-100 text-green-700' : 'bg-gray-100 text-gray-500' }} text-xs font-semibold px-2 py-1 rounded-full">
                                    {{ $story->is_published ? 'Published' : 'Hidden' }}
                                </span>
                            </td>
                            <td class="px-5 py-3 text-center">
                                @if($story->is_featured)
                                    <span class="inline-flex items-center gap-1 bg-yellow-100 text-yellow-700 text-xs font-bold px-3 py-1 rounded-full">
                                        <i class="fa-solid fa-star"></i> Featured
                                    </span>
                                @else
                                    <span class="inline-flex items-center gap-1 bg-gray-100 text-gray-400 text-xs px-3 py-1 rounded-full">
                                        Not Featured
                                    </span>
                                @endif
                            </td>
                            <td class="px-5 py-3">
                                <form method="POST" action="{{ route('admin.cultural.toggle-featured', $story->id) }}">
                                    @csrf @method('PATCH')
                                    <button class="inline-flex items-center gap-1 text-xs font-semibold px-3 py-1.5 rounded-lg border transition
                                        {{ $story->is_featured
                                            ? 'bg-yellow-50 text-yellow-700 border-yellow-200 hover:bg-yellow-100'
                                            : 'bg-brand-50 text-brand-700 border-brand-200 hover:bg-brand-100' }}">
                                        <i class="fa-solid fa-{{ $story->is_featured ? 'star-half-stroke' : 'star' }}"></i>
                                        {{ $story->is_featured ? 'Unfeature' : 'Set Featured' }}
                                    </button>
                                </form>
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="6" class="text-center py-12 text-gray-400">No cultural stories found.</td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
            <div class="px-5 py-4 border-t border-gray-100">
                {{ $stories->links() }}
            </div>
        </div>
    </div>
</div>
@endsection