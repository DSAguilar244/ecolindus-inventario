<?php

namespace App\Services;

use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Log;

class AuditService
{
    public function log(string $event, array $data = []): void
    {
        // If spatie activitylog installed and user authenticated, use it; otherwise fallback to application log
        if ($this->shouldUseActivityLog()) {
            try {
                // Use reflection to safely call activity() without undefined function error
                $activityBuilder = call_user_func('activity');
                if (Auth::check()) {
                    $activityBuilder->causedBy(Auth::user())->withProperties($data)->log($event);
                    return;
                }
            } catch (\Throwable $e) {
                // fall through to Log
            }
        }

        Log::info('audit: '.$event, $data);
    }

    private function shouldUseActivityLog(): bool
    {
        return function_exists('activity') && Auth::check();
    }
}
