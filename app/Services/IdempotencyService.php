<?php

namespace App\Services;

use App\Models\IdempotencyKey;
use Illuminate\Support\Facades\Cache;

class IdempotencyService
{
    public function isProcessed(string $key): bool
    {
        if (class_exists(IdempotencyKey::class)) {
            return IdempotencyKey::where('key', $key)->exists();
        }
        return Cache::get('idempotency:'.$key, false) === true;
    }

    public function markProcessed(string $key, ?int $userId = null): void
    {
        if (class_exists(IdempotencyKey::class)) {
            IdempotencyKey::firstOrCreate(['key' => $key], ['user_id' => $userId, 'used_at' => now()]);
            return;
        }
        Cache::put('idempotency:'.$key, true, now()->addMinutes(30));
    }
}
