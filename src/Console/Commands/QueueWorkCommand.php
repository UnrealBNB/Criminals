<?php

declare(strict_types=1);

namespace App\Console\Commands;

use App\Core\Console\Command;
use App\Services\QueueService;

class QueueWorkCommand extends Command
{
    protected string $name = 'queue:work';
    protected string $description = 'Process queued jobs';

    public function __construct(
        private readonly QueueService $queueService
    ) {}

    public function handle(array $args): int
    {
        $queue = $args[0] ?? 'default';
        $this->info("Processing queue: {$queue}");

        pcntl_signal(SIGTERM, [$this, 'shutdown']);
        pcntl_signal(SIGINT, [$this, 'shutdown']);

        $this->shouldContinue = true;

        while ($this->shouldContinue) {
            $this->queueService->process($queue);

            if ($this->queueService->getPendingCount($queue) === 0) {
                sleep(1);
            }

            pcntl_signal_dispatch();
        }

        $this->info('Queue worker stopped.');
        return 0;
    }

    protected function shutdown(): void
    {
        $this->shouldContinue = false;
    }

    private bool $shouldContinue = true;
}