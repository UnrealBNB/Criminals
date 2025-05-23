<?php

declare(strict_types=1);

namespace App\Providers;

use App\Core\Auth\Auth;
use App\Core\Container\ServiceProvider;

class AuthServiceProvider extends ServiceProvider
{
    public function register(): void
    {
        $this->container->singleton(Auth::class, function ($container) {
            return new Auth($container->get('db'));
        });

        $this->container->alias(Auth::class, 'auth');
    }

    public function boot(): void
    {
        // Start session if not started
        if (session_status() === PHP_SESSION_NONE) {
            session_start([
                'cookie_lifetime' => 86400,
                'cookie_httponly' => true,
                'cookie_samesite' => 'Lax',
                'use_strict_mode' => true,
            ]);
        }
    }
}