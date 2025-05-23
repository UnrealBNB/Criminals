<?php

declare(strict_types=1);

namespace App\Http\Middleware;

use Closure;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;

class ClanMiddleware
{
    public function __construct(
        private readonly int $minLevel = 1
    ) {}

    public function handle(Request $request, Closure $next): Response
    {
        $user = auth()->user();

        if (!$user || !$user->isInClan()) {
            flash('error', 'You must be in a clan to access this page');
            return redirect('/game/clan');
        }

        if (!$user->hasClanLevel($this->minLevel)) {
            flash('error', 'You do not have sufficient clan permissions');
            return redirect('/game/clan');
        }

        return $next($request);
    }
}