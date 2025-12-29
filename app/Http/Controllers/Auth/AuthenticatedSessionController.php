<?php

namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
use App\Http\Requests\Auth\LoginRequest;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Log;
use Illuminate\View\View;

class AuthenticatedSessionController extends Controller
{
    /**
     * Display the login view.
     */
    public function create(): View
    {
        return view('auth.login');
    }

    /**
     * Handle an incoming authentication request.
     */
    public function store(LoginRequest $request): RedirectResponse
    {
        $request->authenticate();

        // Log debugging info to help diagnose session persistence issues in production.
        try {
            $sid = $request->session()->getId();
            Log::info('login.attempt.success', [
                'user_id' => Auth::id(),
                'session_id' => $sid,
                'is_secure' => $request->isSecure(),
                'x_forwarded_proto' => $request->header('X-Forwarded-Proto'),
                'x_forwarded_for' => $request->header('X-Forwarded-For'),
                'session_cookie_sent' => $request->cookie(config('session.cookie')) !== null,
                'session_cookie_name' => config('session.cookie'),
                'session_secure_config' => config('session.secure'),
                'session_files_count' => count(glob(storage_path('framework/sessions').'/*') ?: []),
            ]);
        } catch (\Throwable $e) {
            Log::warning('login.debug.failed_to_log', ['error' => $e->getMessage()]);
        }

        $request->session()->regenerate();

        return redirect()->intended(route('dashboard', absolute: false));
    }

    /**
     * Destroy an authenticated session.
     */
    public function destroy(Request $request): RedirectResponse
    {
        Auth::guard('web')->logout();

        $request->session()->invalidate();

        $request->session()->regenerateToken();

        return redirect('/');
    }
}
