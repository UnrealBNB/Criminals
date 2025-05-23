<?php

declare(strict_types=1);

namespace App\Providers;

use App\Core\Container\ServiceProvider;
use App\Services\AttackService;
use App\Services\ClanService;
use App\Services\ClickService;
use App\Services\GamblingService;
use Monolog\Logger;
use Monolog\Handler\StreamHandler;
use Monolog\Handler\RotatingFileHandler;

class AppServiceProvider extends ServiceProvider
{
    public function register(): void
    {
        // Services
        $this->container->singleton(AttackService::class);
        $this->container->singleton(ClanService::class);
        $this->container->singleton(ClickService::class);
        $this->container->singleton(GamblingService::class);

        // Logger
        $this->container->singleton(Logger::class, function () {
            $logger = new Logger('criminals');

            if (app()->isDevelopment()) {
                $logger->pushHandler(
                    new StreamHandler(
                        app()->storagePath('logs/app.log'),
                        Logger::DEBUG
                    )
                );
            } else {
                $logger->pushHandler(
                    new RotatingFileHandler(
                        app()->storagePath('logs/app.log'),
                        7,
                        Logger::WARNING
                    )
                );
            }

            return $logger;
        });

        // Configuration
        $this->container->instance('config', $this->loadConfiguration());
    }

    public function boot(): void
    {
        // Set default timezone
        date_default_timezone_set(config('app.timezone', 'Europe/Amsterdam'));

        // Error reporting
        if (app()->isDevelopment()) {
            error_reporting(E_ALL);
            ini_set('display_errors', '1');
        } else {
            error_reporting(0);
            ini_set('display_errors', '0');
        }
    }

    private function loadConfiguration(): array
    {
        $config = [];
        $configPath = app()->configPath();

        foreach (glob($configPath . '/*.php') as $file) {
            $key = basename($file, '.php');
            $config[$key] = require $file;
        }

        return $config;
    }
}