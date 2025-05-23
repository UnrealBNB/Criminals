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

    // Relationships

    public function owner(): ?User
    {
        return User::find($this->clan_owner_id);
    }

    public function members(): array
    {
        return User::query()
            ->where('clan_id', $this->clan_id)
            ->orderBy('clan_level', 'DESC')
            ->get();
    }

    public function items(): array
    {
        $results = db()->fetchAll(
            "SELECT i.*, ci.item_count 
             FROM clan_items ci 
             JOIN items i ON ci.item_id = i.item_id 
             WHERE ci.clan_id = :clan_id",
            ['clan_id' => $this->clan_id]
        );

        return array_map(fn($row) => new Item($row), $results);
    }

    // Member Management

    public function addMember(User $user, int $level = 1): bool
    {
        $user->clan_id = $this->clan_id;
        $user->clan_level = $level;
        return $user->save();
    }

    public function removeMember(User $user): bool
    {
        $user->clan_id = 0;
        $user->clan_level = 0;
        return $user->save();
    }

    public function promoteMember(User $user, int $newLevel): bool
    {
        if ($user->clan_id !== $this->clan_id) {
            return false;
        }

        $user->clan_level = $newLevel;
        return $user->save();
    }

    public function getMemberCount(): int
    {
        return (int) db()->fetchColumn(
            "SELECT COUNT(*) FROM users WHERE clan_id = :clan_id",
            ['clan_id' => $this->clan_id]
        );
    }

    public function hasSpace(): bool
    {
        $houseCount = (int) db()->fetchColumn(
            "SELECT item_count FROM clan_items WHERE clan_id = :clan_id AND item_id = 27",
            ['clan_id' => $this->clan_id]
        );

        return ($houseCount * 5) > $this->getMemberCount();
    }

    // Financial Operations

    public function canAfford(int $amount): bool
    {
        return $this->cash >= $amount;
    }

    public function addCash(int $amount): bool
    {
        $this->cash += $amount;
        return $this->save();
    }

    public function removeCash(int $amount): bool
    {
        if (!$this->canAfford($amount)) {
            return false;
        }

        $this->cash -= $amount;
        return $this->save();
    }

    public function deposit(int $amount): bool
    {
        if (!$this->canAfford($amount) || $this->bankleft < 1) {
            return false;
        }

        db()->beginTransaction();

        try {
            $this->cash -= $amount;
            $this->bank += $amount;
            $this->bankleft--;

            $result = $this->save();

            if ($result) {
                db()->commit();
                return true;
            }

            db()->rollBack();
            return false;
        } catch (\Throwable $e) {
            db()->rollBack();
            throw $e;
        }
    }

    public function withdraw(int $amount): bool
    {
        if ($this->bank < $amount) {
            return false;
        }

        db()->beginTransaction();

        try {
            $this->bank -= $amount;
            $this->cash += $amount;

            $result = $this->save();

            if ($result) {
                db()->commit();
                return true;
            }

            db()->rollBack();
            return false;
        } catch (\Throwable $e) {
            db()->rollBack();
            throw $e;
        }
    }

    // Item Operations

    public function giveItem(int $itemId, int $quantity = 1): bool
    {
        $existing = db()->fetchOne(
            "SELECT item_count FROM clan_items WHERE clan_id = :clan_id AND item_id = :item_id",
            ['clan_id' => $this->clan_id, 'item_id' => $itemId]
        );

        if ($existing) {
            return db()->execute(
                    "UPDATE clan_items SET item_count = item_count + :quantity 
                 WHERE clan_id = :clan_id AND item_id = :item_id",
                    ['clan_id' => $this->clan_id, 'item_id' => $itemId, 'quantity' => $quantity]
                ) > 0;
        }

        return db()->insert('clan_items', [
                'clan_id' => $this->clan_id,
                'item_id' => $itemId,
                'item_count' => $quantity,
            ]) > 0;
    }

    public function removeItem(int $itemId, int $quantity = 1): bool
    {
        $existing = db()->fetchOne(
            "SELECT item_count FROM clan_items WHERE clan_id = :clan_id AND item_id = :item_id",
            ['clan_id' => $this->clan_id, 'item_id' => $itemId]
        );

        if (!$existing || $existing['item_count'] < $quantity) {
            return false;
        }

        if ($existing['item_count'] === $quantity) {
            return db()->delete(
                    'clan_items',
                    'clan_id = :clan_id AND item_id = :item_id',
                    ['clan_id' => $this->clan_id, 'item_id' => $itemId]
                ) > 0;
        }

        return db()->execute(
                "UPDATE clan_items SET item_count = item_count - :quantity 
             WHERE clan_id = :clan_id AND item_id = :item_id",
                ['clan_id' => $this->clan_id, 'item_id' => $itemId, 'quantity' => $quantity]
            ) > 0;
    }

    // Application Management

    public function getPendingApplications(): array
    {
        $results = db()->fetchAll(
            "SELECT u.* FROM temp t 
             JOIN users u ON t.userid = u.id 
             WHERE t.area = 'clan_join' AND t.variable = :clan_id",
            ['clan_id' => $this->clan_id]
        );

        return array_map(fn($row) => new User($row), $results);
    }

    public function acceptApplication(User $user): bool
    {
        if (!$this->hasSpace()) {
            return false;
        }

        db()->beginTransaction();

        try {
            // Remove application
            db()->delete(
                'temp',
                "userid = :user_id AND area = 'clan_join'",
                ['user_id' => $user->id]
            );

            // Add to clan
            $this->addMember($user);

            db()->commit();
            return true;
        } catch (\Throwable $e) {
            db()->rollBack();
            throw $e;
        }
    }

    public function rejectApplication(User $user): bool
    {
        return db()->delete(
                'temp',
                "userid = :user_id AND area = 'clan_join' AND variable = :clan_id",
                ['user_id' => $user->id, 'clan_id' => $this->clan_id]
            ) > 0;
    }

    // Power Calculations

    public function getTotalPower(): int
    {
        return (int) db()->fetchColumn(
            "SELECT SUM(attack_power + (clicks * 5)) FROM users WHERE clan_id = :clan_id",
            ['clan_id' => $this->clan_id]
        );
    }

    // Static Methods

    public static function findByName(string $name): ?self
    {
        $result = db()->fetchOne(
            "SELECT * FROM clans WHERE clan_name = :name",
            ['name' => $name]
        );

        return $result ? new self($result) : null;
    }
}