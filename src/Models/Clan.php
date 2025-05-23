<?php

declare(strict_types=1);

namespace App\Models;

use App\Core\Database\Model;

class Clan extends Model
{
    protected string $table = 'clans';
    protected string $primaryKey = 'clan_id';

    protected array $fillable = [
        'clan_name',
        'clan_owner_id',
        'clan_type',
        'clan_clicks',
        'attack_power',
        'defence_power',
        'cash',
        'bank',
        'bankleft',
        'clicks_today',
    ];

    protected array $casts = [
        'clan_id' => 'integer',
        'clan_owner_id' => 'integer',
        'clan_type' => 'integer',
        'clan_clicks' => 'integer',
        'attack_power' => 'integer',
        'defence_power' => 'integer',
        'cash' => 'integer',
        'bank' => 'integer',
        'bankleft' => 'integer',
        'clicks_today' => 'integer',
    ];

    public function owner(): ?User
    {
        return User::find($this->clan_owner_id);
    }

    public function members(): array
    {
        return User::query()
            ->where('clan_id', $this->clan_id)
            ->orderBy('clan_level', 'DESC')
            ->orderBy('username', 'ASC')
            ->get();
    }

    public function memberCount(): int
    {
        return (int) db()->fetchColumn(
            "SELECT COUNT(*) FROM users WHERE clan_id = :clan_id",
            ['clan_id' => $this->clan_id]
        );
    }

    public function getTotalPower(): int
    {
        return (int) db()->fetchColumn(
            "SELECT SUM(attack_power + (clicks * 5)) FROM users WHERE clan_id = :clan_id",
            ['clan_id' => $this->clan_id]
        );
    }

    public function canAcceptMoreMembers(): bool
    {
        $houseCount = (int) db()->fetchColumn(
            "SELECT item_count FROM clan_items WHERE clan_id = :clan_id AND item_id = 27",
            ['clan_id' => $this->clan_id]
        ) ?? 0;

        return ($houseCount * 5) > $this->memberCount();
    }
}