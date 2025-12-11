<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class CheckRole
{
    /**
     * Handle an incoming request.
     *
     * @param  \Closure(\Illuminate\Http\Request): (\Symfony\Component\HttpFoundation\Response)  $next
     */
    public function handle(Request $request, Closure $next, string $role, ?string $strict = null): Response
    {
        if (!$request->user()) {
            return redirect()->route('login');
        }

        // Strict Mode: Require exact role match, ignoring elevation
        if ($strict === 'strict') {
            if ($request->user()->role !== $role) {
                abort(403, 'Unauthorized. Strict Access Required.');
            }
            return $next($request);
        }

        // Normal Mode: Allow access if user has the required role OR if checking for admin and user is elevated
        if ($role === 'admin' && $request->user()->canAccessAdmin()) {
            return $next($request);
        }

        if ($request->user()->role !== $role) {
            abort(403, 'Unauthorized access.');
        }

        return $next($request);
    }
}
