<?php

namespace App\Http\Middleware;

use Illuminate\Foundation\Http\Middleware\VerifyCsrfToken as BaseVerifyCsrfToken;
use Illuminate\Contracts\Encryption\Encrypter;

class VerifyCsrfToken extends BaseVerifyCsrfToken
{
    /**
     * The URIs that should be excluded from CSRF verification.
     *
     * @var array<int, string>
     */
    protected $except = [
        //
    ];

    /**
     * Handle the incoming request.
     * Override the parent completely for testing.
     */
    public function handle($request, \Closure $next)
    {
        // SKIP ALL CSRF VALIDATION FOR UNIT TESTS
        if ($this->app->runningUnitTests()) {
            return $next($request);
        }

        // For non-test environments, use parent validation
        return parent::handle($request, $next);
    }
}

