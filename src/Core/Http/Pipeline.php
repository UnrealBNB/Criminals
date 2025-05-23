<?php

declare(strict_types=1);

namespace App\Core\Http;

use App\Core\Container\Container;
use Closure;

class Pipeline
{
    private mixed $passable;
    private array $pipes = [];
    private string $method = 'handle';

    public function __construct(
        private readonly Container $container
    ) {}

    public function send(mixed $passable): self
    {
        $this->passable = $passable;
        return $this;
    }

    public function through(array $pipes): self
    {
        $this->pipes = $pipes;
        return $this;
    }

    public function via(string $method): self
    {
        $this->method = $method;
        return $this;
    }

    public function then(Closure $destination): mixed
    {
        $pipeline = array_reduce(
            array_reverse($this->pipes),
            $this->carry(),
            $this->prepareDestination($destination)
        );

        return $pipeline($this->passable);
    }

    private function carry(): Closure
    {
        return function (Closure $stack, string|Closure $pipe): Closure {
            return function (mixed $passable) use ($stack, $pipe): mixed {
                if (is_callable($pipe)) {
                    return $pipe($passable, $stack);
                }

                if (!class_exists($pipe)) {
                    throw new \RuntimeException("Middleware class {$pipe} does not exist");
                }

                $middleware = $this->container->make($pipe);

                if (!method_exists($middleware, $this->method)) {
                    throw new \RuntimeException(
                        "Middleware {$pipe} does not have method {$this->method}"
                    );
                }

                return $middleware->{$this->method}($passable, $stack);
            };
        };
    }

    private function prepareDestination(Closure $destination): Closure
    {
        return fn(mixed $passable) => $destination($passable);
    }
}