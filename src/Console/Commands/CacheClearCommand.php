<?php

declare(strict_types=1);

namespace App\Console\Commands;

use App\Core\Console\Command;

class CacheClearCommand extends Command
{
    protected string $name = 'cache:clear';
    protected string $description = 'Clear application cache';

    public function handle(array $args): int
    {
        $this->info('Clearing cache...');

        $cacheDir = app()->storagePath('cache');

        if (!is_dir($cacheDir)) {
            $this->info('Cache directory does not exist.');
            return 0;
        }

        try {
            $this->deleteDirectory($cacheDir);
            mkdir($cacheDir, 0755, true);
            file_put_contents($cacheDir . '/.gitignore', "*\n!.gitignore\n");

            $this->info('Cache cleared successfully!');
            return 0;
        } catch (\Throwable $e) {
            $this->error('Failed to clear cache: ' . $e->getMessage());
            return 1;
        }
    }

    private function deleteDirectory(string $dir): void
    {
        if (!is_dir($dir)) {
            return;
        }

        $files = array_diff(scandir($dir), ['.', '..']);

        foreach ($files as $file) {
            $path = $dir . '/' . $file;

            if (is_dir($path)) {
                $this->deleteDirectory($path);
            } else {
                unlink($path);
            }
        }

        rmdir($dir);
    }
}