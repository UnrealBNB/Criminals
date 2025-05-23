<?php

declare(strict_types=1);

namespace App\Http\Controllers;

use App\Core\Container\Container;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\RedirectResponse;
use Symfony\Component\HttpFoundation\Response;

abstract class Controller
{
    protected Container $container;

    public function __construct(Container $container)
    {
        $this->container = $container;
    }

    protected function view(string $view, array $data = []): Response
    {
        return view($view, $data);
    }

    protected function json(mixed $data, int $status = 200): JsonResponse
    {
        return new JsonResponse($data, $status);
    }

    protected function redirect(string $url, int $status = 302): RedirectResponse
    {
        return new RedirectResponse($url, $status);
    }

    protected function back(): RedirectResponse
    {
        $referer = request()->headers->get('referer', '/');
        return $this->redirect($referer);
    }

    protected function auth(): \App\Core\Auth\Auth
    {
        return $this->container->get(\App\Core\Auth\Auth::class);
    }

    protected function validate(array $data, array $rules): array
    {
        $validator = $this->container->get(\App\Core\Validation\Validator::class);
        return $validator->validate($data, $rules);
    }
}