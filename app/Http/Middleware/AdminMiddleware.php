<?php

namespace App\Http\Middleware;

use App\Models\User;
use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;
use Illuminate\Support\Facades\Auth;


/**
 * AdminMiddleware
 * Ensures only admin users can access admin routes.jkjkbkjbj
 */
class AdminMiddleware
{
    public function handle(Request $request, Closure $next): Response
    {   
        /** @var User|null $user */
        $user = Auth::user();

        if (!$user || !$user->isAdmin()) {
            abort(403, 'Access denied. Admins only.');
        }
       

        return $next($request);
    }
}