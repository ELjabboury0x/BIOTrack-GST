<?php

return [
    // Dedicated cache store for KPI payloads. Set to redis in production.
    'cache_store' => env('DASHBOARD_METRICS_CACHE_STORE', env('CACHE_DRIVER', 'file')),

    // KPI cache TTL in seconds.
    'cache_ttl_seconds' => (int) env('DASHBOARD_METRICS_CACHE_TTL', 60),
];
