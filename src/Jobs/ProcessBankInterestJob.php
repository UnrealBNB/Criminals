<?php

declare(strict_types=1);

namespace App\Jobs;

use App\Core\Container\Container;
use App\Core\Database\Database;

class ProcessBankInterestJob extends Job
{
    public function handle(Container $container): void
    {
        $db = $container->get(Database::class);

        $db->execute('UPDATE users SET bank = bank * 1.05 WHERE activated = 1');
        $db->execute('UPDATE clans SET bank = bank * 1.05');
    }
}