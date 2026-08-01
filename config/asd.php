<?php

return [
    'locales' => [
        'supported' => ['fr', 'ar', 'en'],
        'fallback' => env('APP_FALLBACK_LOCALE', 'fr'),
    ],

    'rate_limits' => [
        'public' => (int) env('PUBLIC_THROTTLE_MAX_ATTEMPTS', 60),
        'auth' => (int) env('AUTH_THROTTLE_MAX_ATTEMPTS', 10),
        'search' => (int) env('SEARCH_THROTTLE_MAX_ATTEMPTS', 30),
        'uploads' => (int) env('UPLOAD_THROTTLE_MAX_ATTEMPTS', 20),
    ],

    'telescope' => [
        'enabled' => (bool) env('TELESCOPE_ENABLED', false)
            && ! app()->environment('production'),
    ],

    'invitations' => [
        'expiry_hours' => (int) env('INVITATION_EXPIRY_HOURS', 48),
    ],
];