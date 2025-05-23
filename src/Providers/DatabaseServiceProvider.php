<?php

declare(strict_types=1);

namespace App\Providers;

use App\Core\Container\ServiceProvider;
use App\Core\Database\Database;
use App\Core\Database\Model;

class DatabaseServiceProvider extends ServiceProvider
{
    public function register(): void
    {
        $this->container->singleton(Database::class, function () {
            $config = [
                'driver' => env('DB_CONNECTION', 'mysql'),
                'host' => env('DB_HOST', '127.0.0.1'),
                'port' => env('DB_PORT', 3306),
                'database' => env('DB_DATABASE', 'criminals'),
                'username' => env('DB_USERNAME', 'root'),
                'password' => env('DB_PASSWORD', ''),
                'charset' => env('DB_CHARSET', 'utf8mb4'),
                'collation' => env('DB_COLLATION', 'utf8mb4_unicode_ci'),
            ];

            return new Database($config);
        });

        $this->container->alias(Database::class, 'db');
    }

    public function boot(): void
    {
        Model::setDatabase($this->container->get(Database::class));
    }
}