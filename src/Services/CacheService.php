<?php

declare(strict_types=1);

namespace App\Services;

use App\Core\Application;

class CacheService
{
    private string $cachePath;
    private int $defaultTtl;
    private bool $enabled;

    public function __construct(Application $app)
    {
        $this->cachePath = $app->storagePath('cache');
        $this->defaultTtl = config('cache.default_ttl', 3600);
        $this->enabled = config('cache.enabled', true);

        if (!is_dir($this->cachePath)) {
            mkdir($this->cachePath, 0755, true);
        }
    }

    public function get(string $key, mixed $default = null): mixed
    {
        if (!$this->enabled) {
            return $default;
        }

        $filename = $this->getFilename($key);

        if (!file_exists($filename)) {
            return $default;
        }

        $data = unserialize(file_get_contents($filename));

        if ($data['expires'] < time()) {
            unlink($filename);
            return $default;
        }

        return $data['value'];
    }

    public function put(string $key, mixed $value, ?int $ttl = null): bool
    {
        if (!$this->enabled) {
            return false;
        }

        $ttl = $ttl ?? $this->defaultTtl;
        $filename = $this->getFilename($key);

        $data = [
            'value' => $value,
            'expires' => time() + $ttl,
        ];

        return file_put_contents($filename, serialize($data)) !== false;
    }

    public function remember(string $key, callable $callback, ?int $ttl = null): mixed
    {
        $value = $this->get($key);

        if ($value !== null) {
            return $value;
        }

        $value = $callback();
        $this->put($key, $value, $ttl);

        return $value;
    }

    public function forget(string $key): bool
    {
        $filename = $this->getFilename($key);

        if (file_exists($filename)) {
            return unlink($filename);
        }

        return true;
    }

    public function flush(): bool
    {
        $files = glob($this->cachePath . '/*.cache');

        foreach ($files as $file) {
            unlink($file);
        }

        return true;
    }

    public function increment(string $key, int $value = 1): int
    {
        $current = (int) $this->get($key, 0);
        $new = $current + $value;
        $this->put($key, $new);
        return $new;
    }

    public function decrement(string $key, int $value = 1): int
    {
        return $this->increment($key, -$value);
    }

    private function getFilename(string $key): string
    {
        $hash = sha1($key);
        return $this->cachePath . '/' . $hash . '.cache';
    }

    public function tags(array $tags): TaggedCache
    {
        return new TaggedCache($this, $tags);
    }
}

class TaggedCache
{
    private CacheService $cache;
    private array $tags;

    public function __construct(CacheService $cache, array $tags)
    {
        $this->cache = $cache;
        $this->tags = $tags;
    }

    public function get(string $key, mixed $default = null): mixed
    {
        return $this->cache->get($this->taggedKey($key), $default);
    }

    public function put(string $key, mixed $value, ?int $ttl = null): bool
    {
        $this->addKeyToTags($key);
        return $this->cache->put($this->taggedKey($key), $value, $ttl);
    }

    public function flush(): bool
    {
        foreach ($this->tags as $tag) {
            $keys = $this->cache->get("tag:{$tag}", []);
            foreach ($keys as $key) {
                $this->cache->forget($key);
            }
            $this->cache->forget("tag:{$tag}");
        }
        return true;
    }

    private function taggedKey(string $key): string
    {
        return 'tagged:' . implode(':', $this->tags) . ':' . $key;
    }

    private function addKeyToTags(string $key): void
    {
        foreach ($this->tags as $tag) {
            $keys = $this->cache->get("tag:{$tag}", []);
            $keys[] = $this->taggedKey($key);
            $this->cache->put("tag:{$tag}", array_unique($keys));
        }
    }
}