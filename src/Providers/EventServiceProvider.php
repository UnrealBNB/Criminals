<?php

declare(strict_types=1);

namespace App\Providers;

use App\Core\Container\ServiceProvider;
use App\Events\EventDispatcher;
use App\Events\UserLevelUpEvent;
use App\Events\AttackCompletedEvent;
use App\Events\ClanCreatedEvent;
use App\Listeners\SendLevelUpNotification;
use App\Listeners\RecordAttackStatistics;
use App\Listeners\NotifyClanMembers;

class EventServiceProvider extends ServiceProvider
{
    protected array $listen = [
        UserLevelUpEvent::class => [
            SendLevelUpNotification::class,
        ],
        AttackCompletedEvent::class => [
            RecordAttackStatistics::class,
        ],
        ClanCreatedEvent::class => [
            NotifyClanMembers::class,
        ],
    ];

    public function register(): void
    {
        $this->container->singleton(EventDispatcher::class, function ($container) {
            return new EventDispatcher($container);
        });
    }

    public function boot(): void
    {
        $dispatcher = $this->container->get(EventDispatcher::class);

        foreach ($this->listen as $event => $listeners) {
            foreach ($listeners as $listener) {
                $dispatcher->listen($event, $listener);
            }
        }
    }
}