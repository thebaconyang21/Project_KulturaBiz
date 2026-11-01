<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <title>@yield('title', 'KulturaBiz') — Digital Marketplace for Mindanaoan Artisans</title>

    {{-- Font Awesome 6 (Real Professional Icons) --}}
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.0/css/all.min.css">

    {{-- Google Fonts --}}
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link href="https://fonts.googleapis.com/css2?family=Playfair+Display:wght@600;700&family=Inter:wght@300;400;500;600&display=swap" rel="stylesheet">

    {{-- Tailwind CSS  --}}
    <script src="https://cdn.tailwindcss.com"></script>
    <script>
        tailwind.config = {
            theme: {
                extend: {
                    colors: {
                        brand: {
                            50:  '#fdf8f0',
                            100: '#faefd8',
                            200: '#f4d9a8',
                            300: '#ecc070',
                            400: '#e2a040',
                            500: '#c8832a',
                            600: '#a8651f',
                            700: '#8B4513', 
                            800: '#6b3410',
                            900: '#4a240b',
                        },
                        accent: '#D4AF37',
                    },
                    fontFamily: {
                        display: ['"Playfair Display"', 'serif'],
                        body:    ['Inter', 'sans-serif'],
                    }
                }
            }
        }
    </script>


    <script defer src="https://unpkg.com/alpinejs@3.x.x/dist/cdn.min.js"></script>

    <style>
        body { font-family: 'Inter', sans-serif; }
        .font-display { font-family: 'Playfair Display', serif; }
        [x-cloak] { display: none !important; }
        .hero-pattern {
            background-image: url("data:image/svg+xml,%3Csvg width='60' height='60' viewBox='0 0 60 60' xmlns='http://www.w3.org/2000/svg'%3E%3Cg fill='none' fill-rule='evenodd'%3E%3Cg fill='%23ffffff' fill-opacity='0.05'%3E%3Cpath d='M36 34v-4h-2v4h-4v2h4v4h2v-4h4v-2h-4zm0-30V0h-2v4h-4v2h4v4h2V6h4V4h-4zM6 34v-4H4v4H0v2h4v4h2v-4h4v-2H6zM6 4V0H4v4H0v2h4v4h2V6h4V4H6z'/%3E%3C/g%3E%3C/g%3E%3C/svg%3E");
        }
        .badge-success  { @apply bg-green-100 text-green-800 text-xs px-2 py-1 rounded-full font-medium; }
        .badge-warning  { @apply bg-yellow-100 text-yellow-800 text-xs px-2 py-1 rounded-full font-medium; }
        .badge-info     { @apply bg-blue-100 text-blue-800 text-xs px-2 py-1 rounded-full font-medium; }
        .badge-primary  { @apply bg-indigo-100 text-indigo-800 text-xs px-2 py-1 rounded-full font-medium; }
        .badge-danger   { @apply bg-red-100 text-red-800 text-xs px-2 py-1 rounded-full font-medium; }
        .badge-secondary { @apply bg-gray-100 text-gray-800 text-xs px-2 py-1 rounded-full font-medium; }
    </style>

    @yield('head')
</head>
<body class="bg-gray-50 text-gray-800 min-h-screen flex flex-col">

    {{-- NAVBAR --}}
    <nav class="sticky top-0 z-50 border-b border-white/10 bg-brand-900 text-white shadow-lg shadow-brand-900/10" x-data="{ mobileOpen: false }">
        <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
            <div class="flex items-center justify-between h-[4.5rem]">
                <a href="{{ route('home') }}" class="flex items-center gap-3 group" aria-label="KulturaBiz home">
                    <span class="flex h-10 w-10 items-center justify-center rounded-full border border-accent/50 bg-white/10 text-accent transition group-hover:bg-white/15">
                        <i class="fa-solid fa-hands-holding-circle text-lg" aria-hidden="true"></i>
                    </span>
                    <span>
                        <span class="block font-display text-xl font-bold tracking-wide leading-tight">KulturaBiz</span>
                        <span class="hidden sm:block text-[10px] uppercase tracking-[0.18em] text-brand-200">Mindanao made</span>
                    </span>
                </a>

                <div class="hidden md:flex items-center gap-1 text-sm font-medium">
                    <a href="{{ route('home') }}"
                       @class(['rounded-full px-4 py-2 transition', 'bg-white/10 text-accent' => request()->routeIs('home'), 'text-brand-100 hover:bg-white/10 hover:text-white' => !request()->routeIs('home')])>
                        Home
                    </a>
                    <a href="{{ route('products.index') }}"
                       @class(['rounded-full px-4 py-2 transition', 'bg-white/10 text-accent' => request()->routeIs('products.*'), 'text-brand-100 hover:bg-white/10 hover:text-white' => !request()->routeIs('products.*')])>
                        Shop
                    </a>
                    <a href="{{ route('cultural.index') }}"
                       @class(['rounded-full px-4 py-2 transition', 'bg-white/10 text-accent' => request()->routeIs('cultural.*'), 'text-brand-100 hover:bg-white/10 hover:text-white' => !request()->routeIs('cultural.*')])>
                        Stories
                    </a>

                    @auth
                        @if(auth()->user()->isCustomer())
                            <a href="{{ route('cart.index') }}" aria-label="Shopping cart" class="relative ml-2 flex h-10 w-10 items-center justify-center rounded-full text-brand-100 transition hover:bg-white/10 hover:text-white">
                                <i class="fa-solid fa-bag-shopping text-base" aria-hidden="true"></i>
                                @php $cartCount = collect(session()->get('cart', []))->sum('quantity'); @endphp
                                @if($cartCount > 0)
                                    <span class="absolute right-0 top-0 flex h-5 min-w-5 items-center justify-center rounded-full bg-accent px-1 text-[10px] font-bold text-brand-900">{{ $cartCount }}</span>
                                @endif
                            </a>
                        @endif

                        <div class="relative ml-2" x-data="{ open: false }">
                            <button @click="open = !open" :aria-expanded="open.toString()" aria-haspopup="true" class="flex items-center gap-2 rounded-full border border-white/15 bg-white/5 py-1.5 pl-1.5 pr-3 transition hover:bg-white/10">
                                <img src="{{ auth()->user()->profile_photo_url }}" alt="" class="h-8 w-8 rounded-full border border-accent/70 object-cover">
                                <span class="max-w-28 truncate">{{ auth()->user()->name }}</span>
                                <i class="fa-solid fa-chevron-down text-[10px] text-brand-200" aria-hidden="true"></i>
                            </button>
                            <div x-cloak x-show="open" x-transition.origin.top.right @click.away="open = false"
                                 class="absolute right-0 mt-3 w-56 overflow-hidden rounded-2xl border border-gray-100 bg-white py-2 text-gray-800 shadow-xl">
                                @if(auth()->user()->isAdmin())
                                    <a href="{{ route('admin.dashboard') }}" class="block px-4 py-2.5 text-sm hover:bg-brand-50"><i class="fa-solid fa-gauge-high mr-2 w-4 text-brand-600"></i>Admin Panel</a>
                                @elseif(auth()->user()->isArtisan())
                                    <a href="{{ route('artisan.dashboard') }}" class="block px-4 py-2.5 text-sm hover:bg-brand-50"><i class="fa-solid fa-chart-line mr-2 w-4 text-brand-600"></i>Artisan Dashboard</a>
                                    <a href="{{ route('artisan.products.index') }}" class="block px-4 py-2.5 text-sm hover:bg-brand-50"><i class="fa-solid fa-box-open mr-2 w-4 text-brand-600"></i>My Products</a>
                                    <a href="{{ route('artisan.orders') }}" class="block px-4 py-2.5 text-sm hover:bg-brand-50"><i class="fa-solid fa-receipt mr-2 w-4 text-brand-600"></i>My Orders</a>
                                @else
                                    <a href="{{ route('orders.mine') }}" class="block px-4 py-2.5 text-sm hover:bg-brand-50"><i class="fa-solid fa-box mr-2 w-4 text-brand-600"></i>My Orders</a>
                                @endif
                                <hr class="my-2 border-gray-100">
                                <form method="POST" action="{{ route('logout') }}">
                                    @csrf
                                    <button type="submit" class="block w-full px-4 py-2.5 text-left text-sm text-red-600 hover:bg-red-50">
                                        <i class="fa-solid fa-right-from-bracket mr-2 w-4" aria-hidden="true"></i>Sign out
                                    </button>
                                </form>
                            </div>
                        </div>
                    @else
                        <a href="{{ route('login') }}" class="ml-3 rounded-full px-4 py-2 text-brand-100 transition hover:bg-white/10 hover:text-white">Sign in</a>
                        <a href="{{ route('register') }}" class="rounded-full bg-accent px-5 py-2.5 font-semibold text-brand-900 shadow-sm transition hover:bg-yellow-300">
                            Join KulturaBiz
                        </a>
                    @endauth
                </div>

                <button @click="mobileOpen = !mobileOpen" :aria-expanded="mobileOpen.toString()" aria-label="Toggle navigation menu" class="flex h-10 w-10 items-center justify-center rounded-full border border-white/20 text-white transition hover:bg-white/10 md:hidden">
                    <i :class="mobileOpen ? 'fa-xmark' : 'fa-bars'" class="fa-solid text-lg" aria-hidden="true"></i>
                </button>
            </div>
        </div>

        <div x-cloak x-show="mobileOpen" x-transition class="space-y-1 border-t border-white/10 bg-brand-900 px-4 pb-5 pt-3 text-sm md:hidden">
            <a href="{{ route('home') }}" class="block rounded-xl px-4 py-3 hover:bg-white/10">Home</a>
            <a href="{{ route('products.index') }}" class="block rounded-xl px-4 py-3 hover:bg-white/10">Shop products</a>
            <a href="{{ route('cultural.index') }}" class="block rounded-xl px-4 py-3 hover:bg-white/10">Cultural stories</a>
            @auth
                @if(auth()->user()->isAdmin())
                    <a href="{{ route('admin.dashboard') }}" class="block rounded-xl px-4 py-3 hover:bg-white/10">Admin panel</a>
                @elseif(auth()->user()->isArtisan())
                    <a href="{{ route('artisan.dashboard') }}" class="block rounded-xl px-4 py-3 hover:bg-white/10">Artisan dashboard</a>
                    <a href="{{ route('artisan.products.index') }}" class="block rounded-xl px-4 py-3 hover:bg-white/10">My products</a>
                    <a href="{{ route('artisan.orders') }}" class="block rounded-xl px-4 py-3 hover:bg-white/10">My orders</a>
                @else
                    <a href="{{ route('cart.index') }}" class="block rounded-xl px-4 py-3 hover:bg-white/10">Shopping cart</a>
                    <a href="{{ route('orders.mine') }}" class="block rounded-xl px-4 py-3 hover:bg-white/10">My orders</a>
                @endif
                <form method="POST" action="{{ route('logout') }}">
                    @csrf
                    <button type="submit" class="block w-full rounded-xl px-4 py-3 text-left text-red-300 hover:bg-white/10">
                        <i class="fa-solid fa-right-from-bracket mr-1"></i> Sign out
                    </button>
                </form>
            @else
                <a href="{{ route('login') }}" class="block rounded-xl px-4 py-3 hover:bg-white/10">Sign in</a>
                <a href="{{ route('register') }}" class="mt-2 block rounded-xl bg-accent px-4 py-3 text-center font-semibold text-brand-900">Create an account</a>
            @endauth
        </div>
    </nav>

    {{-- FLASH MESSAGES --}}
    @if(session('success'))
        <div role="status" x-data="{ show: true }" x-show="show" x-init="setTimeout(() => show = false, 4000)"
             class="flex items-center justify-between border-l-4 border-green-500 bg-green-50 px-6 py-3 text-sm text-green-800">
            <span><i class="fa-solid fa-circle-check mr-2" aria-hidden="true"></i>{{ session('success') }}</span>
            <button @click="show = false" class="text-green-600 hover:text-green-800 font-bold text-lg leading-none">×</button>
        </div>
    @endif
    @if(session('error'))
        <div role="alert" x-data="{ show: true }" x-show="show" x-init="setTimeout(() => show = false, 5000)"
             class="flex items-center justify-between border-l-4 border-red-500 bg-red-50 px-6 py-3 text-sm text-red-800">
            <span><i class="fa-solid fa-circle-exclamation mr-2" aria-hidden="true"></i>{{ session('error') }}</span>
            <button @click="show = false" class="text-red-600 hover:text-red-800 font-bold text-lg leading-none">×</button>
        </div>
    @endif

    {{-- MAIN CONTENT --}}
    <main class="flex-1">
        @yield('content')
    </main>

    {{-- FOOTER --}}
    <footer class="mt-20 bg-brand-900 text-white">
        <div class="max-w-7xl mx-auto px-4 py-14 sm:px-6 lg:px-8">
            <div class="grid grid-cols-1 md:grid-cols-4 gap-8">
                <div class="md:col-span-2">
                    <div class="flex items-center gap-2 mb-3">
                        <span class="flex h-10 w-10 items-center justify-center rounded-full border border-accent/50 bg-white/10 text-accent"><i class="fa-solid fa-hands-holding-circle" aria-hidden="true"></i></span>
                        <span class="font-display text-xl font-bold">KulturaBiz</span>
                    </div>
                    <p class="text-brand-200 text-sm leading-relaxed max-w-xs">
                        A digital marketplace preserving and celebrating the rich cultural heritage of Mindanao's indigenous artisans.
                    </p>
                </div>
                <div>
                    <h4 class="font-semibold mb-3 text-accent">Marketplace</h4>
                    <ul class="space-y-2 text-sm text-brand-200">
                        <li><a href="{{ route('products.index') }}" class="hover:text-white transition">Browse Products</a></li>
                        <li><a href="{{ route('cultural.index') }}" class="hover:text-white transition">Cultural Stories</a></li>
                        <li><a href="{{ route('register') }}" class="hover:text-white transition">Become an Artisan</a></li>
                    </ul>
                </div>
                <div>
                    <h4 class="font-semibold mb-3 text-accent">Communities</h4>
                    <ul class="space-y-2 text-sm text-brand-200">
                        <li>Maranao People</li>
                        <li>T'boli Tribe</li>
                        <li>Higaonon</li>
                        <li>Tausug</li>
                        <li>Manobo</li>
                    </ul>
                </div>
            </div>
            <div class="border-t border-brand-700 mt-8 pt-6 text-center text-brand-300 text-xs">
                © {{ date('Y') }} KulturaBiz. Preserving Mindanaoan Heritage. Made with <i class="fa-notdog fa-solid fa-heart"></i> in the Philippines.
            </div>
        </div>
    </footer>

    @yield('scripts')

    {{-- Delete Confirmation Modal --}}
    <div id="deleteModal"
        class="fixed inset-0 z-50 hidden items-center justify-center"
        style="background: rgba(0,0,0,0.5);">
        <div class="bg-white rounded-2xl shadow-2xl p-8 max-w-sm w-full mx-4 transform transition-all">
            <div class="text-center mb-6">
                <div class="w-16 h-16 bg-red-100 rounded-full flex items-center justify-center mx-auto mb-4">
                    <i class="fa-solid fa-triangle-exclamation text-red-500 text-2xl"></i>
                </div>
                <h3 class="font-display text-xl font-bold text-gray-900 mb-2">
                    Confirm Delete
                </h3>
                <p id="deleteModalMessage" class="text-gray-500 text-sm">
                    Are you sure you want to delete this? This action cannot be undone.
                </p>
            </div>
            <div class="flex gap-3">
                <button onclick="closeDeleteModal()"
                        class="flex-1 px-4 py-2.5 border border-gray-200 text-gray-600 font-semibold rounded-xl hover:bg-gray-50 transition">
                    Cancel
                </button>
                <form id="deleteModalForm" method="POST" class="flex-1">
                    @csrf
                    @method('DELETE')
                    <button type="submit"
                            class="w-full px-4 py-2.5 bg-red-600 text-white font-semibold rounded-xl hover:bg-red-700 transition flex items-center justify-center gap-2">
                        <i class="fa-solid fa-trash"></i>
                        Delete
                    </button>
                </form>
            </div>
        </div>
    </div>

    <script>
    function openDeleteModal(action, message) {
        document.getElementById('deleteModalForm').action = action;
        document.getElementById('deleteModalMessage').textContent = message || 'Are you sure you want to delete this? This action cannot be undone.';
        const modal = document.getElementById('deleteModal');
        modal.classList.remove('hidden');
        modal.classList.add('flex');
    }

    function closeDeleteModal() {
        const modal = document.getElementById('deleteModal');
        modal.classList.add('hidden');
        modal.classList.remove('flex');
    }

    // Close modal when clicking outside
    document.getElementById('deleteModal').addEventListener('click', function(e) {
        if (e.target === this) closeDeleteModal();
    });

    // Close on Escape key
    document.addEventListener('keydown', function(e) {
        if (e.key === 'Escape') closeDeleteModal();
    });
    </script>
</body>
</html>