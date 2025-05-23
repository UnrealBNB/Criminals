<?php

declare(strict_types=1);

namespace App\Core\Routing;

use Closure;

class Route
{
    private array $methods;
    private string $uri;
    private Closure|array $action;
    private array $parameters = [];
    private array $wheres = [];
    private array $middleware = [];
    private array $groups = [];
    private ?string $name = null;
    private ?string $compiled = null;

    public function __construct(string|array $methods, string $uri, Closure|array $action)
    {
        $this->methods = (array) $methods;
        $this->uri = $uri;
        $this->action = $action;
    }

    public function where(string|array $name, ?string $expression = null): self
    {
        if (is_array($name)) {
            foreach ($name as $key => $value) {
                $this->wheres[$key] = $value;
            }
        } else {
            $this->wheres[$name] = $expression;
        }

        return $this;
    }

    public function whereNumber(string $name): self
    {
        return $this->where($name, '[0-9]+');
    }

    public function whereAlpha(string $name): self
    {
        return $this->where($name, '[a-zA-Z]+');
    }

    public function whereAlphaNumeric(string $name): self
    {
        return $this->where($name, '[a-zA-Z0-9]+');
    }

    public function whereUuid(string $name): self
    {
        return $this->where($name, '[0-9a-f]{8}-[0-9a-f]{4}-[0-9a-f]{4}-[0-9a-f]{4}-[0-9a-f]{12}');
    }

    public function middleware(string|array $middleware): self
    {
        if (is_array($middleware)) {
            $this->middleware = array_merge($this->middleware, $middleware);
        } else {
            $this->middleware[] = $middleware;
        }

        return $this;
    }

    public function withoutMiddleware(string|array $middleware): self
    {
        $this->middleware = array_diff($this->middleware, (array) $middleware);

        return $this;
    }

    public function name(string $name): self
    {
        $this->name = $name;

        return $this;
    }

    public function matches(string $uri): bool
    {
        if ($this->compiled === null) {
            $this->compile();
        }

        if (!preg_match($this->compiled, $uri, $matches)) {
            return false;
        }

        $this->parameters = $this->extractParameters($matches);

        return true;
    }

    protected function compile(): void
    {
        $pattern = $this->uri;

        // Convert {param} to named groups
        $pattern = preg_replace_callback('/\{(\w+)\}/', function($matches) {
            $name = $matches[1];
            $constraint = $this->wheres[$name] ?? '[^/]+';

            return "(?P<{$name}>{$constraint})";
        }, $pattern);

        $this->compiled = '#^' . $pattern . '$#u';
    }

    protected function extractParameters(array $matches): array
    {
        $parameters = [];

        foreach ($matches as $key => $value) {
            if (is_string($key)) {
                $parameters[$key] = $value;
            }
        }

        return $parameters;
    }

    public function gatherMiddleware(): array
    {
        $middleware = [];

        // Group middleware
        foreach ($this->groups as $group) {
            if (isset($group['middleware'])) {
                $middleware = array_merge($middleware, (array) $group['middleware']);
            }
        }

        // Route middleware
        $middleware = array_merge($middleware, $this->middleware);

        return array_unique($middleware);
    }

    public function getMethods(): array
    {
        return $this->methods;
    }

    public function getUri(): string
    {
        return $this->uri;
    }

    public function getAction(): Closure|array
    {
        return $this->action;
    }

    public function getName(): ?string
    {
        return $this->name;
    }

    public function parameters(): array
    {
        return $this->parameters;
    }

    public function parameter(string $name, mixed $default = null): mixed
    {
        return $this->parameters[$name] ?? $default;
    }

    public function setGroups(array $groups): void
    {
        $this->groups = $groups;
    }
}