<?php

declare(strict_types=1);

namespace App\Http\Middleware;

use Closure;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;

class MaintenanceMiddleware
{
    protected array $except = [
        '/admin/*',
    ];

    public function handle(Request $request, Closure $next): Response
    {
        if ($this->inMaintenanceMode() && !$this->shouldPassThrough($request)) {
            if ($request->expectsJson()) {
                return new Response(json_encode([
                    'message' => 'Service Unavailable',
                ]), 503, ['Content-Type' => 'application/json']);
            }

            return new Response(
                $this->renderMaintenancePage(),
                503
            );
        }

        return $next($request);
    }

    protected function inMaintenanceMode(): bool
    {
        return file_exists(app()->storagePath('framework/down'));
    }

    protected function shouldPassThrough(Request $request): bool
    {
        foreach ($this->except as $except) {
            if ($request->is($except)) {
                return true;
            }
        }

        $user = auth()->user();
        return $user && $user->isAdmin();
    }

    protected function renderMaintenancePage(): string
    {
        $downFile = app()->storagePath('framework/down');
        $data = json_decode(file_get_contents($downFile), true);

        return view('errors.503', [
            'message' => $data['message'] ?? 'Service Unavailable',
            'retry' => $data['retry'] ?? 60,
        ])->getContent();
    }
}