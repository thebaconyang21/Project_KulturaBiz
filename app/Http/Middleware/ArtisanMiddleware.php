<?php

namespace App\Http\Middleware;

use App\Models\User;
use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;
use Illuminate\Support\Facades\Auth;


/**
 * ArtisanMiddleware
 * Ensures only approved artisans can access artisan routes.
 */
class ArtisanMiddleware
{
    public function handle(Request $request, Closure $next): Response
    {
        /** @var User|null $user */
        $user = Auth::user();

        if (!$user || !$user->isArtisan()) {
            abort(403, 'Access denied. Artisans only.');
        }

        if (!$user->isApproved()) {
            return redirect()->route('home')
                ->with('error', 'Your artisan account is pending approval. Please wait for admin verification.');
        }

        return $next($request);
    }
}