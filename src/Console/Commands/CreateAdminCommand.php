<?php

declare(strict_types=1);

namespace App\Console\Commands;

use App\Core\Console\Command;
use App\Models\User;

class CreateAdminCommand extends Command
{
    protected string $name = 'user:create-admin';
    protected string $description = 'Create an admin user';

    public function handle(array $args): int
    {
        $this->info('Creating admin user...');

        $username = $this->ask('Username');
        $email = $this->ask('Email');
        $password = $this->ask('Password');

        $type = (int) $this->choice('User type', [
            '1' => 'Drug Dealer',
            '2' => 'Scientist',
            '3' => 'Police',
        ]);

        try {
            $user = User::create([
                'username' => $username,
                'email' => $email,
                'password' => bcrypt($password),
                'type' => $type,
                'level' => 10,
                'activated' => 1,
                'protection' => 0,
                'attack_power' => 1000,
                'defence_power' => 1000,
                'cash' => 100000,
                'bank' => 1000000,
            ]);

            $this->info("Admin user '{$username}' created successfully!");
            return 0;
        } catch (\Throwable $e) {
            $this->error('Failed to create admin: ' . $e->getMessage());
            return 1;
        }
    }
}