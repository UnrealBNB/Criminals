<?php

declare(strict_types=1);

namespace App\Http\Middleware;

use Closure;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;

class AdminMiddleware
{
    public function __construct(
        private readonly int $minLevel = 1
    ) {}

    public function handle(Request $request, Closure $next): Response
    {
        $user = auth()->user();

        if (!$user || !$user->hasAdminLevel($this->minLevel)) {
            return redirect('/game');
        }

        return $next($request);
    }
}