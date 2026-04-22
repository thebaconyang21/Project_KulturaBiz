<?php

// namespace App\Http\Controllers;

// use Illuminate\Http\Request;

// class AuthController extends Controller
// {
//     //
// }

namespace App\Http\Controllers;

use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;

/**
 * AuthController
 * Handles registration (with role selection), login, and logout.
 */
class AuthController extends Controller
{
    /**
     * Show login form.
     */
    public function loginForm()
    {
        return view('auth.login');
    }

    /**
     * Process login.
     */
    public function login(Request $request)
    {
        $credentials = $request->validate([
            'email'    => 'required|email',
            'password' => 'required',
        ]);

        if (Auth::attempt($credentials, $request->boolean('remember'))) {
            $request->session()->regenerate();

            $user = Auth::user();

            // Redirect based on role
            return match ($user->role) {
                'admin'   => redirect()->route('admin.dashboard'),
                'artisan' => redirect()->route('artisan.dashboard'),
                default   => redirect()->intended(route('home')),
            };
        }

        return back()->withErrors(['email' => 'Invalid credentials.'])->onlyInput('email');
    }

    /**
     * Show registration form.
     */
    public function registerForm()
    {
        return view('auth.register');
    }

    /**
     * Process registration.
     */
    public function register(Request $request)
    {
        $request->validate([
            'name'      => 'required|string|max:255',
            'email'     => 'required|email|unique:users',
            'password'  => 'required|confirmed|min:8',
            'role'      => 'required|in:customer,artisan',
            'phone'     => 'nullable|string|max:20',
            'address'   => 'nullable|string|max:500',
            // Artisan-specific fields
            'shop_name' => 'required_if:role,artisan|nullable|string|max:255',
            'tribe'     => 'required_if:role,artisan|nullable|string|max:100',
            'region'    => 'required_if:role,artisan|nullable|string|max:100',
            'bio'       => 'nullable|string|max:1000',
        ]);

        $user = User::create([
            'name'      => $request->name,
            'email'     => $request->email,
            'password'  => Hash::make($request->password),
            'role'      => $request->role,
            'phone'     => $request->phone,
            'address'   => $request->address,
            'shop_name' => $request->shop_name,
            'tribe'     => $request->tribe,
            'region'    => $request->region,
            'bio'       => $request->bio,
            // Artisans need approval, customers are auto-approved
            'status'    => $request->role === 'artisan' ? 'pending' : 'approved',
        ]);

        if ($request->role === 'artisan') {
            return redirect()->route('login')
                ->with('success', 'Registration successful! Your artisan account is pending admin approval.');
        }

        Auth::login($user);
        return redirect()->route('home')->with('success', 'Welcome to KulturaBiz!');
    }

    /**
     * Logout.
     */
    public function logout(Request $request)
    {
        Auth::logout();
        $request->session()->invalidate();
        $request->session()->regenerateToken();
        return redirect()->route('home')->with('success', 'Logged out successfully.');
    }
}