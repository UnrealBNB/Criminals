<?php

declare(strict_types=1);

namespace App\Http\Middleware;

use Closure;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;

class PreventRequestsDuringMaintenance
{
    protected array $except = [];

    public function handle(Request $request, Closure $next): Response
    {
        if ($this->isDownForMaintenance() && !$this->shouldPassThrough($request)) {
            return new Response(
                view('errors.503')->getContent(),
                503
            );
        }

        return $next($request);
    }

    private function isDownForMaintenance(): bool
    {
        return file_exists(app()->storagePath('framework/down'));
    }

    private function shouldPassThrough(Request $request): bool
    {
        foreach ($this->except as $except) {
            if ($request->is($except)) {
                return true;
            }
        }

        return false;
    }
}