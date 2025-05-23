<?php

declare(strict_types=1);

namespace App\Models;

use App\Core\Database\Model;

class Click extends Model
{
    protected string $table = 'clicks';

    protected array $fillable = [
        'userid',
        'clicked_ip',
    ];

    protected array $casts = [
        'userid' => 'integer',
    ];

    public static function hasClickedToday(int $userId, string $ip): bool
    {
        return static::query()
            ->where('userid', $userId)
            ->where('clicked_ip', $ip)
            ->exists();
    }

    public static function recordClick(int $userId, string $ip): bool
    {
        return db()->insert('clicks', [
                'userid' => $userId,
                'clicked_ip' => $ip,
            ]) > 0;
    }

    public static function getClicksToday(int $userId): int
    {
        return static::query()
            ->where('userid', $userId)
            ->count();
    }

    public static function clearAllClicks(): int
    {
        return db()->execute("TRUNCATE TABLE clicks");
    }
}