<?php

declare(strict_types=1);

namespace App\Console\Commands;

use App\Core\Console\Command;
use App\Core\Database\Migrator;

class MigrateFreshCommand extends Command
{
    protected string $name = 'migrate:fresh';
    protected string $description = 'Drop all tables and re-run migrations';

    public function __construct(
        private readonly Migrator $migrator
    ) {}

    public function handle(array $args): int
    {
        if (!$this->confirm('This will delete all data. Are you sure?')) {
            $this->info('Operation cancelled.');
            return 0;
        }

        $this->info('Dropping all tables...');

        try {
            $this->migrator->fresh();
            $this->info('Fresh migration completed successfully!');
            return 0;
        } catch (\Throwable $e) {
            $this->error('Fresh migration failed: ' . $e->getMessage());
            return 1;
        }
    }
}