<?php

declare(strict_types=1);

namespace App\Models;

use App\Core\Database\Model;

class Message extends Model
{
    protected string $table = 'messages';
    protected string $primaryKey = 'message_id';

    protected array $fillable = [
        'message_from_id',
        'message_to_id',
        'message_subject',
        'message_message',
        'message_read',
        'message_deleted_from',
        'message_deleted_to',
    ];

    protected array $casts = [
        'message_id' => 'integer',
        'message_from_id' => 'integer',
        'message_to_id' => 'integer',
        'message_read' => 'boolean',
        'message_deleted_from' => 'boolean',
        'message_deleted_to' => 'boolean',
        'message_time' => 'datetime',
    ];

    // Relationships

    public function sender(): ?User
    {
        return User::find($this->message_from_id);
    }

    public function recipient(): ?User
    {
        return User::find($this->message_to_id);
    }

    // Status Methods

    public function markAsRead(): bool
    {
        if ($this->message_read) {
            return true;
        }

        $this->message_read = 1;
        return $this->save();
    }

    public function isRead(): bool
    {
        return $this->message_read === 1;
    }

    public function deleteForSender(): bool
    {
        $this->message_deleted_from = 1;

        if ($this->message_deleted_to) {
            return $this->delete();
        }

        return $this->save();
    }

    public function deleteForRecipient(): bool
    {
        $this->message_deleted_to = 1;

        if ($this->message_deleted_from) {
            return $this->delete();
        }

        return $this->save();
    }

    // Query Methods

    public static function getInbox(int $userId): array
    {
        return static::query()
            ->where('message_to_id', $userId)
            ->where('message_deleted_to', 0)
            ->orderBy('message_time', 'DESC')
            ->get();
    }

    public static function getOutbox(int $userId): array
    {
        return static::query()
            ->where('message_from_id', $userId)
            ->where('message_deleted_from', 0)
            ->orderBy('message_time', 'DESC')
            ->get();
    }

    public static function getUnreadCount(int $userId): int
    {
        return static::query()
            ->where('message_to_id', $userId)
            ->where('message_deleted_to', 0)
            ->where('message_read', 0)
            ->count();
    }

    // Send Methods

    public static function send(int $fromId, int $toId, string $subject, string $message): ?self
    {
        return static::create([
            'message_from_id' => $fromId,
            'message_to_id' => $toId,
            'message_subject' => $subject,
            'message_message' => $message,
            'message_read' => 0,
            'message_deleted_from' => 0,
            'message_deleted_to' => 0,
        ]);
    }

    public static function sendMassMessage(int $fromId, array $toIds, string $subject, string $message): int
    {
        $sent = 0;

        foreach ($toIds as $toId) {
            if (static::send($fromId, $toId, $subject, $message)) {
                $sent++;
            }
        }

        return $sent;
    }

    public static function sendToClan(int $fromId, int $clanId, string $subject, string $message): int
    {
        $members = db()->fetchAll(
            "SELECT id FROM users WHERE clan_id = :clan_id",
            ['clan_id' => $clanId]
        );

        $toIds = array_column($members, 'id');
        return static::sendMassMessage($fromId, $toIds, $subject, $message);
    }
}