<?php

declare(strict_types=1);

namespace App\Console\Commands;

use App\Core\Console\Command;
use App\Core\Database\Migrator;

class MigrateRollbackCommand extends Command
{
    protected string $name = 'migrate:rollback';
    protected string $description = 'Rollback database migrations';

    public function __construct(
        private readonly Migrator $migrator
    ) {}

    public function handle(array $args): int
    {
        $steps = isset($args[0]) ? (int) $args[0] : 1;

        $this->info("Rolling back {$steps} migration(s)...");

        try {
            $this->migrator->rollback($steps);
            $this->info('Rollback completed successfully!');
            return 0;
        } catch (\Throwable $e) {
            $this->error('Rollback failed: ' . $e->getMessage());
            return 1;
        }
    }
}