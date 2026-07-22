<?php

namespace App\Http\Middleware;

use App\Enums\UserRole;
use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class IsUser
{
    public function handle(Request $request, Closure $next): Response
    {
        if ($request->user()?->role !== UserRole::User) {
            abort(403, 'User access is required.');
        }

        return $next($request);
    }
}
