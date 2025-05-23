<?php

declare(strict_types=1);

namespace App\Listeners;

use App\Events\Event;
use App\Events\UserLevelUpEvent;
use App\Models\Message;
use App\Services\QueueService;
use App\Jobs\SendEmailJob;

class SendLevelUpNotification extends Listener
{
    public function __construct(
        private readonly QueueService $queueService
    ) {}

    public function handle(Event $event): void
    {
        if (!$event instanceof UserLevelUpEvent) {
            return;
        }

        // Send in-game message
        Message::send(
            0, // System message
            $event->user->id,
            'Congratulations on your promotion!',
            "You have been promoted from {$event->oldRank} to {$event->newRank}! Keep up the good work!"
        );

        // Queue email notification
        $job = new SendEmailJob(
            $event->user->email,
            'Level Up in Criminals Game!',
            "Congratulations {$event->user->username}, you've reached {$event->newRank}!"
        );

        $this->queueService->push($job);
    }
}