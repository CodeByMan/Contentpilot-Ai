<?php

namespace App\Http\Middleware;

use App\Enums\UserRole;
use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class EnsureAiAccess
{
    public function handle(Request $request, Closure $next): Response
    {
        $user = $request->user()?->loadMissing('plan');

        if (! $user) {
            abort(401);
        }

        if ($user->role === UserRole::Admin) {
            return $next($request);
        }

        if (! $user->plan || ! $user->plan->is_active) {
            abort(403, 'An active plan is required to use AI features.');
        }

        return $next($request);
    }
}
