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
    <nav class="bg-brand-700 text-white shadow-lg sticky top-0 z-50" x-data="{ mobileOpen: false }">
        <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
            <div class="flex items-center justify-between h-16">

                {{-- Logo --}}
                <a href="{{ route('home') }}" class="flex items-center gap-2">
                    <span class="text-accent text-2xl"></span>
                    <span class="font-display text-xl font-bold tracking-wide">KulturaBiz</span>
                </a>

                {{-- Desktop Nav --}}
                <div class="hidden md:flex items-center gap-6 text-sm font-medium">
                    <a href="{{ route('home') }}" class="hover:text-accent transition">Home</a>
                    <a href="{{ route('products.index') }}" class="hover:text-accent transition">Products</a>
                    <a href="{{ route('cultural.index') }}" class="hover:text-accent transition">Cultural Stories</a>

                    @auth
                        {{-- Cart --}}
                        @if(auth()->user()->isCustomer())
                            <a href="{{ route('cart.index') }}" class="relative hover:text-accent transition">
                                <i class="fa-solid fa-cart-shopping"></i>
                                @php $cartCount = collect(session()->get('cart', []))->sum('quantity'); @endphp
                                @if($cartCount > 0)
                                    <span class="absolute -top-2 -right-2 bg-red-500 text-white rounded-full text-xs w-5 h-5 flex items-center justify-center">{{ $cartCount }}</span>
                                @endif
                            </a>
                        @endif

                        {{-- User Dropdown --}}
                        <div class="relative" x-data="{ open: false }">
                            <button @click="open = !open" class="flex items-center gap-2 hover:text-accent transition">
                                <img src="{{ auth()->user()->profile_photo_url }}" alt="" class="w-7 h-7 rounded-full border border-accent">
                                {{ auth()->user()->name }} <i class="fa-solid fa-chevron-down text-xs ml-1"></i>
                            </button>
                            <div x-show="open" @click.away="open = false"
                                 class="absolute right-0 mt-2 w-48 bg-white text-gray-800 rounded-lg shadow-xl border border-gray-100 py-1 z-50">
                                @if(auth()->user()->isAdmin())
                                    <a href="{{ route('admin.dashboard') }}" class="block px-4 py-2 hover:bg-gray-50 text-sm">Admin Panel</a>
                                @elseif(auth()->user()->isArtisan())
                                    <a href="{{ route('artisan.dashboard') }}" class="block px-4 py-2 hover:bg-gray-50 text-sm">Artisan Dashboard</a>
                                    <a href="{{ route('artisan.products.index') }}" class="block px-4 py-2 hover:bg-gray-50 text-sm">My Products</a>
                                    <a href="{{ route('artisan.orders') }}" class="block px-4 py-2 hover:bg-gray-50 text-sm">My Orders</a>
                                @else
                                    <a href="{{ route('orders.mine') }}" class="block px-4 py-2 hover:bg-gray-50 text-sm">My Orders</a>
                                @endif
                                <hr class="my-1">
                                <form method="POST" action="{{ route('logout') }}">
                                    @csrf
                                    <button class="w-full text-left px-4 py-2 hover:bg-gray-50 text-sm text-red-600">Logout</button>
                                </form>
                            </div>
                        </div>
                    @else
                        <a href="{{ route('login') }}" class="hover:text-accent transition">Login</a>
                        <a href="{{ route('register') }}" class="bg-accent text-brand-900 px-4 py-1.5 rounded-full font-semibold hover:bg-yellow-400 transition text-sm">
                            Register
                        </a>
                    @endauth
                </div>

                {{-- Mobile Menu Toggle --}}
                <button @click="mobileOpen = !mobileOpen" class="md:hidden text-white">
                        <i class="fa-solid fa-bars text-xl"></i>
                </button>
            </div>
        </div>

        {{-- Mobile Menu --}}
        <div x-show="mobileOpen" class="md:hidden bg-brand-800 px-4 pb-4 space-y-2 text-sm">
            <a href="{{ route('home') }}" class="block py-2 hover:text-accent">Home</a>
            <a href="{{ route('products.index') }}" class="block py-2 hover:text-accent">Products</a>
            <a href="{{ route('cultural.index') }}" class="block py-2 hover:text-accent">Cultural Stories</a>
            @auth
                <a href="{{ route('orders.mine') }}" class="block py-2 hover:text-accent">My Orders</a>
                <form method="POST" action="{{ route('logout') }}">
                    @csrf
                    <button class="block py-2 text-red-400">Logout</button>
                </form>
            @else
                <a href="{{ route('login') }}" class="block py-2 hover:text-accent">Login</a>
                <a href="{{ route('register') }}" class="block py-2 hover:text-accent">Register</a>
            @endauth
        </div>
    </nav>

    {{-- FLASH MESSAGES --}}
    @if(session('success'))
        <div x-data="{ show: true }" x-show="show" x-init="setTimeout(() => show = false, 4000)"
             class="bg-green-50 border-l-4 border-green-500 text-green-800 px-6 py-3 text-sm flex justify-between items-center">
            <span> {{ session('success') }}</span>
            <button @click="show = false" class="text-green-600 hover:text-green-800 font-bold text-lg leading-none">×</button>
        </div>
    @endif
    @if(session('error'))
        <div x-data="{ show: true }" x-show="show" x-init="setTimeout(() => show = false, 5000)"
             class="bg-red-50 border-l-4 border-red-500 text-red-800 px-6 py-3 text-sm flex justify-between items-center">
            <span> {{ session('error') }}</span>
            <button @click="show = false" class="text-red-600 hover:text-red-800 font-bold text-lg leading-none">×</button>
        </div>
    @endif

    {{-- MAIN CONTENT --}}
    <main class="flex-1">
        @yield('content')
    </main>

    {{-- FOOTER --}}
    <footer class="bg-brand-900 text-white mt-16">
        <div class="max-w-7xl mx-auto px-4 py-12">
            <div class="grid grid-cols-1 md:grid-cols-4 gap-8">
                <div class="md:col-span-2">
                    <div class="flex items-center gap-2 mb-3">
                        <span class="text-accent text-2xl"></span>
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
                © {{ date('Y') }} KulturaBiz. Preserving Mindanaoan Heritage. Made with ❤️ in the Philippines.
            </div>
        </div>
    </footer>

    @yield('scripts')
</body>
</html>