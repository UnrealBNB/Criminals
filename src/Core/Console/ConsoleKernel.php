<?php

declare(strict_types=1);

namespace App\Core\Console;

use App\Core\Container\Container;

class ConsoleKernel
{
    private array $commands = [];

    public function __construct(
        private readonly Container $container
    ) {
        $this->registerCommands();
    }

    public function handle(array $argv): int
    {
        array_shift($argv); // Remove script name

        if (empty($argv)) {
            $this->showHelp();
            return 0;
        }

        $commandName = array_shift($argv);

        if (!isset($this->commands[$commandName])) {
            $this->error("Command '{$commandName}' not found.");
            return 1;
        }

        try {
            $command = $this->container->make($this->commands[$commandName]);
            return $command->handle($argv);
        } catch (\Throwable $e) {
            $this->error("Error: " . $e->getMessage());
            return 1;
        }
    }

    private function registerCommands(): void
    {
        $this->commands = [
            'migrate' => \App\Console\Commands\MigrateCommand::class,
            'migrate:rollback' => \App\Console\Commands\MigrateRollbackCommand::class,
            'migrate:fresh' => \App\Console\Commands\MigrateFreshCommand::class,
            'cron:daily' => \App\Console\Commands\CronDailyCommand::class,
            'cron:hourly' => \App\Console\Commands\CronHourlyCommand::class,
            'cron:horse-race' => \App\Console\Commands\CronHorseRaceCommand::class,
            'user:create-admin' => \App\Console\Commands\CreateAdminCommand::class,
            'cache:clear' => \App\Console\Commands\CacheClearCommand::class,
        ];
    }

    private function showHelp(): void
    {
        $this->info("Criminals Game Console");
        $this->info("=====================");
        $this->info("");
        $this->info("Available commands:");

        foreach ($this->commands as $name => $class) {
            $this->info("  {$name}");
        }
    }

    private function info(string $message): void
    {
        echo "\033[32m{$message}\033[0m\n";
    }

    private function error(string $message): void
    {
        echo "\033[31m{$message}\033[0m\n";
    }
}