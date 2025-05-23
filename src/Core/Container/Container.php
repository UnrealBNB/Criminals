<?php

declare(strict_types=1);

namespace App\Core\Container;

use App\Core\Application;
use Closure;
use ReflectionClass;
use ReflectionException;
use ReflectionNamedType;
use ReflectionParameter;
use ReflectionUnionType;
use RuntimeException;

class Container
{
    private array $bindings = [];
    private array $instances = [];
    private array $aliases = [];

    public static function getInstance(): self
    {
        $app = Application::getInstance();
        if (!$app) {
            throw new RuntimeException('Application has not been initialized');
        }
        return $app->getContainer();
    }
    public function bind(string $abstract, Closure|string $concrete, bool $shared = false): void
    {
        $this->bindings[$abstract] = [
            'concrete' => $concrete,
            'shared' => $shared,
        ];
    }

    public function singleton(string $abstract, Closure|string $concrete): void
    {
        $this->bind($abstract, $concrete, true);
    }

    public function instance(string $abstract, mixed $instance): void
    {
        $this->instances[$abstract] = $instance;
    }

    public function alias(string $abstract, string $alias): void
    {
        $this->aliases[$alias] = $abstract;
    }

    public function make(string $abstract, array $parameters = []): mixed
    {
        return $this->resolve($abstract, $parameters);
    }

    public function get(string $abstract): mixed
    {
        return $this->resolve($abstract);
    }

    public function has(string $abstract): bool
    {
        return isset($this->bindings[$abstract]) ||
            isset($this->instances[$abstract]) ||
            isset($this->aliases[$abstract]);
    }

    private function resolve(string $abstract, array $parameters = []): mixed
    {
        $abstract = $this->getAlias($abstract);

        // Return existing instance if available
        if (isset($this->instances[$abstract])) {
            return $this->instances[$abstract];
        }

        // Get the concrete implementation
        $concrete = $this->getConcrete($abstract);

        // Build the instance
        if ($concrete instanceof Closure) {
            $object = $concrete($this, $parameters);
        } else {
            $object = $this->build($concrete, $parameters);
        }

        // Store as singleton if needed
        if ($this->isShared($abstract)) {
            $this->instances[$abstract] = $object;
        }

        return $object;
    }

    private function getConcrete(string $abstract): Closure|string
    {
        if (!isset($this->bindings[$abstract])) {
            return $abstract;
        }

        return $this->bindings[$abstract]['concrete'];
    }

    private function build(string $concrete, array $parameters = []): mixed
    {
        try {
            $reflector = new ReflectionClass($concrete);
        } catch (ReflectionException $e) {
            throw new RuntimeException("Class {$concrete} does not exist", 0, $e);
        }

        if (!$reflector->isInstantiable()) {
            throw new RuntimeException("Class {$concrete} is not instantiable");
        }

        $constructor = $reflector->getConstructor();

        if ($constructor === null) {
            return new $concrete;
        }

        $dependencies = $this->resolveDependencies($constructor->getParameters(), $parameters);

        return $reflector->newInstanceArgs($dependencies);
    }

    private function resolveDependencies(array $reflectionParameters, array $parameters = []): array
    {
        $dependencies = [];

        foreach ($reflectionParameters as $parameter) {
            $name = $parameter->getName();

            // Use provided parameter if available
            if (array_key_exists($name, $parameters)) {
                $dependencies[] = $parameters[$name];
                continue;
            }

            // Try to resolve the type
            $type = $parameter->getType();

            if ($type === null || $type instanceof ReflectionUnionType) {
                if ($parameter->isDefaultValueAvailable()) {
                    $dependencies[] = $parameter->getDefaultValue();
                } else {
                    throw new RuntimeException(
                        "Unable to resolve dependency {$name} in {$parameter->getDeclaringClass()?->getName()}"
                    );
                }
                continue;
            }

            if ($type instanceof ReflectionNamedType && !$type->isBuiltin()) {
                try {
                    $dependencies[] = $this->resolve($type->getName());
                } catch (RuntimeException $e) {
                    if ($parameter->isDefaultValueAvailable()) {
                        $dependencies[] = $parameter->getDefaultValue();
                    } elseif ($type->allowsNull()) {
                        $dependencies[] = null;
                    } else {
                        throw $e;
                    }
                }
            } else {
                if ($parameter->isDefaultValueAvailable()) {
                    $dependencies[] = $parameter->getDefaultValue();
                } elseif ($type?->allowsNull()) {
                    $dependencies[] = null;
                } else {
                    throw new RuntimeException(
                        "Unable to resolve primitive dependency {$name} in {$parameter->getDeclaringClass()?->getName()}"
                    );
                }
            }
        }

        return $dependencies;
    }

    private function isShared(string $abstract): bool
    {
        return isset($this->bindings[$abstract]['shared']) && $this->bindings[$abstract]['shared'];
    }

    private function getAlias(string $abstract): string
    {
        return $this->aliases[$abstract] ?? $abstract;
    }
}