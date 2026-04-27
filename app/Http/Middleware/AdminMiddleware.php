<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;


/**
 * AdminMiddleware
 * Ensures only admin users can access admin routes.jkjkbkjbj
 */
class AdminMiddleware
{
    public function handle(Request $request, Closure $next): Response
    {
        if (!auth()->check() || !auth()->user()->isAdmin()) {
            abort(403, 'Access denied. Admins only.');
        }
       

        return $next($request);
    }
}