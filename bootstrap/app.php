<?php

use Illuminate\Foundation\Application;
use Illuminate\Foundation\Configuration\Exceptions;
use Illuminate\Foundation\Configuration\Middleware;
use App\Http\Middleware\VerifyCsrfToken;
use Symfony\Component\HttpFoundation\Request as SymfonyRequest;
use Illuminate\Support\Facades\URL;

$app = Application::configure(basePath: dirname(__DIR__))
    ->withRouting(
        web: __DIR__ . '/../routes/web.php',
        commands: __DIR__ . '/../routes/console.php',
        health: '/up',
    )
    ->withMiddleware(function (Middleware $middleware): void {

        /**
         * -------------------------------------------------------------
         * 🌐 WEB MIDDLEWARE GROUP
         * -------------------------------------------------------------
         */
        $middleware->group('web', [
            \Illuminate\Cookie\Middleware\EncryptCookies::class,
            \Illuminate\Cookie\Middleware\AddQueuedCookiesToResponse::class,
            \Illuminate\Session\Middleware\StartSession::class,
            \Illuminate\View\Middleware\ShareErrorsFromSession::class,

            // CSRF personalizado
            VerifyCsrfToken::class,

            \Illuminate\Routing\Middleware\SubstituteBindings::class,
        ]);
    })
    ->withExceptions(function (Exceptions $exceptions): void {
        //
    })
    ->create();

// Configure trusted proxies and force HTTPS in production.
if (env('APP_ENV') === 'production') {
    $trusted = env('TRUSTED_PROXIES', '*');
    // Compose the forwarded header mask from available Symfony constants
    $forwardedMask = SymfonyRequest::HEADER_X_FORWARDED_FOR
        | SymfonyRequest::HEADER_X_FORWARDED_HOST
        | SymfonyRequest::HEADER_X_FORWARDED_PROTO
        | SymfonyRequest::HEADER_X_FORWARDED_PORT
        | SymfonyRequest::HEADER_X_FORWARDED_PREFIX;

    if ($trusted === '*' || $trusted === '') {
        SymfonyRequest::setTrustedProxies([
            '0.0.0.0/0',
            '::/0',
        ], $forwardedMask);
    } else {
        $proxies = array_map('trim', explode(',', $trusted));
        SymfonyRequest::setTrustedProxies($proxies, $forwardedMask);
    }

    // Force scheme to https so URL generation and redirects use HTTPS behind proxies
    try {
        URL::forceScheme('https');
    } catch (\Throwable $e) {
        // facades might not be fully available yet; AppServiceProvider can also do this as fallback
    }
}

return $app;