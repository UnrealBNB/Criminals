<?php

declare(strict_types=1);

namespace App\Core\Container;

abstract class ServiceProvider
{
    public function __construct(
        protected Container $container
    ) {}

    /**
     * Register services into the container
     */
    abstract public function register(): void;

    /**
     * Bootstrap services after all providers are registered
     */
    public function boot(): void
    {
        // Override in child classes if needed
    }

    /**
     * Get the services provided by the provider
     */
    public function provides(): array
    {
        return [];
    }
}