<?php

declare(strict_types=1);

namespace App\Events;

use App\Core\Container\Container;

class EventDispatcher
{
    private array $listeners = [];

    public function __construct(
        private readonly Container $container
    ) {}

    public function listen(string $event, string|callable $listener): void
    {
        $this->listeners[$event][] = $listener;
    }

    public function dispatch(Event $event): void
    {
        $eventName = get_class($event);

        if (!isset($this->listeners[$eventName])) {
            return;
        }

        foreach ($this->listeners[$eventName] as $listener) {
            if (is_string($listener)) {
                $listener = $this->container->make($listener);
            }

            if (is_callable($listener)) {
                $listener($event);
            } elseif (is_object($listener) && method_exists($listener, 'handle')) {
                $listener->handle($event);
            }
        }
    }

    public function forget(string $event): void
    {
        unset($this->listeners[$event]);
    }

    public function hasListeners(string $event): bool
    {
        return isset($this->listeners[$event]) && count($this->listeners[$event]) > 0;
    }

    public function getListeners(string $event): array
    {
        return $this->listeners[$event] ?? [];
    }
}