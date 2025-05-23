<?php

declare(strict_types=1);

namespace App\Events;

use App\Models\Clan;
use App\Models\User;

class ClanCreatedEvent extends Event
{
    public function __construct(
        public readonly Clan $clan,
        public readonly User $founder
    ) {}
}