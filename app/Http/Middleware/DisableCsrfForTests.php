<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;

class DisableCsrfForTests
{
    /**
     * Handle an incoming request.
     * This middleware disables CSRF checking during tests.
     */
    public function handle(Request $request, Closure $next)
    {
        if (app()->runningUnitTests()) {
            $request->headers->set('X-CSRF-TOKEN', 'disabled-for-tests');
        }
        return $next($request);
    }
}
