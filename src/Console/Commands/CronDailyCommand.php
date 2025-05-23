<?php

declare(strict_types=1);

namespace App\Console\Commands;

use App\Core\Console\Command;
use App\Core\Database\Database;

class CronDailyCommand extends Command
{
    protected string $name = 'cron:daily';
    protected string $description = 'Run daily cron tasks';

    public function __construct(
        private readonly Database $db
    ) {}

    public function handle(array $args): int
    {
        $this->info('Running daily cron tasks...');

        try {
            // Update user bank limits
            $this->db->execute('UPDATE users SET bank_left = (
                CASE 
                    WHEN (attack_power + defence_power) < 5000 THEN 5
                    WHEN (attack_power + defence_power) < 10000 THEN 4
                    WHEN (attack_power + defence_power) > 10000 THEN 5
                END),
                bank = (bank * 1.05), 
                clicks_today = 0');

            $this->info('Updated user banks and limits');

            // Update clans
            $this->db->execute('UPDATE clans SET bank = (bank * 1.05), bankleft = 10, clicks_today = 0');
            $this->info('Updated clan banks');

            // Clear clicks
            $this->db->execute('DELETE FROM clicks');
            $this->info('Cleared daily clicks');

            $this->info('Daily cron completed successfully!');
            return 0;
        } catch (\Throwable $e) {
            $this->error('Daily cron failed: ' . $e->getMessage());
            return 1;
        }
    }
}