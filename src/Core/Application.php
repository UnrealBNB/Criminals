<?php

declare(strict_types=1);

namespace App\Core;

use App\Core\Container\Container;
use App\Core\Container\ServiceProvider;
use App\Core\Exceptions\Handler as ExceptionHandler;
use App\Providers\AppServiceProvider;
use App\Providers\AuthServiceProvider;
use App\Providers\DatabaseServiceProvider;
use App\Providers\RouteServiceProvider;
use App\Providers\ViewServiceProvider;
use Monolog\Logger;

final class Application
{
    private static ?self $instance = null;

    private readonly array $providers;

    public function __construct(
        private readonly Container $container
    ) {
        self::$instance = $this;

        $this->container->instance(Application::class, $this);
        $this->container->instance(Container::class, $this->container);

        $this->providers = $this->getServiceProviders();
    }

    public static function getInstance(): ?self
    {
        return self::$instance;
    }

    public function bootstrap(): void
    {
        $this->registerExceptionHandler();
        $this->registerServiceProviders();
        $this->bootServiceProviders();
    }

    public function getContainer(): Container
    {
        return $this->container;
    }

    public function environment(): string
    {
        return $_ENV['APP_ENV'] ?? 'production';
    }

    public function isDebug(): bool
    {
        return filter_var($_ENV['APP_DEBUG'] ?? false, FILTER_VALIDATE_BOOLEAN);
    }

    public function isProduction(): bool
    {
        return $this->environment() === 'production';
    }

    public function isDevelopment(): bool
    {
        return $this->environment() === 'local';
    }

    public function basePath(string $path = ''): string
    {
        return ROOT_PATH . ($path ? DIRECTORY_SEPARATOR . ltrim($path, DIRECTORY_SEPARATOR) : '');
    }

    public function storagePath(string $path = ''): string
    {
        return STORAGE_PATH . ($path ? DIRECTORY_SEPARATOR . ltrim($path, DIRECTORY_SEPARATOR) : '');
    }

    public function configPath(string $path = ''): string
    {
        return CONFIG_PATH . ($path ? DIRECTORY_SEPARATOR . ltrim($path, DIRECTORY_SEPARATOR) : '');
    }

    public function publicPath(string $path = ''): string
    {
        return PUBLIC_PATH . ($path ? DIRECTORY_SEPARATOR . ltrim($path, DIRECTORY_SEPARATOR) : '');
    }

    public function resourcePath(string $path = ''): string
    {
        return RESOURCES_PATH . ($path ? DIRECTORY_SEPARATOR . ltrim($path, DIRECTORY_SEPARATOR) : '');
    }

    private function registerExceptionHandler(): void
    {
        $handler = new ExceptionHandler($this->container);

        set_exception_handler([$handler, 'handle']);
        register_shutdown_function([$handler, 'handleShutdown']);
    }

    private function registerServiceProviders(): void
    {
        foreach ($this->providers as $provider) {
            $instance = new $provider($this->container);
            $instance->register();
            $this->container->instance($provider, $instance);
        }
    }

    private function bootServiceProviders(): void
    {
        foreach ($this->providers as $provider) {
            $instance = $this->container->get($provider);
            if (method_exists($instance, 'boot')) {
                $instance->boot();
            }
        }
    }

    private function getServiceProviders(): array
    {
        return [
            DatabaseServiceProvider::class,
            AuthServiceProvider::class,
            ViewServiceProvider::class,
            RouteServiceProvider::class,
            AppServiceProvider::class,
        ];
    }
}