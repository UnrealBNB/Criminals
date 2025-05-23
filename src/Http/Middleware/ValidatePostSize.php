<?php

declare(strict_types=1);

namespace App\Http\Middleware;

use Closure;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;

class ValidatePostSize
{
    public function handle(Request $request, Closure $next): Response
    {
        $maxSize = $this->getPostMaxSize();

        if ($maxSize > 0 && $request->server->get('CONTENT_LENGTH') > $maxSize) {
            abort(413, 'Payload Too Large');
        }

        return $next($request);
    }

    private function getPostMaxSize(): int
    {
        if (is_numeric($postMaxSize = ini_get('post_max_size'))) {
            return (int) $postMaxSize;
        }

        $metric = strtoupper(substr($postMaxSize, -1));
        $postMaxSize = (int) $postMaxSize;

        return match ($metric) {
            'K' => $postMaxSize * 1024,
            'M' => $postMaxSize * 1048576,
            'G' => $postMaxSize * 1073741824,
            default => $postMaxSize,
        };
    }
}