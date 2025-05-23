<?php

declare(strict_types=1);

namespace App\Jobs;

use App\Core\Container\Container;
use App\Core\Database\Database;

class ProcessHorseRaceJob extends Job
{
    public function handle(Container $container): void
    {
        $db = $container->get(Database::class);

        $winningHorse = rand(1, 50);

        // Get all bets
        $bets = $db->fetchAll("SELECT * FROM temp WHERE area = 'horse'");

        if (empty($bets)) {
            return;
        }

        $jackpot = count($bets) * 20000;
        $winners = array_filter($bets, fn($bet) => $bet['variable'] == $winningHorse);

        // Process winnings
        foreach ($winners as $winner) {
            $multiply = match((int)$winner['extra']) {
                3 => 1,
                2 => 0.5,
                1 => 0.25,
                default => 0
            };

            $amount = floor($jackpot / count($winners) * (25 * pow(2, $multiply)));

            $db->execute(
                'UPDATE users SET bank = bank + :amount WHERE id = :user_id',
                ['amount' => $amount, 'user_id' => $winner['userid']]
            );
        }

        // Clear bets
        $db->execute("DELETE FROM temp WHERE area = 'horse'");
    }
}