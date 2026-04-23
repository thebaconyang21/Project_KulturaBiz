@extends('layouts.app')

@section('title', 'Login')

@section('content')

<div class="min-h-screen flex items-center justify-center py-12 px-4 bg-gradient-to-br from-brand-50 to-brand-100">
    <div class="w-full max-w-md">

        {{-- Logo --}}
        <div class="text-center mb-8">
            <a href="{{ route('home') }}" class="inline-flex items-center gap-2 text-brand-700">
                <span class="text-accent text-4xl"></span>
                <span class="font-display text-3xl font-bold">KulturaBiz</span>
            </a>
            <p class="text-gray-500 mt-2 text-sm">Sign in to your account</p>
        </div>

        <div class="bg-white rounded-2xl shadow-xl border border-gray-100 p-8">
            <h2 class="font-display text-2xl font-bold text-gray-900 mb-6">Welcome Back</h2>

            {{-- Validation Errors --}}
            @if($errors->any())
                <div class="bg-red-50 border border-red-200 text-red-700 rounded-lg p-3 mb-5 text-sm">
                    @foreach($errors->all() as $error)
                        <p>• {{ $error }}</p>
                    @endforeach
                </div>
            @endif

            <form method="POST" action="{{ route('login') }}" class="space-y-5">
                @csrf

                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-1">Email Address</label>
                    <input type="email" name="email" value="{{ old('email') }}" required autofocus
                           class="w-full border border-gray-200 rounded-xl px-4 py-3 text-sm focus:outline-none focus:ring-2 focus:ring-brand-400 focus:border-transparent transition"
                           placeholder="you@example.com">
                </div>

                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-1">Password</label>
                    <input type="password" name="password" required
                           class="w-full border border-gray-200 rounded-xl px-4 py-3 text-sm focus:outline-none focus:ring-2 focus:ring-brand-400 focus:border-transparent transition"
                           placeholder="••••••••">
                </div>

                <div class="flex items-center justify-between">
                    <label class="flex items-center gap-2 text-sm text-gray-600 cursor-pointer">
                        <input type="checkbox" name="remember" class="rounded border-gray-300 text-brand-600">
                        Remember me
                    </label>
                </div>

                <button type="submit"
                        class="w-full bg-brand-700 text-white font-bold py-3 rounded-xl hover:bg-brand-800 transition-all hover:shadow-lg">
                    Sign In
                </button>
            </form>

            <p class="text-center text-sm text-gray-500 mt-6">
                Don't have an account?
                <a href="{{ route('register') }}" class="text-brand-600 font-semibold hover:underline">Register here</a>
            </p>

            
            <!-- <div class="mt-6 pt-5 border-t border-gray-100">
                <p class="text-xs text-gray-400 text-center mb-3">Demo Credentials</p>
                <div class="grid grid-cols-3 gap-2 text-xs">
                    <div class="bg-gray-50 rounded-lg p-2 text-center">
                        <div class="font-semibold text-gray-700">Admin</div>
                        <div class="text-gray-500">admin@kulturabiz.com</div>
                    </div>
                    <div class="bg-brand-50 rounded-lg p-2 text-center">
                        <div class="font-semibold text-brand-700">Artisan</div>
                        <div class="text-gray-500">fatima@kulturabiz.com</div>
                    </div>
                    <div class="bg-green-50 rounded-lg p-2 text-center">
                        <div class="font-semibold text-green-700">Buyer</div>
                        <div class="text-gray-500">maria@example.com</div>
                    </div>
                </div>
                <p class="text-xs text-gray-400 text-center mt-2">Password: <strong>password</strong></p>
            </div> -->
        </div>
    </div>
</div>

@endsection