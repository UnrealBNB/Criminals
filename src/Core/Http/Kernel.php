<?php

declare(strict_types=1);

namespace App\Core\Http;

use App\Core\Container\Container;
use App\Core\Routing\Router;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Throwable;

class Kernel
{
    private array $middleware = [
        \App\Http\Middleware\TrimStrings::class,
        \App\Http\Middleware\ConvertEmptyStringsToNull::class,
        \App\Http\Middleware\ValidatePostSize::class,
        \App\Http\Middleware\TrustProxies::class,
        \App\Http\Middleware\PreventRequestsDuringMaintenance::class,
    ];

    public function __construct(
        private readonly Container $container,
        private readonly Router $router
    ) {}

    public function handle(Request $request): Response
    {
        try {
            $this->container->instance(Request::class, $request);

            $response = $this->sendRequestThroughRouter($request);

            return $response;
        } catch (Throwable $e) {
            return $this->handleException($request, $e);
        }
    }

    public function terminate(Request $request, Response $response): void
    {
        // Perform any cleanup tasks
        if ($this->container->has('session')) {
            $this->container->get('session')->save();
        }
    }

    private function sendRequestThroughRouter(Request $request): Response
    {
        return (new Pipeline($this->container))
            ->send($request)
            ->through($this->middleware)
            ->then(fn($request) => $this->router->dispatch($request));
    }

    private function handleException(Request $request, Throwable $e): Response
    {
        if ($this->container->has(\App\Core\Exceptions\Handler::class)) {
            $handler = $this->container->get(\App\Core\Exceptions\Handler::class);
            return $handler->render($request, $e);
        }

        // Fallback error response
        return new Response(
            'An error occurred',
            500,
            ['Content-Type' => 'text/plain']
        );
    }
}