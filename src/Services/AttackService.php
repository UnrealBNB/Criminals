<?php

declare(strict_types=1);

namespace App\Services;

use App\Models\User;

class AttackService
{
    private const COOLDOWN_SECONDS = 10;
    private const MIN_WIN_PERCENTAGE = 40;
    private const MAX_WIN_PERCENTAGE = 75;
    private const MIN_LOSS_PERCENTAGE = 25;
    private const MAX_LOSS_PERCENTAGE = 40;

    public function performAttack(User $attacker, User $defender): array
    {
        // Record attack attempt
        $this->recordAttack($attacker->id, $defender->id);

        // Calculate attack powers
        $attackPower = $this->calculateAttackPower($attacker);
        $defensePower = $this->calculateDefensePower($defender);

        // Determine winner
        $attackerWins = $attackPower >= $defensePower;

        // Calculate money transfer
        if ($attackerWins) {
            $percentage = rand(self::MIN_WIN_PERCENTAGE, self::MAX_WIN_PERCENTAGE);
            $moneyTaken = (int) ($defender->cash * $percentage / 100);

            // Update stats
            $this->updateAfterVictory($attacker, $defender, $moneyTaken);

            return [
                'success' => true,
                'winner' => 'attacker',
                'message' => "You defeated {$defender->username} and stole {$moneyTaken} cash!",
                'money_taken' => $moneyTaken,
                'attack_power' => $attackPower,
                'defense_power' => $defensePower,
            ];
        } else {
            $percentage = rand(self::MIN_LOSS_PERCENTAGE, self::MAX_LOSS_PERCENTAGE);
            $moneyLost = (int) ($attacker->cash * $percentage / 100);

            // Update stats
            $this->updateAfterDefeat($attacker, $defender, $moneyLost);

            return [
                'success' => false,
                'winner' => 'defender',
                'message' => "{$defender->username} was too strong! You lost {$moneyLost} cash!",
                'money_lost' => $moneyLost,
                'attack_power' => $attackPower,
                'defense_power' => $defensePower,
            ];
        }
    }

    private function calculateAttackPower(User $attacker): int
    {
        $basePower = $attacker->getTotalAttackPower();
        $randomFactor = rand(90, 115) / 100;

        return (int) ($basePower * $randomFactor);
    }

    private function calculateDefensePower(User $defender): int
    {
        $basePower = $defender->getTotalDefencePower();
        $randomFactor = rand(90, 115) / 100;

        return (int) ($basePower * $randomFactor);
    }

    private function updateAfterVictory(User $attacker, User $defender, int $money): void
    {
        db()->beginTransaction();

        try {
            // Update attacker
            $attacker->cash += $money;
            $attacker->attacks_won++;
            $attacker->save();

            // Update defender
            $defender->cash -= $money;
            $defender->attacks_lost++;
            $defender->save();

            // Record cooldown
            $this->recordCooldown($attacker->id);

            db()->commit();
        } catch (\Throwable $e) {
            db()->rollBack();
            throw $e;
        }
    }

    private function updateAfterDefeat(User $attacker, User $defender, int $money): void
    {
        db()->beginTransaction();

        try {
            // Update attacker
            $attacker->cash -= $money;
            $attacker->attacks_lost++;
            $attacker->save();

            // Update defender
            $defender->cash += $money;
            $defender->attacks_won++;
            $defender->save();

            // Record cooldown
            $this->recordCooldown($attacker->id);

            db()->commit();
        } catch (\Throwable $e) {
            db()->rollBack();
            throw $e;
        }
    }

    private function recordAttack(int $attackerId, int $defenderId): void
    {
        db()->insert('temp', [
            'userid' => $attackerId,
            'area' => 'attack',
            'variable' => (string) $defenderId,
        ]);
    }

    private function recordCooldown(int $userId): void
    {
        $existing = db()->fetchOne(
            "SELECT * FROM temp WHERE userid = :user_id AND area = 'cooldown'",
            ['user_id' => $userId]
        );

        if ($existing) {
            db()->execute(
                "UPDATE temp SET variable = UNIX_TIMESTAMP() WHERE userid = :user_id AND area = 'cooldown'",
                ['user_id' => $userId]
            );
        } else {
            db()->insert('temp', [
                'userid' => $userId,
                'area' => 'cooldown',
                'variable' => (string) time(),
            ]);
        }
    }

    public function isOnCooldown(int $userId): bool
    {
        $cooldown = db()->fetchOne(
            "SELECT variable FROM temp 
             WHERE userid = :user_id 
             AND area = 'cooldown' 
             AND (UNIX_TIMESTAMP() - variable) < :seconds",
            ['user_id' => $userId, 'seconds' => self::COOLDOWN_SECONDS]
        );

        return $cooldown !== null;
    }

    public function getAttacksToday(int $attackerId, int $defenderId): int
    {
        return (int) db()->fetchColumn(
            "SELECT COUNT(*) FROM temp 
             WHERE userid = :attacker 
             AND area = 'attack' 
             AND variable = :defender 
             AND DATE(FROM_UNIXTIME(UNIX_TIMESTAMP())) = CURDATE()",
            ['attacker' => $attackerId, 'defender' => $defenderId]
        );
    }
}