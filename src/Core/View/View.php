<?php

declare(strict_types=1);

namespace App\Core\View;

use App\Core\Application;
use RuntimeException;

class View
{
    private array $data = [];
    private array $sections = [];
    private array $sectionStack = [];
    private ?string $layout = null;
    private array $includes = [];

    public function __construct(
        private readonly Application $app,
        private readonly array $config = []
    ) {}

    public function render(string $view, array $data = []): string
    {
        $this->data = array_merge($this->data, $data);

        $content = $this->renderView($view);

        if ($this->layout !== null) {
            $layoutView = $this->layout;
            $this->layout = null;

            $this->sections['content'] = $content;
            $content = $this->renderView($layoutView);
        }

        return $content;
    }

    public function renderView(string $view): string
    {
        $path = $this->getViewPath($view);

        if (!file_exists($path)) {
            throw new RuntimeException("View [{$view}] not found at path [{$path}]");
        }

        return $this->getContents($path);
    }

    protected function getContents(string $path): string
    {
        ob_start();

        try {
            $this->includeFile($path);
        } catch (\Throwable $e) {
            ob_end_clean();
            throw $e;
        }

        return ob_get_clean();
    }

    protected function includeFile(string $__path): void
    {
        extract($this->data, EXTR_SKIP);

        include $__path;
    }

    protected function getViewPath(string $view): string
    {
        $view = str_replace('.', '/', $view);
        return $this->app->resourcePath("views/{$view}.php");
    }

    // Template directives

    public function extends(string $layout): void
    {
        $this->layout = $layout;
    }

    public function section(string $name): void
    {
        $this->sectionStack[] = $name;
        ob_start();
    }

    public function endSection(): void
    {
        if (empty($this->sectionStack)) {
            throw new RuntimeException('Cannot end section without starting one');
        }

        $name = array_pop($this->sectionStack);
        $this->sections[$name] = ob_get_clean();
    }

    public function yield(string $name, string $default = ''): void
    {
        echo $this->sections[$name] ?? $default;
    }

    public function include(string $view, array $data = []): void
    {
        $data = array_merge($this->data, $data);
        $previousData = $this->data;
        $this->data = $data;

        echo $this->renderView($view);

        $this->data = $previousData;
    }

    public function includeIf(string $view, array $data = []): void
    {
        try {
            $this->include($view, $data);
        } catch (RuntimeException $e) {
            // View doesn't exist, do nothing
        }
    }

    public function includeWhen(bool $condition, string $view, array $data = []): void
    {
        if ($condition) {
            $this->include($view, $data);
        }
    }

    public function includeUnless(bool $condition, string $view, array $data = []): void
    {
        if (!$condition) {
            $this->include($view, $data);
        }
    }

    // Helper methods

    public function e(mixed $value): string
    {
        return htmlspecialchars((string) $value, ENT_QUOTES, 'UTF-8');
    }

    public function raw(string $value): string
    {
        return $value;
    }

    public function old(string $key, mixed $default = null): mixed
    {
        return $_SESSION['_old_input'][$key] ?? $default;
    }

    public function error(string $key): ?string
    {
        return $_SESSION['_errors'][$key] ?? null;
    }

    public function hasError(string $key): bool
    {
        return isset($_SESSION['_errors'][$key]);
    }

    public function csrf(): string
    {
        $token = $_SESSION['_csrf_token'] ?? null;

        if ($token === null) {
            $token = bin2hex(random_bytes(32));
            $_SESSION['_csrf_token'] = $token;
        }

        return '<input type="hidden" name="_token" value="' . $this->e($token) . '">';
    }

    public function method(string $method): string
    {
        $method = strtoupper($method);

        if (in_array($method, ['PUT', 'PATCH', 'DELETE'])) {
            return '<input type="hidden" name="_method" value="' . $method . '">';
        }

        return '';
    }

    public function url(string $path = '', array $params = []): string
    {
        $baseUrl = $this->config['base_url'] ?? $_ENV['APP_URL'] ?? '';
        $url = $baseUrl . '/' . ltrim($path, '/');

        if (!empty($params)) {
            $url .= '?' . http_build_query($params);
        }

        return $url;
    }

    public function asset(string $path): string
    {
        return $this->url('assets/' . ltrim($path, '/'));
    }

    public function route(string $name, array $params = []): string
    {
        // This would integrate with the router to generate URLs from route names
        // For now, just return a basic URL
        return $this->url($name, $params);
    }

    // Data sharing

    public function share(string|array $key, mixed $value = null): void
    {
        if (is_array($key)) {
            $this->data = array_merge($this->data, $key);
        } else {
            $this->data[$key] = $value;
        }
    }

    public function with(string|array $key, mixed $value = null): self
    {
        $this->share($key, $value);
        return $this;
    }

    // Loops and conditionals helpers

    public function each(string $view, array $data, string $iterator, string $empty = ''): void
    {
        if (empty($data)) {
            if ($empty) {
                echo $this->renderView($empty);
            }
            return;
        }

        foreach ($data as $key => $item) {
            echo $this->renderView($view, [$iterator => $item, 'key' => $key]);
        }
    }
}