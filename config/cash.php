<?php

return [
    // If true, closing a cash session requires reported_closing_amount to be provided.
    // Default false to maintain backward compatibility in existing automated tests.
    'require_reported_closing' => env('CASH_REQUIRE_REPORTED_CLOSING', false),
    // Cache TTL minutes for cash summary
    'summary_cache_ttl' => env('CASH_SUMMARY_CACHE_TTL', 60),
];
