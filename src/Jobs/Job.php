<?php

declare(strict_types=1);

namespace App\Jobs;

use App\Core\Container\Container;

abstract class Job
{
    abstract public function handle(Container $container): void;

    public function failed(?\Throwable $exception = null): void
    {
        // Override in child classes to handle failures
    }
}