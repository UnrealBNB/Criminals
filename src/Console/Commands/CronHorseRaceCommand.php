<?php

declare(strict_types=1);

namespace App\Console\Commands;

use App\Core\Console\Command;
use App\Core\Database\Database;

class CronHorseRaceCommand extends Command
{
    protected string $name = 'cron:horse-race';
    protected string $description = 'Process horse race results';

    public function __construct(
        private readonly Database $db
    ) {}

    public function handle(array $args): int
    {
        $this->info('Processing horse race...');

        try {
            $winningHorse = rand(1, 50);
            $this->info("Winning horse: #{$winningHorse}");

            // Get all bets
            $bets = $this->db->fetchAll("SELECT * FROM temp WHERE area = 'horse'");

            if (empty($bets)) {
                $this->info('No bets placed for this race.');
                return 0;
            }

            $jackpot = count($bets) * 20000;
            $winners = array_filter($bets, fn($bet) => $bet['variable'] == $winningHorse);

            $this->info("Total jackpot: €" . number_format($jackpot));
            $this->info("Winners: " . count($winners));

            // Process winnings
            foreach ($winners as $winner) {
                $multiply = match((int)$winner['extra']) {
                    3 => 1,
                    2 => 0.5,
                    1 => 0.25,
                    default => 0
                };

                $amount = floor($jackpot / count($winners) * (25 * pow(2, $multiply)));

                $this->db->execute(
                    'UPDATE users SET bank = bank + :amount WHERE id = :user_id',
                    ['amount' => $amount, 'user_id' => $winner['userid']]
                );

                $this->info("User {$winner['userid']} won €" . number_format($amount));
            }

            // Clear bets
            $this->db->execute("DELETE FROM temp WHERE area = 'horse'");

            $this->info('Horse race completed successfully!');
            return 0;
        } catch (\Throwable $e) {
            $this->error('Horse race failed: ' . $e->getMessage());
            return 1;
        }
    }
}