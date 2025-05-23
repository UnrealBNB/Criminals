<?php

return [
    'name' => env('APP_NAME', 'Criminals'),
    'env' => env('APP_ENV', 'production'),
    'debug' => env('APP_DEBUG', false),
    'url' => env('APP_URL', 'http://localhost'),
    'timezone' => env('APP_TIMEZONE', 'Europe/Amsterdam'),

    'providers' => [
        \App\Providers\DatabaseServiceProvider::class,
        \App\Providers\AuthServiceProvider::class,
        \App\Providers\ViewServiceProvider::class,
        \App\Providers\RouteServiceProvider::class,
        \App\Providers\AppServiceProvider::class,
    ],

    'aliases' => [
        'App' => \App\Core\Application::class,
        'Auth' => \App\Core\Auth\Auth::class,
        'DB' => \App\Core\Database\Database::class,
        'View' => \App\Core\View\View::class,
    ],
];