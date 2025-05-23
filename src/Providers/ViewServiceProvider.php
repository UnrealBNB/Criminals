<?php

declare(strict_types=1);

namespace App\Providers;

use App\Core\Container\ServiceProvider;
use App\Core\View\View;

class ViewServiceProvider extends ServiceProvider
{
    public function register(): void
    {
        $this->container->singleton(View::class, function ($container) {
            return new View($container->get('app'), [
                'base_url' => env('APP_URL', 'http://localhost'),
            ]);
        });

        $this->container->alias(View::class, 'view');
    }

    public function boot(): void
    {
        $view = $this->container->get('view');

        // Share common data with all views
        $view->share([
            'app_name' => env('APP_NAME', 'Criminals'),
            'app_url' => env('APP_URL', 'http://localhost'),
        ]);
    }
}