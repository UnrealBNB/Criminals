<?php

declare(strict_types=1);

/**
 * Criminals Game - Public Entry Point
 * PHP 8.2+ Implementation
 */

use App\Core\Application;
use App\Core\Container\Container;
use App\Core\Http\Kernel;
use Symfony\Component\HttpFoundation\Request;

// Set timezone
date_default_timezone_set('Europe/Amsterdam');

// Define paths
define('ROOT_PATH', dirname(__DIR__) . DIRECTORY_SEPARATOR);
define('APP_PATH', ROOT_PATH . 'src' . DIRECTORY_SEPARATOR);
define('CONFIG_PATH', ROOT_PATH . 'config' . DIRECTORY_SEPARATOR);
define('STORAGE_PATH', ROOT_PATH . 'storage' . DIRECTORY_SEPARATOR);
define('PUBLIC_PATH', __DIR__ . DIRECTORY_SEPARATOR);
define('RESOURCES_PATH', ROOT_PATH . 'resources' . DIRECTORY_SEPARATOR);

// Composer autoloader
require ROOT_PATH . 'vendor/autoload.php';

// Error handling
set_error_handler(function (int $errno, string $errstr, string $errfile, int $errline): bool {
    if (error_reporting() === 0) {
        return false;
    }
    throw new ErrorException($errstr, 0, $errno, $errfile, $errline);
});

try {
    // Load environment
    $dotenv = Dotenv\Dotenv::createImmutable(ROOT_PATH);
    $dotenv->safeLoad();

    // Create container
    $container = new Container();

    // Bootstrap application
    $app = new Application($container);
    $app->bootstrap();

    // Handle request
    $kernel = $container->get(Kernel::class);
    $request = Request::createFromGlobals();
    $response = $kernel->handle($request);

    // Send response
    $response->send();

    // Terminate
    $kernel->terminate($request, $response);

} catch (Throwable $e) {
    // Emergency error handling
    if ($_ENV['APP_DEBUG'] ?? false) {
        echo '<pre>';
        echo 'Error: ' . $e->getMessage() . PHP_EOL;
        echo 'File: ' . $e->getFile() . ':' . $e->getLine() . PHP_EOL;
        echo 'Trace:' . PHP_EOL . $e->getTraceAsString();
        echo '</pre>';
    } else {
        http_response_code(500);
        echo 'An error occurred. Please try again later.';
    }
    exit(1);
}