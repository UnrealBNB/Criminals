<?php

declare(strict_types=1);

namespace App\Http\Middleware;

use Closure;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;

class CsrfMiddleware
{
    protected array $except = [];

    public function handle(Request $request, Closure $next): Response
    {
        if ($this->isReading($request) || $this->shouldPassThrough($request)) {
            return $next($request);
        }

        if (!$this->tokensMatch($request)) {
            abort(419, 'CSRF token mismatch');
        }

        return $next($request);
    }

    private function isReading(Request $request): bool
    {
        return in_array($request->getMethod(), ['GET', 'HEAD', 'OPTIONS']);
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

    private function tokensMatch(Request $request): bool
    {
        $token = $request->request->get('_token')
            ?? $request->headers->get('X-CSRF-TOKEN')
            ?? $request->headers->get('X-XSRF-TOKEN');

        $sessionToken = $_SESSION['_csrf_token'] ?? null;

        return $token && $sessionToken && hash_equals($sessionToken, $token);
    }
}