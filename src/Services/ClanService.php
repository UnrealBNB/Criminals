<?php

declare(strict_types=1);

namespace App\Services;

use App\Models\Clan;
use App\Models\User;
use App\Models\Message;

class ClanService
{
    public function createClan(User $user, string $name): array
    {
        if ($user->isInClan()) {
            return ['success' => false, 'message' => 'You are already in a clan'];
        }

        db()->beginTransaction();

        try {
            $clanId = db()->insert('clans', [
                'clan_name' => $name,
                'clan_owner_id' => $user->id,
                'clan_type' => $user->type,
                'clan_clicks' => 0,
                'attack_power' => 0,
                'defence_power' => 0,
                'cash' => 0,
                'bank' => 0,
                'bankleft' => 10,
                'clicks_today' => 0,
            ]);

            $user->clan_id = $clanId;
            $user->clan_level = 10;
            $user->save();

            db()->commit();
            return ['success' => true];
        } catch (\Throwable $e) {
            db()->rollBack();
            return ['success' => false, 'message' => 'Failed to create clan'];
        }
    }

    public function deleteClan(User $user): array
    {
        if (!$user->isClanOwner()) {
            return ['success' => false, 'message' => 'Only clan owners can delete clans'];
        }

        db()->beginTransaction();

        try {
            $clanId = $user->clan_id;

            // Remove all members
            db()->execute(
                "UPDATE users SET clan_id = 0, clan_level = 0 WHERE clan_id = :clan_id",
                ['clan_id' => $clanId]
            );

            // Delete clan items
            db()->delete('clan_items', 'clan_id = :clan_id', ['clan_id' => $clanId]);

            // Delete clan
            db()->delete('clans', 'clan_id = :clan_id', ['clan_id' => $clanId]);

            db()->commit();
            return ['success' => true];
        } catch (\Throwable $e) {
            db()->rollBack();
            return ['success' => false, 'message' => 'Failed to delete clan'];
        }
    }

    public function applyToClan(User $user, Clan $clan): array
    {
        if ($user->isInClan()) {
            return ['success' => false, 'message' => 'You are already in a clan'];
        }

        // Check if already applied
        $existing = db()->fetchOne(
            "SELECT * FROM temp WHERE userid = :user_id AND area = 'clan_join'",
            ['user_id' => $user->id]
        );

        if ($existing) {
            return ['success' => false, 'message' => 'You already have a pending application'];
        }

        db()->insert('temp', [
            'userid' => $user->id,
            'area' => 'clan_join',
            'variable' => (string) $clan->clan_id,
            'extra' => null,
        ]);

        return ['success' => true];
    }

    public function leaveClan(User $user): array
    {
        if (!$user->isInClan()) {
            return ['success' => false, 'message' => 'You are not in a clan'];
        }

        if ($user->isClanOwner()) {
            return ['success' => false, 'message' => 'Clan owners cannot leave'];
        }

        $user->clan_id = 0;
        $user->clan_level = 0;
        $user->save();

        return ['success' => true];
    }

    public function acceptApplication(User $acceptor, User $applicant): array
    {
        if (!$acceptor->hasClanLevel(5)) {
            return ['success' => false, 'message' => 'Insufficient permissions'];
        }

        $clan = $acceptor->clan();

        if (!$clan->hasSpace()) {
            return ['success' => false, 'message' => 'Clan is full'];
        }

        return $clan->acceptApplication($applicant)
            ? ['success' => true]
            : ['success' => false, 'message' => 'Failed to accept application'];
    }

    public function rejectApplication(User $rejector, User $applicant): array
    {
        if (!$rejector->hasClanLevel(5)) {
            return ['success' => false, 'message' => 'Insufficient permissions'];
        }

        $clan = $rejector->clan();

        return $clan->rejectApplication($applicant)
            ? ['success' => true]
            : ['success' => false, 'message' => 'Failed to reject application'];
    }

    public function kickMember(User $kicker, User $target): array
    {
        if (!$kicker->hasClanLevel(8)) {
            return ['success' => false, 'message' => 'Insufficient permissions'];
        }

        if ($target->clan_level >= $kicker->clan_level) {
            return ['success' => false, 'message' => 'Cannot kick equal or higher ranks'];
        }

        $clan = $kicker->clan();
        return $clan->removeMember($target)
            ? ['success' => true]
            : ['success' => false, 'message' => 'Failed to kick member'];
    }

    public function promoteMember(User $promoter, User $target, int $newLevel): array
    {
        if (!$promoter->hasClanLevel(9)) {
            return ['success' => false, 'message' => 'Insufficient permissions'];
        }

        if ($newLevel >= $promoter->clan_level) {
            return ['success' => false, 'message' => 'Cannot promote to equal or higher rank'];
        }

        $clan = $promoter->clan();
        return $clan->promoteMember($target, $newLevel)
            ? ['success' => true]
            : ['success' => false, 'message' => 'Failed to promote member'];
    }

    public function transferOwnership(User $currentOwner, User $newOwner): array
    {
        if (!$currentOwner->isClanOwner()) {
            return ['success' => false, 'message' => 'Only owners can transfer ownership'];
        }

        db()->beginTransaction();

        try {
            // Update clan owner
            db()->execute(
                "UPDATE clans SET clan_owner_id = :new_owner WHERE clan_id = :clan_id",
                ['new_owner' => $newOwner->id, 'clan_id' => $currentOwner->clan_id]
            );

            // Update user levels
            $currentOwner->clan_level = 1;
            $currentOwner->save();

            $newOwner->clan_level = 10;
            $newOwner->save();

            db()->commit();
            return ['success' => true];
        } catch (\Throwable $e) {
            db()->rollBack();
            return ['success' => false, 'message' => 'Failed to transfer ownership'];
        }
    }

    public function buyItems(User $user, array $purchases): array
    {
        if (!$user->hasClanLevel(7)) {
            return ['success' => false, 'message' => 'Insufficient permissions'];
        }

        $clan = $user->clan();
        $totalCost = array_sum(array_column($purchases, 'cost'));

        if ($totalCost > $clan->cash) {
            return ['success' => false, 'message' => 'Clan cannot afford this'];
        }

        db()->beginTransaction();

        try {
            foreach ($purchases as $purchase) {
                $item = $purchase['item'];
                $quantity = $purchase['quantity'];

                // Give item to clan
                $clan->giveItem($item->item_id, $quantity);

                // Update clan stats
                $clan->attack_power += $item->item_attack_power * $quantity;
                $clan->defence_power += $item->item_defence_power * $quantity;
            }

            // Deduct cash
            $clan->cash -= $totalCost;
            $clan->save();

            db()->commit();
            return ['success' => true];
        } catch (\Throwable $e) {
            db()->rollBack();
            return ['success' => false, 'message' => 'Purchase failed'];
        }
    }

    public function sellItems(User $user, array $sales): array
    {
        if (!$user->hasClanLevel(7)) {
            return ['success' => false, 'message' => 'Insufficient permissions'];
        }

        $clan = $user->clan();
        $totalRevenue = array_sum(array_column($sales, 'revenue'));

        db()->beginTransaction();

        try {
            foreach ($sales as $sale) {
                $item = $sale['item'];
                $quantity = $sale['quantity'];

                // Remove item from clan
                $clan->removeItem($item->item_id, $quantity);

                // Update clan stats
                $clan->attack_power -= $item->item_attack_power * $quantity;
                $clan->defence_power -= $item->item_defence_power * $quantity;
            }

            // Add cash
            $clan->cash += $totalRevenue;
            $clan->save();

            db()->commit();
            return ['success' => true];
        } catch (\Throwable $e) {
            db()->rollBack();
            return ['success' => false, 'message' => 'Sale failed'];
        }
    }

    public function clanBankDeposit(User $user, int $amount): array
    {
        if (!$user->hasClanLevel(6)) {
            return ['success' => false, 'message' => 'Insufficient permissions'];
        }

        $clan = $user->clan();

        if ($clan->deposit($amount)) {
            return ['success' => true];
        }

        return ['success' => false, 'message' => 'Deposit failed'];
    }

    public function clanBankWithdraw(User $user, int $amount): array
    {
        if (!$user->hasClanLevel(6)) {
            return ['success' => false, 'message' => 'Insufficient permissions'];
        }

        $clan = $user->clan();

        if ($clan->withdraw($amount)) {
            return ['success' => true];
        }

        return ['success' => false, 'message' => 'Withdrawal failed'];
    }

    public function donateToClean(User $user, int $amount): array
    {
        if (!$user->isInClan()) {
            return ['success' => false, 'message' => 'You are not in a clan'];
        }

        if (!$user->canAfford($amount)) {
            return ['success' => false, 'message' => 'Insufficient funds'];
        }

        db()->beginTransaction();

        try {
            $user->removeCash($amount);

            $clan = $user->clan();
            $clan->addCash($amount);

            db()->commit();
            return ['success' => true];
        } catch (\Throwable $e) {
            db()->rollBack();
            return ['success' => false, 'message' => 'Donation failed'];
        }
    }

    public function sendClanMessage(User $sender, string $subject, string $message): array
    {
        if (!$sender->hasClanLevel(7)) {
            return ['success' => false, 'message' => 'Insufficient permissions'];
        }

        $clan = $sender->clan();
        $sent = Message::sendToClan($sender->id, $clan->clan_id, $subject, $message);

        return ['success' => true, 'sent' => $sent];
    }
}