<?php

declare(strict_types=1);

namespace App\Events;

use App\Models\User;

class AttackCompletedEvent extends Event
{
    public function __construct(
        public readonly User $attacker,
        public readonly User $defender,
        public readonly bool $attackerWon,
        public readonly int $moneyTransferred
    ) {}
}