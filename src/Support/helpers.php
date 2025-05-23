<?php

declare(strict_types=1);

use App\Core\Application;
use App\Core\Container\Container;
use App\Core\View\View;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;

if (!function_exists('app')) {
    /**
     * Get the application instance
     */
    function app(?string $abstract = null): mixed
    {
        $app = Application::getInstance();

        if ($abstract === null) {
            return $app;
        }

        return $app->getContainer()->get($abstract);
    }
}

if (!function_exists('container')) {
    /**
     * Get the container instance
     */
    function container(): Container
    {
        return app()->getContainer();
    }
}

if (!function_exists('config')) {
    /**
     * Get configuration value
     */
    function config(string $key, mixed $default = null): mixed
    {
        static $config = null;

        if ($config === null) {
            $config = require app()->configPath('app.php');
        }

        $keys = explode('.', $key);
        $value = $config;

        foreach ($keys as $k) {
            if (!is_array($value) || !array_key_exists($k, $value)) {
                return $default;
            }
            $value = $value[$k];
        }

        return $value;
    }
}

if (!function_exists('env')) {
    /**
     * Get environment variable
     */
    function env(string $key, mixed $default = null): mixed
    {
        $value = $_ENV[$key] ?? $_SERVER[$key] ?? getenv($key);

        if ($value === false) {
            return $default;
        }

        return match (strtolower($value)) {
            'true', '(true)' => true,
            'false', '(false)' => false,
            'empty', '(empty)' => '',
            'null', '(null)' => null,
            default => $value,
        };
    }
}

if (!function_exists('view')) {
    /**
     * Create a view response
     */
    function view(string $view, array $data = []): Response
    {
        $content = app(View::class)->render($view, $data);

        return new Response($content);
    }
}

if (!function_exists('redirect')) {
    /**
     * Create a redirect response
     */
    function redirect(string $url, int $status = 302): Response
    {
        return new Response('', $status, ['Location' => $url]);
    }
}

if (!function_exists('back')) {
    /**
     * Redirect back to previous URL
     */
    function back(): Response
    {
        $referer = $_SERVER['HTTP_REFERER'] ?? '/';

        return redirect($referer);
    }
}

if (!function_exists('request')) {
    /**
     * Get the request instance
     */
    function request(): Request
    {
        return app(Request::class);
    }
}

if (!function_exists('response')) {
    /**
     * Create a response
     */
    function response(mixed $content = '', int $status = 200, array $headers = []): Response
    {
        return new Response($content, $status, $headers);
    }
}

if (!function_exists('json')) {
    /**
     * Create a JSON response
     */
    function json(mixed $data, int $status = 200, array $headers = []): Response
    {
        $headers['Content-Type'] = 'application/json';

        return new Response(
            json_encode($data, JSON_THROW_ON_ERROR),
            $status,
            $headers
        );
    }
}

if (!function_exists('abort')) {
    /**
     * Abort with HTTP error
     */
    function abort(int $code = 404, string $message = ''): never
    {
        if (empty($message)) {
            $message = match ($code) {
                400 => 'Bad Request',
                401 => 'Unauthorized',
                403 => 'Forbidden',
                404 => 'Not Found',
                405 => 'Method Not Allowed',
                422 => 'Unprocessable Entity',
                429 => 'Too Many Requests',
                500 => 'Internal Server Error',
                503 => 'Service Unavailable',
                default => 'Error',
            };
        }

        throw new \App\Core\Exceptions\HttpException($message, $code);
    }
}

if (!function_exists('auth')) {
    /**
     * Get the auth instance
     */
    function auth(): \App\Core\Auth\Auth
    {
        return app(\App\Core\Auth\Auth::class);
    }
}

if (!function_exists('user')) {
    /**
     * Get the authenticated user
     */
    function user(): ?\App\Models\User
    {
        return auth()->user();
    }
}

if (!function_exists('db')) {
    /**
     * Get the database instance
     */
    function db(): \App\Core\Database\Database
    {
        return app(\App\Core\Database\Database::class);
    }
}

if (!function_exists('logger')) {
    /**
     * Log a message
     */
    function logger(string $message, array $context = [], string $level = 'info'): void
    {
        app(\Monolog\Logger::class)->log($level, $message, $context);
    }
}

if (!function_exists('session')) {
    /**
     * Get or set session values
     */
    function session(?string $key = null, mixed $default = null): mixed
    {
        if ($key === null) {
            return $_SESSION;
        }

        if (is_array($key)) {
            foreach ($key as $k => $v) {
                $_SESSION[$k] = $v;
            }
            return null;
        }

        return $_SESSION[$key] ?? $default;
    }
}

if (!function_exists('flash')) {
    /**
     * Set a flash message
     */
    function flash(string $key, mixed $value): void
    {
        $_SESSION['_flash'][$key] = $value;
    }
}

if (!function_exists('old')) {
    /**
     * Get old input value
     */
    function old(string $key, mixed $default = null): mixed
    {
        return $_SESSION['_old_input'][$key] ?? $default;
    }
}

if (!function_exists('csrf_token')) {
    /**
     * Get the CSRF token
     */
    function csrf_token(): string
    {
        if (!isset($_SESSION['_csrf_token'])) {
            $_SESSION['_csrf_token'] = bin2hex(random_bytes(32));
        }

        return $_SESSION['_csrf_token'];
    }
}

if (!function_exists('csrf_field')) {
    /**
     * Generate CSRF field
     */
    function csrf_field(): string
    {
        return '<input type="hidden" name="_token" value="' . csrf_token() . '">';
    }
}

if (!function_exists('method_field')) {
    /**
     * Generate method field
     */
    function method_field(string $method): string
    {
        return '<input type="hidden" name="_method" value="' . strtoupper($method) . '">';
    }
}

if (!function_exists('url')) {
    /**
     * Generate URL
     */
    function url(string $path = '', array $params = []): string
    {
        $baseUrl = config('app.url', '');
        $url = rtrim($baseUrl, '/') . '/' . ltrim($path, '/');

        if (!empty($params)) {
            $url .= '?' . http_build_query($params);
        }

        return $url;
    }
}

if (!function_exists('asset')) {
    /**
     * Generate asset URL
     */
    function asset(string $path): string
    {
        return url('assets/' . ltrim($path, '/'));
    }
}

if (!function_exists('route')) {
    /**
     * Generate route URL
     */
    function route(string $name, array $params = []): string
    {
        // This would be implemented with route name resolution
        return url($name, $params);
    }
}

if (!function_exists('e')) {
    /**
     * Escape HTML entities
     */
    function e(mixed $value): string
    {
        return htmlspecialchars((string) $value, ENT_QUOTES, 'UTF-8');
    }
}

if (!function_exists('dd')) {
    /**
     * Dump and die
     */
    function dd(mixed ...$vars): never
    {
        foreach ($vars as $var) {
            echo '<pre>';
            var_dump($var);
            echo '</pre>';
        }

        exit(1);
    }
}

if (!function_exists('dump')) {
    /**
     * Dump variables
     */
    function dump(mixed ...$vars): void
    {
        foreach ($vars as $var) {
            echo '<pre>';
            var_dump($var);
            echo '</pre>';
        }
    }
}

if (!function_exists('now')) {
    /**
     * Get current datetime
     */
    function now(): \DateTime
    {
        return new \DateTime();
    }
}

if (!function_exists('bcrypt')) {
    /**
     * Hash a password using bcrypt
     */
    function bcrypt(string $password, array $options = []): string
    {
        $cost = $options['rounds'] ?? config('hashing.bcrypt.rounds', 12);

        return password_hash($password, PASSWORD_BCRYPT, ['cost' => $cost]);
    }
}

if (!function_exists('blank')) {
    /**
     * Check if value is blank
     */
    function blank(mixed $value): bool
    {
        if (is_null($value)) {
            return true;
        }

        if (is_string($value)) {
            return trim($value) === '';
        }

        if (is_numeric($value) || is_bool($value)) {
            return false;
        }

        if ($value instanceof Countable) {
            return count($value) === 0;
        }

        return empty($value);
    }
}

if (!function_exists('filled')) {
    /**
     * Check if value is filled
     */
    function filled(mixed $value): bool
    {
        return !blank($value);
    }
}

if (!function_exists('collect')) {
    /**
     * Create a collection
     */
    function collect(mixed $items = []): \App\Support\Collection
    {
        return new \App\Support\Collection($items);
    }
}

if (!function_exists('retry')) {
    /**
     * Retry an operation
     */
    function retry(int $times, callable $callback, int $sleep = 0): mixed
    {
        $attempts = 0;

        beginning:
        try {
            return $callback($attempts);
        } catch (\Throwable $e) {
            if (++$attempts < $times) {
                if ($sleep > 0) {
                    usleep($sleep * 1000);
                }

                goto beginning;
            }

            throw $e;
        }
    }
}

if (!function_exists('tap')) {
    /**
     * Call a closure with a value and return the value
     */
    function tap(mixed $value, callable $callback): mixed
    {
        $callback($value);

        return $value;
    }
}

if (!function_exists('value')) {
    /**
     * Return the value of a callable or the value itself
     */
    function value(mixed $value, mixed ...$args): mixed
    {
        return $value instanceof Closure ? $value(...$args) : $value;
    }
}

if (!function_exists('class_basename')) {
    /**
     * Get the class basename
     */
    function class_basename(object|string $class): string
    {
        $class = is_object($class) ? get_class($class) : $class;

        return basename(str_replace('\\', '/', $class));
    }
}