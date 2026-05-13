<aside class="w-56 bg-brand-800 text-white shrink-0 min-h-screen pt-6 hidden lg:block"
       x-data="{ salesOpen: {{ request()->routeIs('artisan.sales.*') ? 'true' : 'false' }} }">
    <div class="px-5 mb-8">
        <div class="w-12 h-12 rounded-full overflow-hidden mb-3 border-2 border-accent">
            <img src="{{ auth()->user()->profile_photo_url }}"
                 alt="{{ auth()->user()->name }}"
                 class="w-full h-full object-cover">
        </div>
        <p class="font-semibold text-sm">{{ auth()->user()->name }}</p>
        <p class="text-brand-300 text-xs mt-0.5">{{ auth()->user()->shop_name }}</p>
    </div>

    <nav class="space-y-1 px-3">

        <a href="{{ route('artisan.dashboard') }}"
           class="flex items-center gap-3 px-3 py-2 rounded-lg text-sm transition
                  {{ request()->routeIs('artisan.dashboard') ? 'bg-brand-700 text-white font-semibold' : 'text-brand-200 hover:bg-brand-700' }}">
            <i class="fa-solid fa-gauge w-4 text-center"></i> Dashboard
        </a>

        <a href="{{ route('artisan.products.index') }}"
           class="flex items-center gap-3 px-3 py-2 rounded-lg text-sm transition
                  {{ request()->routeIs('artisan.products.index') ? 'bg-brand-700 text-white font-semibold' : 'text-brand-200 hover:bg-brand-700' }}">
            <i class="fa-solid fa-box w-4 text-center"></i> My Products
        </a>

        <a href="{{ route('artisan.products.create') }}"
           class="flex items-center gap-3 px-3 py-2 rounded-lg text-sm transition
                  {{ request()->routeIs('artisan.products.create') ? 'bg-brand-700 text-white font-semibold' : 'text-brand-200 hover:bg-brand-700' }}">
            <i class="fa-solid fa-plus w-4 text-center"></i> Add Product
        </a>

        <a href="{{ route('artisan.orders') }}"
           class="flex items-center gap-3 px-3 py-2 rounded-lg text-sm transition
                  {{ request()->routeIs('artisan.orders') ? 'bg-brand-700 text-white font-semibold' : 'text-brand-200 hover:bg-brand-700' }}">
            <i class="fa-solid fa-cart-shopping w-4 text-center"></i> Orders
        </a>

        {{-- Sales with submenu --}}
        <button @click="salesOpen = !salesOpen"
                class="w-full flex items-center gap-3 px-3 py-2 rounded-lg text-sm transition
                       {{ request()->routeIs('artisan.sales.*') ? 'bg-brand-700 text-white font-semibold' : 'text-brand-200 hover:bg-brand-700' }}">
            <i class="fa-solid fa-chart-line w-4 text-center"></i>
            <span class="flex-1 text-left">Sales</span>
            <i class="fa-solid fa-chevron-down text-xs transition-transform duration-200"
               :class="salesOpen ? 'rotate-180' : ''"></i>
        </button>

        <div x-show="salesOpen"
             x-transition:enter="transition ease-out duration-150"
             x-transition:enter-start="opacity-0 -translate-y-1"
             x-transition:enter-end="opacity-100 translate-y-0"
             x-transition:leave="transition ease-in duration-100"
             x-transition:leave-start="opacity-100 translate-y-0"
             x-transition:leave-end="opacity-0 -translate-y-1"
             class="ml-4 space-y-1 border-l border-brand-600 pl-3 mt-1">

            <a href="{{ route('artisan.sales.daily') }}"
               class="flex items-center gap-3 px-3 py-2 rounded-lg text-sm transition
                      {{ request()->routeIs('artisan.sales.daily') ? 'bg-brand-700 text-white font-semibold' : 'text-brand-300 hover:bg-brand-700 hover:text-white' }}">
                <i class="fa-solid fa-calendar-day w-4 text-center text-xs"></i> Daily
            </a>

            <a href="{{ route('artisan.sales.monthly') }}"
               class="flex items-center gap-3 px-3 py-2 rounded-lg text-sm transition
                      {{ request()->routeIs('artisan.sales.monthly') ? 'bg-brand-700 text-white font-semibold' : 'text-brand-300 hover:bg-brand-700 hover:text-white' }}">
                <i class="fa-solid fa-calendar-days w-4 text-center text-xs"></i> Monthly
            </a>

            <a href="{{ route('artisan.sales.yearly') }}"
               class="flex items-center gap-3 px-3 py-2 rounded-lg text-sm transition
                      {{ request()->routeIs('artisan.sales.yearly') ? 'bg-brand-700 text-white font-semibold' : 'text-brand-300 hover:bg-brand-700 hover:text-white' }}">
                <i class="fa-solid fa-calendar w-4 text-center text-xs"></i> Yearly
            </a>
        </div>

    </nav>
</aside>