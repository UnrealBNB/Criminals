<?php

declare(strict_types=1);

namespace App\Models;

use App\Core\Database\Model;
use App\Enums\UserType;
use DateTime;

class User extends Model
{
    protected string $table = 'users';

    protected array $fillable = [
        'username',
        'email',
        'password',
        'type',
        'level',
        'activated',
        'website',
        'info',
        'online_time',
        'attack_power',
        'defence_power',
        'clicks',
        'clicks_today',
        'bank',
        'cash',
        'showonline',
        'protection',
        'hlround',
        'clan_id',
        'clan_level',
        'attacks_won',
        'attacks_lost',
        'bank_left',
        'country_id',
        'session_id',
    ];

    protected array $hidden = [
        'password',
        'session_id',
        'password_reset_token',
    ];

    protected array $casts = [
        'id' => 'integer',
        'type' => 'integer',
        'level' => 'integer',
        'activated' => 'boolean',
        'attack_power' => 'integer',
        'defence_power' => 'integer',
        'clicks' => 'integer',
        'clicks_today' => 'integer',
        'bank' => 'integer',
        'cash' => 'integer',
        'showonline' => 'boolean',
        'protection' => 'boolean',
        'hlround' => 'integer',
        'clan_id' => 'integer',
        'clan_level' => 'integer',
        'attacks_won' => 'integer',
        'attacks_lost' => 'integer',
        'bank_left' => 'integer',
        'country_id' => 'integer',
        'online_time' => 'datetime',
        'signup_date' => 'datetime',
    ];

    // Relationships

    public function clan(): ?Clan
    {
        if (!$this->clan_id) {
            return null;
        }

        return Clan::find($this->clan_id);
    }

    public function items(): array
    {
        $results = db()->fetchAll(
            "SELECT i.*, ui.item_count 
             FROM user_items ui 
             JOIN items i ON ui.item_id = i.item_id 
             WHERE ui.user_id = :user_id",
            ['user_id' => $this->id]
        );

        return array_map(fn($row) => new Item($row), $results);
    }

    public function messages(): array
    {
        return Message::query()
            ->where('message_to_id', $this->id)
            ->where('message_deleted_to', 0)
            ->orderBy('message_time', 'DESC')
            ->get();
    }

    public function clicks(): array
    {
        return Click::query()
            ->where('userid', $this->id)
            ->get();
    }

    // Computed Properties

    public function getTotalAttackPower(): int
    {
        return $this->attack_power + ($this->clicks * 5);
    }

    public function getTotalDefencePower(): int
    {
        return $this->defence_power + ($this->clicks * 5);
    }

    public function getTypeName(): string
    {
        return UserType::tryFrom($this->type)?->label() ?? 'Unknown';
    }

    public function getRank(): string
    {
        $ranks = config('game.ranks', []);

        foreach ($ranks as $rank) {
            if ($this->attack_power >= $rank['power_low'] &&
                $this->attack_power < $rank['power_high']) {
                return $rank['name'];
            }
        }

        return end($ranks)['name'] ?? 'Unknown';
    }

    public function getCountryName(): string
    {
        $countries = config('game.countries', []);
        return $countries[$this->country_id] ?? 'Unknown';
    }

    // Status Checks

    public function isProtected(): bool
    {
        return $this->protection === 1;
    }

    public function isOnline(): bool
    {
        if (!$this->online_time) {
            return false;
        }

        return $this->online_time->getTimestamp() > (time() - 300);
    }

    public function isAdmin(): bool
    {
        return $this->level > 0;
    }

    public function hasAdminLevel(int $level): bool
    {
        return $this->level >= $level;
    }

    public function isInClan(): bool
    {
        return $this->clan_id > 0;
    }

    public function hasClanLevel(int $level): bool
    {
        return $this->clan_level >= $level;
    }

    public function isClanOwner(): bool
    {
        return $this->clan_level === 10;
    }

    // Money Operations

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
        if (!$this->canAfford($amount) || $this->bank_left < 1) {
            return false;
        }

        db()->beginTransaction();

        try {
            $this->cash -= $amount;
            $this->bank += $amount;
            $this->bank_left--;

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
        if ($this->bank < $amount || $this->bank_left < 1) {
            return false;
        }

        db()->beginTransaction();

        try {
            $this->bank -= $amount;
            $this->cash += $amount;
            $this->bank_left--;

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
            "SELECT item_count FROM user_items WHERE user_id = :user_id AND item_id = :item_id",
            ['user_id' => $this->id, 'item_id' => $itemId]
        );

        if ($existing) {
            return db()->execute(
                    "UPDATE user_items SET item_count = item_count + :quantity 
                 WHERE user_id = :user_id AND item_id = :item_id",
                    ['user_id' => $this->id, 'item_id' => $itemId, 'quantity' => $quantity]
                ) > 0;
        }

        return db()->insert('user_items', [
                'user_id' => $this->id,
                'item_id' => $itemId,
                'item_count' => $quantity,
            ]) > 0;
    }

    public function removeItem(int $itemId, int $quantity = 1): bool
    {
        $existing = db()->fetchOne(
            "SELECT item_count FROM user_items WHERE user_id = :user_id AND item_id = :item_id",
            ['user_id' => $this->id, 'item_id' => $itemId]
        );

        if (!$existing || $existing['item_count'] < $quantity) {
            return false;
        }

        if ($existing['item_count'] === $quantity) {
            return db()->delete(
                    'user_items',
                    'user_id = :user_id AND item_id = :item_id',
                    ['user_id' => $this->id, 'item_id' => $itemId]
                ) > 0;
        }

        return db()->execute(
                "UPDATE user_items SET item_count = item_count - :quantity 
             WHERE user_id = :user_id AND item_id = :item_id",
                ['user_id' => $this->id, 'item_id' => $itemId, 'quantity' => $quantity]
            ) > 0;
    }

    public function hasItem(int $itemId, int $quantity = 1): bool
    {
        $count = db()->fetchColumn(
            "SELECT item_count FROM user_items WHERE user_id = :user_id AND item_id = :item_id",
            ['user_id' => $this->id, 'item_id' => $itemId]
        );

        return $count >= $quantity;
    }

    // Message Operations

    public function unreadMessageCount(): int
    {
        return (int) db()->fetchColumn(
            "SELECT COUNT(*) FROM messages 
             WHERE message_to_id = :user_id 
             AND message_deleted_to = 0 
             AND message_read = 0",
            ['user_id' => $this->id]
        );
    }

    public function sendMessage(User $to, string $subject, string $message): int
    {
        return db()->insert('messages', [
            'message_from_id' => $this->id,
            'message_to_id' => $to->id,
            'message_subject' => $subject,
            'message_message' => $message,
            'message_time' => date('Y-m-d H:i:s'),
        ]);
    }

    // Attack Operations

    public function canAttack(User $target): array
    {
        if ($target->isProtected()) {
            return ['allowed' => false, 'reason' => 'Target is under protection'];
        }

        if ($this->type === $target->type) {
            return ['allowed' => false, 'reason' => 'Cannot attack same type'];
        }

        if ($this->id === $target->id) {
            return ['allowed' => false, 'reason' => 'Cannot attack yourself'];
        }

        if ($target->cash < 1000) {
            return ['allowed' => false, 'reason' => 'Target has too little money'];
        }

        $attacksToday = (int) db()->fetchColumn(
            "SELECT COUNT(*) FROM attack_logs 
             WHERE attacker_id = :attacker 
             AND defender_id = :defender 
             AND DATE(created_at) = CURDATE()",
            ['attacker' => $this->id, 'defender' => $target->id]
        );

        if ($attacksToday >= config('game.max_attacks_per_target', 5)) {
            return ['allowed' => false, 'reason' => 'Daily attack limit reached'];
        }

        return ['allowed' => true];
    }

    // Authentication

    public function setPassword(string $password): void
    {
        $this->password = bcrypt($password);
    }

    public function verifyPassword(string $password): bool
    {
        // Check modern hash
        if (password_get_info($this->password)['algo']) {
            return password_verify($password, $this->password);
        }

        // Legacy crypt compatibility
        return hash_equals($this->password, crypt($password, $this->password));
    }

    public function updateOnlineTime(): void
    {
        $this->online_time = new DateTime();
        $this->save();
    }

    // Scopes

    public static function online(): array
    {
        return static::query()
            ->where('online_time', '>', date('Y-m-d H:i:s', time() - 300))
            ->get();
    }

    public static function activated(): array
    {
        return static::query()
            ->where('activated', 1)
            ->get();
    }

    public static function admins(): array
    {
        return static::query()
            ->where('level', '>', 0)
            ->orderBy('level', 'DESC')
            ->get();
    }
}