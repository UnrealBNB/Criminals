<?php

declare(strict_types=1);

namespace App\Http\Middleware;

use Closure;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;

class TrustProxies
{
    protected array $proxies = [];
    protected int $headers = Request::HEADER_X_FORWARDED_ALL;

    public function handle(Request $request, Closure $next): Response
    {
        $request->setTrustedProxies($this->proxies, $this->headers);
        return $next($request);
    }
}