<?php

declare(strict_types=1);

namespace App\Services;

use App\Models\User;

class GamblingService
{
    // Number Game
    public function playNumberGame(User $user, int $guess, int $amount): array
    {
        $winningNumber = rand(1, 10);
        $won = $guess === $winningNumber;

        db()->beginTransaction();

        try {
            if ($won) {
                $winnings = $amount * 8;
                $user->cash += $winnings;
            } else {
                $user->cash -= $amount;
            }

            $user->save();
            db()->commit();

            return [
                'won' => $won,
                'winningNumber' => $winningNumber,
                'winnings' => $won ? $amount * 8 : 0,
            ];
        } catch (\Throwable $e) {
            db()->rollBack();
            throw $e;
        }
    }

    // Russian Roulette
    public function playRussianRoulette(User $user): array
    {
        $survived = rand(0, 1) === 1;
        $amount = 500;

        db()->beginTransaction();

        try {
            if ($survived) {
                $user->cash += $amount;
            } else {
                $user->cash -= $amount;
            }

            $user->save();
            db()->commit();

            return ['survived' => $survived];
        } catch (\Throwable $e) {
            db()->rollBack();
            throw $e;
        }
    }

    // Coin Flip
    public function playCoinFlip(User $user, int $choice, int $amount): array
    {
        $result = rand(1, 6);
        // 1,3,5 = heads (0), 2,4,6 = tails (1)
        $won = (($result % 2 === 1) && $choice === 0) || (($result % 2 === 0) && $choice === 1);

        db()->beginTransaction();

        try {
            if ($won) {
                $winnings = (int) ($amount * 1.5);
                $user->cash += $winnings;
            } else {
                $user->cash -= $amount;
            }

            $user->save();
            db()->commit();

            return [
                'won' => $won,
                'result' => $result,
                'winnings' => $won ? (int) ($amount * 1.5) : 0,
            ];
        } catch (\Throwable $e) {
            db()->rollBack();
            throw $e;
        }
    }

    // Rock Paper Scissors
    public function playRockPaperScissors(User $user, int $choice): array
    {
        $computerChoice = rand(1, 3);
        $won = false;
        $draw = false;

        // 1=rock, 2=paper, 3=scissors
        if ($choice === $computerChoice) {
            $draw = true;
        } elseif (
            ($choice === 1 && $computerChoice === 3) ||
            ($choice === 2 && $computerChoice === 1) ||
            ($choice === 3 && $computerChoice === 2)
        ) {
            $won = true;
        }

        db()->beginTransaction();

        try {
            if ($won) {
                $user->cash += 500;
            } elseif (!$draw) {
                $user->cash -= 500;
            }

            $user->save();
            db()->commit();

            return [
                'won' => $won,
                'draw' => $draw,
                'computerChoice' => $computerChoice,
            ];
        } catch (\Throwable $e) {
            db()->rollBack();
            throw $e;
        }
    }

    // Higher/Lower
    public function getHigherLowerState(User $user): array
    {
        $round = $user->hlround;
        $winMoney = (int) ($round * 2.6 * 500);
        $costMoney = (int) ($round * 2.5 * 500);
        $number = rand(1, 100);

        return [
            'round' => $round,
            'winMoney' => $winMoney,
            'costMoney' => $costMoney,
            'number' => $number,
        ];
    }

    public function playHigherLower(User $user, int $guess, int $currentNumber): array
    {
        $round = $user->hlround;
        $costMoney = (int) ($round * 2.5 * 500);
        $winMoney = (int) ($round * 2.6 * 500);

        if ($user->cash < $costMoney) {
            return ['won' => false, 'message' => 'Not enough cash'];
        }

        $nextNumber = rand(1, 100);
        $won = false;

        if ($guess === 1 && $nextNumber > $currentNumber) {
            $won = true;
        } elseif ($guess === 2 && $nextNumber < $currentNumber) {
            $won = true;
        } elseif ($nextNumber === $currentNumber) {
            $won = true; // Draw counts as win
        }

        db()->beginTransaction();

        try {
            $user->cash -= $costMoney;

            if ($won) {
                $user->cash += $winMoney;
                $user->hlround++;
            } else {
                $user->hlround = 1; // Reset to round 1
            }

            $user->save();
            db()->commit();

            return [
                'won' => $won,
                'nextNumber' => $nextNumber,
            ];
        } catch (\Throwable $e) {
            db()->rollBack();
            throw $e;
        }
    }

    // Bank Robbery
    public function playBankRobbery(User $user): array
    {
        $success = rand(0, 15) === 13;

        db()->beginTransaction();

        try {
            if ($success) {
                $user->cash += 10000;
            } else {
                $user->cash -= 10000;
            }

            $user->save();
            db()->commit();

            return ['success' => $success];
        } catch (\Throwable $e) {
            db()->rollBack();
            throw $e;
        }
    }

    // Horse Race
    public function getCurrentHorseRaceBet(User $user): ?array
    {
        $bet = db()->fetchOne(
            "SELECT * FROM temp WHERE userid = :user_id AND area = 'horse'",
            ['user_id' => $user->id]
        );

        if (!$bet) {
            return null;
        }

        return [
            'horse' => (int) $bet['variable'],
            'ticket' => (int) $bet['extra'],
        ];
    }

    public function placeHorseRaceBet(User $user, int $horse, int $ticket): array
    {
        $ticketCost = 250 * pow(2, $ticket - 1); // 250, 500, 1000

        if ($user->cash < $ticketCost) {
            return ['success' => false, 'message' => 'Not enough cash for this ticket'];
        }

        $existingBet = $this->getCurrentHorseRaceBet($user);

        if ($existingBet) {
            if ($existingBet['ticket'] === $ticket) {
                return ['success' => false, 'message' => 'You already have this ticket type'];
            }

            if ($existingBet['ticket'] > $ticket) {
                return ['success' => false, 'message' => 'You cannot buy a cheaper ticket'];
            }
        }

        db()->beginTransaction();

        try {
            $user->cash -= $ticketCost;
            $user->save();

            if ($existingBet) {
                db()->execute(
                    "UPDATE temp SET variable = :horse, extra = :ticket 
                     WHERE userid = :user_id AND area = 'horse'",
                    ['horse' => $horse, 'ticket' => $ticket, 'user_id' => $user->id]
                );
            } else {
                db()->insert('temp', [
                    'userid' => $user->id,
                    'area' => 'horse',
                    'variable' => (string) $horse,
                    'extra' => (string) $ticket,
                ]);
            }

            db()->commit();
            return ['success' => true];
        } catch (\Throwable $e) {
            db()->rollBack();
            throw $e;
        }
    }

    // Roulette
    public function playRoulette(User $user, int $color, int $number, int $amount): array
    {
        $winningNumber = rand(0, 36);
        $won = false;
        $winnings = 0;

        // Check if number bet wins
        if ($number === $winningNumber) {
            $won = true;
            $winnings = $amount * 36;
        }

        db()->beginTransaction();

        try {
            if ($won) {
                $user->cash += $winnings;
            } else {
                $user->cash -= $amount;
            }

            $user->save();
            db()->commit();

            return [
                'won' => $won,
                'winningNumber' => $winningNumber,
                'winnings' => $winnings,
            ];
        } catch (\Throwable $e) {
            db()->rollBack();
            throw $e;
        }
    }
}