<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class StrictAdminOnly
{
    /**
     * Handle an incoming request.
     * Only allows users with role === 'admin' (excludes elevated managers)
     */
    public function handle(Request $request, Closure $next): Response
    {
        if (!$request->user()) {
            return redirect()->route('login');
        }

        if ($request->user()->role !== 'admin') {
            abort(403, 'Only the admin can access this resource.');
        }

        return $next($request);
    }
}
