<?php

namespace App\Http\Middleware;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Cache;
use Closure;
use Symfony\Component\HttpFoundation\Response;

class TrackUsers
{
    /**
     * Handle an incoming request.
     *
     * @param  Closure(Request): (Response)  $next
     */
    public function handle(Request $request, Closure $next): Response
    {
         if (auth()->check()) {
            Cache::put(
                'active-user-' . auth()->id(),
                true,
                now()->addMinutes(5)
            );
        }
        return $next($request);
    }
}
