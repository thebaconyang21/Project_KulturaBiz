@extends('layouts.app')

@section('title', 'Register')

@section('content')

<div class="min-h-screen py-12 px-4 bg-gradient-to-br from-brand-50 to-brand-100">
    <div class="w-full max-w-lg mx-auto">

        {{-- Logo --}}
        <div class="text-center mb-8">
            <a href="{{ route('home') }}" class="inline-flex items-center gap-2 text-brand-700">
                <span class="text-accent text-4xl">⬡</span>
                <span class="font-display text-3xl font-bold">KulturaBiz</span>
            </a>
            <p class="text-gray-500 mt-2 text-sm">Join the Mindanaoan Heritage Marketplace</p>
        </div>

        <div class="bg-white rounded-2xl shadow-xl border border-gray-100 p-8" x-data="{ role: '{{ old('role', 'customer') }}' }">
            <h2 class="font-display text-2xl font-bold text-gray-900 mb-6">Create Account</h2>

            @if($errors->any())
                <div class="bg-red-50 border border-red-200 text-red-700 rounded-lg p-3 mb-5 text-sm">
                    @foreach($errors->all() as $error)
                        <p>• {{ $error }}</p>
                    @endforeach
                </div>
            @endif

            <form method="POST" action="{{ route('register') }}" class="space-y-4">
                @csrf

                {{-- Role Selection --}}
                <div class="mb-6">
                    <label class="block text-sm font-medium text-gray-700 mb-2">I want to join as a...</label>
                    <div class="grid grid-cols-2 gap-3">
                        <label class="cursor-pointer">
                            <input type="radio" name="role" value="customer" x-model="role" class="sr-only">
                            <div :class="role === 'customer' ? 'border-brand-500 bg-brand-50 text-brand-700' : 'border-gray-200 hover:border-brand-300'"
                                 class="border-2 rounded-xl p-3 text-center transition-all">
                                <div class="text-2xl mb-1"></div>
                                <div class="font-semibold text-sm">Buyer</div>
                                <div class="text-xs text-gray-500">Shop products</div>
                            </div>
                        </label>
                        <label class="cursor-pointer">
                            <input type="radio" name="role" value="artisan" x-model="role" class="sr-only">
                            <div :class="role === 'artisan' ? 'border-brand-500 bg-brand-50 text-brand-700' : 'border-gray-200 hover:border-brand-300'"
                                 class="border-2 rounded-xl p-3 text-center transition-all">
                                <div class="text-2xl mb-1"></div>
                                <div class="font-semibold text-sm">Artisan</div>
                                <div class="text-xs text-gray-500">Sell your crafts</div>
                            </div>
                        </label>
                    </div>
                </div>

                {{-- Basic Info --}}
                <div class="grid grid-cols-1 gap-4">
                    <div>
                        <label class="block text-sm font-medium text-gray-700 mb-1">Full Name *</label>
                        <input type="text" name="name" value="{{ old('name') }}" required
                               class="w-full border border-gray-200 rounded-xl px-4 py-2.5 text-sm focus:outline-none focus:ring-2 focus:ring-brand-400">
                    </div>

                    <div>
                        <label class="block text-sm font-medium text-gray-700 mb-1">Email Address *</label>
                        <input type="email" name="email" value="{{ old('email') }}" required
                               class="w-full border border-gray-200 rounded-xl px-4 py-2.5 text-sm focus:outline-none focus:ring-2 focus:ring-brand-400">
                    </div>

                    <div>
                        <label class="block text-sm font-medium text-gray-700 mb-1">Phone Number</label>
                        <input type="text" name="phone" value="{{ old('phone') }}"
                               class="w-full border border-gray-200 rounded-xl px-4 py-2.5 text-sm focus:outline-none focus:ring-2 focus:ring-brand-400"
                               placeholder="09XXXXXXXXX">
                    </div>

                    <div>
                        <label class="block text-sm font-medium text-gray-700 mb-1">Password *</label>
                        <input type="password" name="password" required
                               class="w-full border border-gray-200 rounded-xl px-4 py-2.5 text-sm focus:outline-none focus:ring-2 focus:ring-brand-400"
                               placeholder="Minimum 8 characters">
                    </div>

                    <div>
                        <label class="block text-sm font-medium text-gray-700 mb-1">Confirm Password *</label>
                        <input type="password" name="password_confirmation" required
                               class="w-full border border-gray-200 rounded-xl px-4 py-2.5 text-sm focus:outline-none focus:ring-2 focus:ring-brand-400">
                    </div>
                </div>

                {{-- Artisan-specific fields --}}
                <div x-show="role === 'artisan'" x-transition class="space-y-4 border-t border-gray-100 pt-4 mt-4">
                    <p class="text-sm text-brand-700 font-medium">🎨 Artisan Information</p>

                    <div>
                        <label class="block text-sm font-medium text-gray-700 mb-1">Shop/Studio Name *</label>
                        <input type="text" name="shop_name" value="{{ old('shop_name') }}"
                               class="w-full border border-gray-200 rounded-xl px-4 py-2.5 text-sm focus:outline-none focus:ring-2 focus:ring-brand-400"
                               placeholder="e.g., Maranao Weavers Co.">
                    </div>

                    <div class="grid grid-cols-2 gap-3">
                        <div>
                            <label class="block text-sm font-medium text-gray-700 mb-1">Tribe/Community *</label>
                            <input type="text" name="tribe" value="{{ old('tribe') }}"
                                   class="w-full border border-gray-200 rounded-xl px-4 py-2.5 text-sm focus:outline-none focus:ring-2 focus:ring-brand-400"
                                   placeholder="e.g., Maranao">
                        </div>
                        <div>
                            <label class="block text-sm font-medium text-gray-700 mb-1">Region *</label>
                            <select name="region" class="w-full border border-gray-200 rounded-xl px-4 py-2.5 text-sm focus:outline-none focus:ring-2 focus:ring-brand-400">
                                <option value="">Select Region</option>
                                <option value="Region IX - Zamboanga Peninsula" @selected(old('region') === 'Region IX - Zamboanga Peninsula')>Region IX - Zamboanga Peninsula</option>
                                <option value="Region X - Northern Mindanao" @selected(old('region') === 'Region X - Northern Mindanao')>Region X - Northern Mindanao</option>
                                <option value="Region XI - Davao Region" @selected(old('region') === 'Region XI - Davao Region')>Region XI - Davao Region</option>
                                <option value="Region XII - SOCCSKSARGEN" @selected(old('region') === 'Region XII - SOCCSKSARGEN')>Region XII - SOCCSKSARGEN</option>
                                <option value="Region XIII - Caraga" @selected(old('region') === 'Region XIII - Caraga')>Region XIII - Caraga</option>
                                <option value="BARMM" @selected(old('region') === 'BARMM')>BARMM</option>
                            </select>
                        </div>
                    </div>

                    <div>
                        <label class="block text-sm font-medium text-gray-700 mb-1">Bio / About You</label>
                        <textarea name="bio" rows="3"
                                  class="w-full border border-gray-200 rounded-xl px-4 py-2.5 text-sm focus:outline-none focus:ring-2 focus:ring-brand-400"
                                  placeholder="Tell customers about you and your craft...">{{ old('bio') }}</textarea>
                    </div>

                    <div class="bg-amber-50 border border-amber-200 rounded-lg p-3 text-xs text-amber-800">
                        ℹ️ Artisan accounts require admin approval before you can start selling. You'll be notified once approved.
                    </div>
                </div>

                <button type="submit"
                        class="w-full bg-brand-700 text-white font-bold py-3 rounded-xl hover:bg-brand-800 transition-all hover:shadow-lg mt-6">
                    Create Account
                </button>
            </form>

            <p class="text-center text-sm text-gray-500 mt-5">
                Already have an account?
                <a href="{{ route('login') }}" class="text-brand-600 font-semibold hover:underline">Sign in</a>
            </p>
        </div>
    </div>
</div>

@endsection