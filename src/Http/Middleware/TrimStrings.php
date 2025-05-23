<?php

declare(strict_types=1);

namespace App\Http\Middleware;

use Closure;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;

class TrimStrings
{
    protected array $except = [
        'password',
        'password_confirmation',
    ];

    public function handle(Request $request, Closure $next): Response
    {
        $this->clean($request);
        return $next($request);
    }

    private function clean(Request $request): void
    {
        $input = $request->request->all();
        $request->request->replace($this->cleanArray($input));
    }

    private function cleanArray(array $data): array
    {
        foreach ($data as $key => $value) {
            if (in_array($key, $this->except)) {
                continue;
            }

            if (is_array($value)) {
                $data[$key] = $this->cleanArray($value);
            } elseif (is_string($value)) {
                $data[$key] = trim($value);
            }
        }

        return $data;
    }
}