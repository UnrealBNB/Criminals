<?php

declare(strict_types=1);

namespace App\Http\Middleware;

use Closure;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use App\Services\CacheService;

class RateLimitMiddleware
{
    private int $maxAttempts = 60;
    private int $decayMinutes = 1;

    public function __construct(
        private readonly CacheService $cache
    ) {}

    public function handle(Request $request, Closure $next, string $maxAttempts = '60'): Response
    {
        $key = $this->resolveRequestSignature($request);
        $maxAttempts = (int) $maxAttempts;

        if ($this->tooManyAttempts($key, $maxAttempts)) {
            return $this->buildResponse($key, $maxAttempts);
        }

        $this->hit($key);

        $response = $next($request);

        return $this->addHeaders(
            $response,
            $maxAttempts,
            $this->calculateRemainingAttempts($key, $maxAttempts)
        );
    }

    protected function resolveRequestSignature(Request $request): string
    {
        $user = auth()->user();

        if ($user) {
            return 'rate_limit:' . $user->id . ':' . sha1($request->getPathInfo());
        }

        return 'rate_limit:' . $request->getClientIp() . ':' . sha1($request->getPathInfo());
    }

    protected function tooManyAttempts(string $key, int $maxAttempts): bool
    {
        if ($this->attempts($key) >= $maxAttempts) {
            if ($this->cache->get($key . ':timer')) {
                return true;
            }

            $this->resetAttempts($key);
        }

        return false;
    }

    protected function hit(string $key): int
    {
        $this->cache->put(
            $key . ':timer',
            time() + ($this->decayMinutes * 60),
            $this->decayMinutes * 60
        );

        return $this->cache->increment($key);
    }

    protected function attempts(string $key): int
    {
        return $this->cache->get($key, 0);
    }

    protected function resetAttempts(string $key): void
    {
        $this->cache->forget($key);
    }

    protected function calculateRemainingAttempts(string $key, int $maxAttempts): int
    {
        return $maxAttempts - $this->attempts($key);
    }

    protected function buildResponse(string $key, int $maxAttempts): Response
    {
        $retryAfter = $this->cache->get($key . ':timer') - time();

        return new Response('Too Many Attempts.', 429, [
            'X-RateLimit-Limit' => $maxAttempts,
            'X-RateLimit-Remaining' => 0,
            'Retry-After' => $retryAfter,
            'X-RateLimit-Reset' => time() + $retryAfter,
        ]);
    }

    protected function addHeaders(Response $response, int $maxAttempts, int $remainingAttempts): Response
    {
        $response->headers->set('X-RateLimit-Limit', (string) $maxAttempts);
        $response->headers->set('X-RateLimit-Remaining', (string) max(0, $remainingAttempts));

        return $response;
    }
}