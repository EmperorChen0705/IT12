<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class StrictAdmin
{
    /**
     * Handle an incoming request.
     *
     * @param  \Closure(\Illuminate\Http\Request): (\Symfony\Component\HttpFoundation\Response)  $next
     */
    public function handle(Request $request, Closure $next): Response
    {
        if (!$request->user()) {
            return redirect()->route('login');
        }

        // Strict check: role MUST be 'admin', ignoring any elevation
        if ($request->user()->role !== 'admin') {
            abort(403, 'Unauthorized. Strict Admin Access Required.');
        }

        return $next($request);
    }
}
