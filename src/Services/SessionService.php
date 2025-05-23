<?php

declare(strict_types=1);

namespace App\Services;

use SessionHandlerInterface;

class SessionService implements SessionHandlerInterface
{
    private string $savePath;
    private int $maxLifetime;

    public function open(string $savePath, string $sessionName): bool
    {
        $this->savePath = $savePath;
        $this->maxLifetime = (int) ini_get('session.gc_maxlifetime');

        if (!is_dir($this->savePath)) {
            mkdir($this->savePath, 0755, true);
        }

        return true;
    }

    public function close(): bool
    {
        return true;
    }

    public function read(string $sessionId): string|false
    {
        $file = $this->savePath . '/sess_' . $sessionId;

        if (file_exists($file) && (time() - filemtime($file)) < $this->maxLifetime) {
            return file_get_contents($file);
        }

        return '';
    }

    public function write(string $sessionId, string $data): bool
    {
        $file = $this->savePath . '/sess_' . $sessionId;
        return file_put_contents($file, $data) !== false;
    }

    public function destroy(string $sessionId): bool
    {
        $file = $this->savePath . '/sess_' . $sessionId;

        if (file_exists($file)) {
            unlink($file);
        }

        return true;
    }

    public function gc(int $maxLifetime): int|false
    {
        $files = glob($this->savePath . '/sess_*');
        $deleted = 0;

        foreach ($files as $file) {
            if ((time() - filemtime($file)) > $maxLifetime) {
                unlink($file);
                $deleted++;
            }
        }

        return $deleted;
    }

    public static function start(array $options = []): void
    {
        $defaults = [
            'name' => 'criminals_session',
            'lifetime' => 86400,
            'path' => '/',
            'domain' => '',
            'secure' => isset($_SERVER['HTTPS']),
            'httponly' => true,
            'samesite' => 'Lax',
        ];

        $options = array_merge($defaults, $options);

        ini_set('session.cookie_lifetime', (string) $options['lifetime']);
        ini_set('session.cookie_path', $options['path']);
        ini_set('session.cookie_domain', $options['domain']);
        ini_set('session.cookie_secure', $options['secure'] ? '1' : '0');
        ini_set('session.cookie_httponly', '1');
        ini_set('session.cookie_samesite', $options['samesite']);
        ini_set('session.name', $options['name']);

        if (session_status() === PHP_SESSION_NONE) {
            session_start();
        }
    }

    public static function regenerate(bool $deleteOldSession = false): bool
    {
        return session_regenerate_id($deleteOldSession);
    }

    public static function flash(string $key, mixed $value): void
    {
        $_SESSION['_flash'][$key] = $value;
    }

    public static function getFlash(string $key, mixed $default = null): mixed
    {
        $value = $_SESSION['_flash'][$key] ?? $default;
        unset($_SESSION['_flash'][$key]);
        return $value;
    }

    public static function keepFlash(array $keys): void
    {
        foreach ($keys as $key) {
            if (isset($_SESSION['_flash'][$key])) {
                $_SESSION['_flash_keep'][$key] = $_SESSION['_flash'][$key];
            }
        }
    }

    public static function ageFlashData(): void
    {
        $_SESSION['_flash_old'] = $_SESSION['_flash'] ?? [];
        $_SESSION['_flash'] = $_SESSION['_flash_keep'] ?? [];
        $_SESSION['_flash_keep'] = [];
    }
}