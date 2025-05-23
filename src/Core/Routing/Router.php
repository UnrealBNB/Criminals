<?php

declare(strict_types=1);

namespace App\Core\Routing;

use App\Core\Container\Container;
use Closure;
use ReflectionFunction;
use ReflectionMethod;
use RuntimeException;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;

class Router
{
    private array $routes = [
        'GET' => [],
        'POST' => [],
        'PUT' => [],
        'PATCH' => [],
        'DELETE' => [],
        'OPTIONS' => [],
        'HEAD' => [],
    ];

    private array $groups = [];
    private ?Route $currentRoute = null;

    public function __construct(
        private readonly Container $container
    ) {}

    public function get(string $uri, Closure|array|string $action): Route
    {
        return $this->addRoute(['GET', 'HEAD'], $uri, $action);
    }

    public function post(string $uri, Closure|array|string $action): Route
    {
        return $this->addRoute('POST', $uri, $action);
    }

    public function put(string $uri, Closure|array|string $action): Route
    {
        return $this->addRoute('PUT', $uri, $action);
    }

    public function patch(string $uri, Closure|array|string $action): Route
    {
        return $this->addRoute('PATCH', $uri, $action);
    }

    public function delete(string $uri, Closure|array|string $action): Route
    {
        return $this->addRoute('DELETE', $uri, $action);
    }

    public function options(string $uri, Closure|array|string $action): Route
    {
        return $this->addRoute('OPTIONS', $uri, $action);
    }

    public function any(string $uri, Closure|array|string $action): Route
    {
        return $this->addRoute(array_keys($this->routes), $uri, $action);
    }

    public function match(array $methods, string $uri, Closure|array|string $action): Route
    {
        return $this->addRoute($methods, $uri, $action);
    }

    public function group(array $attributes, Closure $routes): void
    {
        $this->updateGroupStack($attributes);

        $routes($this);

        array_pop($this->groups);
    }

    public function redirect(string $uri, string $destination, int $status = 302): Route
    {
        return $this->any($uri, fn() => new Response('', $status, ['Location' => $destination]));
    }

    public function view(string $uri, string $view, array $data = []): Route
    {
        return $this->get($uri, function() use ($view, $data) {
            $viewEngine = $this->container->get('view');
            return $viewEngine->render($view, $data);
        });
    }

    public function dispatch(Request $request): Response
    {
        $method = $request->getMethod();
        $uri = $request->getPathInfo();

        $route = $this->findRoute($method, $uri);

        if ($route === null) {
            return new Response('Not Found', 404);
        }

        $this->currentRoute = $route;
        $this->container->instance(Route::class, $route);

        $parameters = $route->parameters();
        $middleware = $route->gatherMiddleware();

        $pipeline = new \App\Core\Http\Pipeline($this->container);

        return $pipeline
            ->send($request)
            ->through($middleware)
            ->then(fn($request) => $this->runRoute($request, $route, $parameters));
    }

    protected function runRoute(Request $request, Route $route, array $parameters): Response
    {
        $action = $route->getAction();

        if ($action instanceof Closure) {
            $response = $this->runCallable($action, $parameters);
        } elseif (is_array($action)) {
            $response = $this->runController($action[0], $action[1], $parameters);
        } else {
            throw new RuntimeException('Invalid route action');
        }

        if (!$response instanceof Response) {
            $response = new Response($response);
        }

        return $response;
    }

    protected function runCallable(Closure $callable, array $parameters): mixed
    {
        $reflection = new ReflectionFunction($callable);
        $dependencies = $this->resolveMethodDependencies($reflection->getParameters(), $parameters);

        return $callable(...$dependencies);
    }

    protected function runController(string $controller, string $method, array $parameters): mixed
    {
        $controller = $this->container->make($controller);

        if (!method_exists($controller, $method)) {
            throw new RuntimeException("Method {$method} does not exist on controller");
        }

        $reflection = new ReflectionMethod($controller, $method);
        $dependencies = $this->resolveMethodDependencies($reflection->getParameters(), $parameters);

        return $controller->{$method}(...$dependencies);
    }

    protected function resolveMethodDependencies(array $reflectionParameters, array $parameters): array
    {
        $dependencies = [];

        foreach ($reflectionParameters as $parameter) {
            $class = $parameter->getType()?->getName();

            if ($class && !in_array($class, ['string', 'int', 'float', 'bool', 'array'])) {
                $dependencies[] = $this->container->make($class);
            } elseif (array_key_exists($parameter->getName(), $parameters)) {
                $dependencies[] = $parameters[$parameter->getName()];
            } elseif ($parameter->isDefaultValueAvailable()) {
                $dependencies[] = $parameter->getDefaultValue();
            } else {
                throw new RuntimeException("Unable to resolve parameter {$parameter->getName()}");
            }
        }

        return $dependencies;
    }

    protected function addRoute(string|array $methods, string $uri, Closure|array|string $action): Route
    {
        $route = $this->createRoute($methods, $uri, $action);

        foreach ((array) $methods as $method) {
            $this->routes[strtoupper($method)][$route->getUri()] = $route;
        }

        return $route;
    }

    protected function createRoute(string|array $methods, string $uri, Closure|array|string $action): Route
    {
        if (is_string($action)) {
            $action = $this->parseAction($action);
        }

        $route = new Route($methods, $this->prefix($uri), $action);

        if (!empty($this->groups)) {
            $route->setGroups($this->groups);
        }

        return $route;
    }

    protected function parseAction(string $action): array
    {
        if (!str_contains($action, '@')) {
            throw new RuntimeException("Invalid action format: {$action}");
        }

        [$controller, $method] = explode('@', $action, 2);

        if (!str_starts_with($controller, '\\')) {
            $controller = "\\App\\Http\\Controllers\\{$controller}";
        }

        return [$controller, $method];
    }

    protected function findRoute(string $method, string $uri): ?Route
    {
        $routes = $this->routes[$method] ?? [];

        // First try exact match
        if (isset($routes[$uri])) {
            return $routes[$uri];
        }

        // Then try pattern matching
        foreach ($routes as $route) {
            if ($route->matches($uri)) {
                return $route;
            }
        }

        return null;
    }

    protected function prefix(string $uri): string
    {
        $prefix = $this->getGroupPrefix();

        return $prefix ? trim($prefix, '/') . '/' . trim($uri, '/') : trim($uri, '/');
    }

    protected function getGroupPrefix(): string
    {
        $prefix = '';

        foreach ($this->groups as $group) {
            if (isset($group['prefix'])) {
                $prefix .= '/' . trim($group['prefix'], '/');
            }
        }

        return $prefix;
    }

    protected function updateGroupStack(array $attributes): void
    {
        if (!empty($this->groups)) {
            $attributes = $this->mergeWithLastGroup($attributes);
        }

        $this->groups[] = $attributes;
    }

    protected function mergeWithLastGroup(array $new): array
    {
        $last = end($this->groups);

        return array_merge_recursive($last, $new);
    }

    public function getCurrentRoute(): ?Route
    {
        return $this->currentRoute;
    }

    public function getRoutes(): array
    {
        return $this->routes;
    }
}