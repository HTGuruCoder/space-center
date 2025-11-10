<?php

namespace App\Http\Middleware;

use App\Enums\RoleEnum;
use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class CheckNotOnlyEmployee
{
    /**
     * Handle an incoming request.
     *
     * Check if user has at least one role other than employee role.
     * This middleware is used to restrict access to admin area.
     *
     * @param  \Closure(\Illuminate\Http\Request): (\Symfony\Component\HttpFoundation\Response)  $next
     */
    public function handle(Request $request, Closure $next): Response
    {
        $user = $request->user();

        if (!$user) {
            abort(401, 'Unauthenticated.');
        }

        // Check if user has at least one role that is NOT employee
        $hasAdminRole = $user->roles()->where('name', '!=', RoleEnum::EMPLOYEE->value)->exists();

        if (!$hasAdminRole) {
            abort(403, 'Access denied to admin area. You need an administrative role.');
        }

        return $next($request);
    }
}
