<?php

declare(strict_types=1);

namespace App\Listeners;

use App\Events\Event;

abstract class Listener
{
    abstract public function handle(Event $event): void;
}