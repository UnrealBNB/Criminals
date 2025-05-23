<?php

declare(strict_types=1);

namespace App\Console\Commands;

use App\Core\Console\Command;
use App\Core\Database\Database;

class CronHourlyCommand extends Command
{
    protected string $name = 'cron:hourly';
    protected string $description = 'Run hourly cron tasks';

    public function __construct(
        private readonly Database $db
    ) {}

    public function handle(array $args): int
    {
        $this->info('Running hourly cron tasks...');

        try {
            // Update user cash and bank
            $this->db->execute('UPDATE users SET 
                cash = (cash + 100),
                bank = (bank + (
                    CASE 
                        WHEN type = 3 THEN 200
                        ELSE 0
                    END
                ))
                WHERE activated = 1');

            $this->info('Updated user cash and banks');

            // Update clan income from special buildings
            $result = $this->db->fetchAll('
                SELECT
                    c.clan_id,
                    c.type,
                    ci.item_count
                FROM
                    clans c
                    LEFT JOIN clan_items ci ON c.clan_id = ci.clan_id
                WHERE
                    ci.item_id IN (29, 30, 31)');

            foreach ($result as $row) {
                $cashIncome = match($row['type']) {
                    1 => 50 * $row['item_count'],
                    2 => 100 * $row['item_count'],
                    3 => 250 * $row['item_count'],
                    default => 0
                };

                $bankIncome = match($row['type']) {
                    1 => 150 * $row['item_count'],
                    2 => 100 * $row['item_count'],
                    3 => 0,
                    default => 0
                };

                if ($cashIncome > 0 || $bankIncome > 0) {
                    $this->db->execute('UPDATE clans SET 
                        cash = cash + :cash,
                        bank = bank + :bank
                        WHERE clan_id = :clan_id',
                        [
                            'cash' => $cashIncome,
                            'bank' => $bankIncome,
                            'clan_id' => $row['clan_id']
                        ]
                    );
                }
            }

            $this->info('Updated clan income');
            $this->info('Hourly cron completed successfully!');
            return 0;
        } catch (\Throwable $e) {
            $this->error('Hourly cron failed: ' . $e->getMessage());
            return 1;
        }
    }
}