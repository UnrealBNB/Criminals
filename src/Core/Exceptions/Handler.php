<?php

declare(strict_types=1);

namespace App\Core\Exceptions;

use App\Core\Container\Container;
use Monolog\Logger;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Throwable;

class Handler
{
    private array $dontReport = [
        HttpException::class,
        ValidationException::class,
    ];

    public function __construct(
        private readonly Container $container
    ) {}

    public function handle(Throwable $e): void
    {
        $this->report($e);

        $request = $this->container->has(Request::class)
            ? $this->container->get(Request::class)
            : Request::createFromGlobals();

        $response = $this->render($request, $e);
        $response->send();
    }

    public function handleShutdown(): void
    {
        $error = error_get_last();

        if ($error && $this->isFatal($error['type'])) {
            $this->handle(
                new \ErrorException(
                    $error['message'],
                    0,
                    $error['type'],
                    $error['file'],
                    $error['line']
                )
            );
        }
    }

    public function report(Throwable $e): void
    {
        if ($this->shouldReport($e) && $this->container->has(Logger::class)) {
            $this->container->get(Logger::class)->error($e->getMessage(), [
                'exception' => $e,
            ]);
        }
    }

    public function render(Request $request, Throwable $e): Response
    {
        if ($e instanceof HttpException) {
            return $this->renderHttpException($e);
        }

        if ($e instanceof ValidationException) {
            return $this->renderValidationException($e);
        }

        return $this->renderGenericException($e);
    }

    private function shouldReport(Throwable $e): bool
    {
        foreach ($this->dontReport as $type) {
            if ($e instanceof $type) {
                return false;
            }
        }

        return true;
    }

    private function renderHttpException(HttpException $e): Response
    {
        $status = $e->getCode();
        $view = "errors.{$status}";

        if (file_exists(app()->resourcePath("views/{$view}.php"))) {
            return new Response(
                view($view, ['exception' => $e])->getContent(),
                $status
            );
        }

        return new Response($e->getMessage(), $status);
    }

    private function renderValidationException(ValidationException $e): Response
    {
        if (request()->expectsJson()) {
            return json(['errors' => $e->getErrors()], 422);
        }

        $_SESSION['_errors'] = $e->getErrors();
        $_SESSION['_old_input'] = request()->request->all();

        return redirect()->back();
    }

    private function renderGenericException(Throwable $e): Response
    {
        $status = method_exists($e, 'getStatusCode') ? $e->getStatusCode() : 500;

        if (app()->isDebug()) {
            return new Response(
                $this->renderDebugException($e),
                $status
            );
        }

        return new Response(
            view('errors.500', ['exception' => $e])->getContent(),
            $status
        );
    }

    private function renderDebugException(Throwable $e): string
    {
        ob_start();
        ?>
        <!DOCTYPE html>
        <html>
        <head>
            <title>Error - <?= htmlspecialchars($e->getMessage()) ?></title>
            <style>
                body { font-family: Arial, sans-serif; margin: 40px; }
                .error { background: #f8d7da; padding: 20px; border-radius: 5px; }
                .trace { background: #f8f9fa; padding: 10px; margin-top: 20px; overflow-x: auto; }
                pre { margin: 0; white-space: pre-wrap; }
            </style>
        </head>
        <body>
        <div class="error">
            <h1><?= htmlspecialchars(get_class($e)) ?></h1>
            <p><?= htmlspecialchars($e->getMessage()) ?></p>
            <p><strong>File:</strong> <?= htmlspecialchars($e->getFile()) ?> : <?= $e->getLine() ?></p>
        </div>
        <div class="trace">
            <h2>Stack Trace</h2>
            <pre><?= htmlspecialchars($e->getTraceAsString()) ?></pre>
        </div>
        </body>
        </html>
        <?php
        return ob_get_clean();
    }

    private function isFatal(int $type): bool
    {
        return in_array($type, [E_ERROR, E_CORE_ERROR, E_COMPILE_ERROR, E_PARSE]);
    }
}