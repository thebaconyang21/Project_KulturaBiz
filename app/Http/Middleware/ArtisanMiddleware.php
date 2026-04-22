<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;


/**
 * ArtisanMiddleware
 * Ensures only approved artisans can access artisan routes.
 */
class ArtisanMiddleware
{
    public function handle(Request $request, Closure $next): Response
    {
        $user = auth()->user();

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