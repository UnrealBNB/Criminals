<?php

declare(strict_types=1);

namespace App\Core\Console;

abstract class Command
{
    protected string $name = '';
    protected string $description = '';

    abstract public function handle(array $args): int;

    protected function info(string $message): void
    {
        echo "\033[32m{$message}\033[0m\n";
    }

    protected function error(string $message): void
    {
        echo "\033[31m{$message}\033[0m\n";
    }

    protected function warning(string $message): void
    {
        echo "\033[33m{$message}\033[0m\n";
    }

    protected function line(string $message): void
    {
        echo "{$message}\n";
    }

    protected function ask(string $question): string
    {
        echo "{$question}: ";
        return trim(fgets(STDIN));
    }

    protected function confirm(string $question): bool
    {
        $answer = $this->ask("{$question} (yes/no)");
        return in_array(strtolower($answer), ['yes', 'y']);
    }

    protected function choice(string $question, array $choices): string
    {
        $this->line($question);

        foreach ($choices as $key => $choice) {
            $this->line("  [{$key}] {$choice}");
        }

        $answer = $this->ask("Your choice");

        if (!isset($choices[$answer])) {
            $this->error("Invalid choice.");
            return $this->choice($question, $choices);
        }

        return $answer;
    }

    protected function table(array $headers, array $rows): void
    {
        $columnWidths = [];

        foreach ($headers as $i => $header) {
            $columnWidths[$i] = strlen($header);
        }

        foreach ($rows as $row) {
            foreach ($row as $i => $cell) {
                $columnWidths[$i] = max($columnWidths[$i] ?? 0, strlen((string) $cell));
            }
        }

        // Print headers
        $headerLine = '| ';
        foreach ($headers as $i => $header) {
            $headerLine .= str_pad($header, $columnWidths[$i]) . ' | ';
        }
        $this->line($headerLine);

        // Print separator
        $separator = '+';
        foreach ($columnWidths as $width) {
            $separator .= str_repeat('-', $width + 2) . '+';
        }
        $this->line($separator);

        // Print rows
        foreach ($rows as $row) {
            $rowLine = '| ';
            foreach ($row as $i => $cell) {
                $rowLine .= str_pad((string) $cell, $columnWidths[$i]) . ' | ';
            }
            $this->line($rowLine);
        }
    }
}