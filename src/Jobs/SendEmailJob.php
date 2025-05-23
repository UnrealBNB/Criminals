<?php

declare(strict_types=1);

namespace App\Jobs;

use App\Core\Container\Container;
use App\Services\EmailService;

class SendEmailJob extends Job
{
    public function __construct(
        private readonly string $to,
        private readonly string $subject,
        private readonly string $body,
        private readonly array $options = []
    ) {}

    public function handle(Container $container): void
    {
        $emailService = $container->get(EmailService::class);
        $emailService->send($this->to, $this->subject, $this->body, $this->options);
    }
}