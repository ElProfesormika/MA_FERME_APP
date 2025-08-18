<?php

return [

    /*
    |--------------------------------------------------------------------------
    | Route Caching
    |--------------------------------------------------------------------------
    |
    | Here you may define the route caching configuration for your application.
    | This allows you to cache your routes for better performance.
    |
    */

    'cache' => [
        'enabled' => env('ROUTE_CACHE_ENABLED', false),
        'ttl' => env('ROUTE_CACHE_TTL', 3600),
    ],

]; 