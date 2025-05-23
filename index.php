<?php
/**
 * Criminals Game - Modern Rewrite
 * Single Entry Point
 */

// Set error reporting for development
error_reporting(E_ALL);
ini_set('display_errors', 1);

// Define paths
define('ROOT_PATH', dirname(__DIR__) . '/');
define('APP_PATH', ROOT_PATH . 'src/');
define('PUBLIC_PATH', __DIR__ . '/');

// Load the autoloader
require_once ROOT_PATH . 'vendor/autoload.php';

// Load environment configuration
$config = require APP_PATH . 'Config/app.php';

// Initialize the application
$app = new \App\Core\Application($config);

// Load routes
require_once APP_PATH . 'Config/routes.php';

// Run the application
$app->run();