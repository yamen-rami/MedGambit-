<?php

namespace App\Http\Middleware;

use Illuminate\Http\Request;
use Closure;
use Symfony\Component\HttpFoundation\Response;

class admin
{
    /**
     * Handle an incoming request.
     *
     * @param  Closure(Request): (Response)  $next
     */
    public function handle(Request $request, Closure $next): Response
    {
        if (!auth()->check()) {
            return redirect()->route("login");
        }
        if (auth()->check()) {
            if (auth()->user()->role === "user") {
                return redirect()->route("home");
            }
        }
        return $next($request);
    }
}
