<?php

return [
    'driver' => env('GOOGLE_REVIEWS_DRIVER', 'business_profile'),

    'language' => env('GOOGLE_REVIEWS_LANGUAGE', config('app.locale', 'en')),
    'timeout' => (int) env('GOOGLE_REVIEWS_TIMEOUT', 5),
    'cache_ttl' => (int) env('GOOGLE_REVIEWS_CACHE_TTL', 1800),
    'stale_ttl' => (int) env('GOOGLE_REVIEWS_STALE_TTL', 86400),
    'max_reviews' => (int) env('GOOGLE_REVIEWS_MAX_REVIEWS', 5),

    'show_errors' => (bool) env('GOOGLE_REVIEWS_SHOW_ERRORS', false),
    'theme' => env('GOOGLE_REVIEWS_THEME', 'light'),
    'review_button_label' => env('GOOGLE_REVIEWS_BUTTON_LABEL', 'review us on Google'),

    'business_profile' => [
        'access_token' => env('GOOGLE_BUSINESS_PROFILE_ACCESS_TOKEN'),
        'refresh_token' => env('GOOGLE_BUSINESS_PROFILE_REFRESH_TOKEN'),
        'client_id' => env('GOOGLE_BUSINESS_PROFILE_CLIENT_ID'),
        'client_secret' => env('GOOGLE_BUSINESS_PROFILE_CLIENT_SECRET'),
        'location' => env('GOOGLE_BUSINESS_PROFILE_LOCATION'),
        'accounts_endpoint' => env('GOOGLE_BUSINESS_PROFILE_ACCOUNTS_ENDPOINT', 'https://mybusinessaccountmanagement.googleapis.com/v1/accounts'),
        'locations_endpoint' => env('GOOGLE_BUSINESS_PROFILE_LOCATIONS_ENDPOINT', 'https://mybusinessbusinessinformation.googleapis.com/v1'),
        'reviews_endpoint' => env('GOOGLE_BUSINESS_PROFILE_REVIEWS_ENDPOINT', 'https://mybusiness.googleapis.com/v4'),
        'token_endpoint' => env('GOOGLE_BUSINESS_PROFILE_TOKEN_ENDPOINT', 'https://oauth2.googleapis.com/token'),
    ],

    'places' => [
        'api_key' => env('GOOGLE_MAPS_API_KEY'),
        'place_id' => env('GOOGLE_REVIEWS_PLACE_ID'),
        'endpoint' => env('GOOGLE_REVIEWS_PLACES_ENDPOINT', 'https://places.googleapis.com/v1/places'),
        'region' => env('GOOGLE_REVIEWS_REGION'),
    ],

    'nicxon_connector' => [
        'endpoint' => env('NICXON_GOOGLE_REVIEWS_CONNECTOR_ENDPOINT'),
        'site_token' => env('NICXON_GOOGLE_REVIEWS_SITE_TOKEN'),
        'location' => env('NICXON_GOOGLE_REVIEWS_LOCATION'),
    ],
];
