<?php

declare(strict_types=1);

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\User;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;

class AdminUserController extends Controller
{
    public function index(Request $request): Response
    {
        $user = $this->auth()->user();

        if (!$user->hasAdminLevel(3)) {
            return $this->redirect('/game');
        }

        $search = $request->query->get('search', '');
        $page = max(1, (int) $request->query->get('page', 1));
        $perPage = 50;

        $query = User::query();

        if ($search) {
            $query->where('username', 'LIKE', "%{$search}%")
                ->orWhere('email', 'LIKE', "%{$search}%");
        }

        $total = $query->count();
        $users = $query->orderBy('id', 'DESC')
            ->limit($perPage)
            ->offset(($page - 1) * $perPage)
            ->get();

        return $this->view('admin.users.index', [
            'users' => $users,
            'search' => $search,
            'pagination' => [
                'current' => $page,
                'total' => ceil($total / $perPage),
                'perPage' => $perPage,
            ],
        ]);
    }

    public function show(int $id): Response
    {
        $user = $this->auth()->user();

        if (!$user->hasAdminLevel(3)) {
            return $this->redirect('/game');
        }

        $targetUser = User::find($id);

        if (!$targetUser) {
            flash('error', 'User not found');
            return $this->redirect('/admin/users');
        }

        $items = $targetUser->items();
        $messages = $targetUser->messages();

        return $this->view('admin.users.show', [
            'user' => $user,
            'targetUser' => $targetUser,
            'items' => $items,
            'messages' => $messages,
        ]);
    }

    public function reset(Request $request, int $id): Response
    {
        $user = $this->auth()->user();

        if (!$user->hasAdminLevel(3)) {
            return $this->redirect('/game');
        }

        $targetUser = User::find($id);

        if (!$targetUser) {
            flash('error', 'User not found');
            return $this->redirect('/admin/users');
        }

        if ($request->isMethod('GET')) {
            return $this->view('admin.users.reset', [
                'user' => $user,
                'targetUser' => $targetUser,
            ]);
        }

        if ($request->request->get('confirm') !== 'yes') {
            flash('error', 'Please confirm the reset');
            return $this->back();
        }

        db()->beginTransaction();

        try {
            // Delete user items
            db()->delete('user_items', 'user_id = :user_id', ['user_id' => $targetUser->id]);

            // Delete temp data
            db()->delete('temp', 'userid = :user_id', ['user_id' => $targetUser->id]);

            // Delete clicks
            db()->delete('clicks', 'userid = :user_id', ['user_id' => $targetUser->id]);

            // Reset user stats
            $targetUser->attack_power = 0;
            $targetUser->defence_power = 0;
            $targetUser->cash = 0;
            $targetUser->bank = 0;
            $targetUser->clicks = 0;
            $targetUser->clicks_today = 0;
            $targetUser->protection = 1;
            $targetUser->clan_id = 0;
            $targetUser->clan_level = 0;
            $targetUser->attacks_won = 0;
            $targetUser->attacks_lost = 0;
            $targetUser->bank_left = 5;
            $targetUser->hlround = 1;
            $targetUser->save();

            db()->commit();
            flash('success', "User {$targetUser->username} has been reset successfully");
        } catch (\Throwable $e) {
            db()->rollBack();
            flash('error', 'Failed to reset user');
        }

        return $this->redirect('/admin/users');
    }

    public function delete(Request $request, int $id): Response
    {
        $user = $this->auth()->user();

        if (!$user->hasAdminLevel(3)) {
            return $this->redirect('/game');
        }

        $targetUser = User::find($id);

        if (!$targetUser) {
            flash('error', 'User not found');
            return $this->redirect('/admin/users');
        }

        if ($targetUser->id === $user->id) {
            flash('error', 'You cannot delete yourself');
            return $this->redirect('/admin/users');
        }

        if ($request->isMethod('GET')) {
            return $this->view('admin.users.delete', [
                'user' => $user,
                'targetUser' => $targetUser,
            ]);
        }

        if ($request->request->get('confirm') !== 'yes') {
            flash('error', 'Please confirm the deletion');
            return $this->back();
        }

        db()->beginTransaction();

        try {
            // Delete all related data
            db()->delete('user_items', 'user_id = :user_id', ['user_id' => $targetUser->id]);
            db()->delete('temp', 'userid = :user_id', ['user_id' => $targetUser->id]);
            db()->delete('clicks', 'userid = :user_id', ['user_id' => $targetUser->id]);
            db()->delete('messages', 'message_from_id = :user_id OR message_to_id = :user_id', ['user_id' => $targetUser->id]);

            // Remove from clan if in one
            if ($targetUser->clan_id) {
                if ($targetUser->isClanOwner()) {
                    // Transfer ownership or delete clan
                    $newOwner = db()->fetchOne(
                        "SELECT * FROM users WHERE clan_id = :clan_id AND id != :user_id ORDER BY clan_level DESC LIMIT 1",
                        ['clan_id' => $targetUser->clan_id, 'user_id' => $targetUser->id]
                    );

                    if ($newOwner) {
                        db()->execute(
                            "UPDATE clans SET clan_owner_id = :new_owner WHERE clan_id = :clan_id",
                            ['new_owner' => $newOwner['id'], 'clan_id' => $targetUser->clan_id]
                        );
                        db()->execute(
                            "UPDATE users SET clan_level = 10 WHERE id = :user_id",
                            ['user_id' => $newOwner['id']]
                        );
                    } else {
                        db()->delete('clans', 'clan_id = :clan_id', ['clan_id' => $targetUser->clan_id]);
                        db()->delete('clan_items', 'clan_id = :clan_id', ['clan_id' => $targetUser->clan_id]);
                    }
                }
            }

            // Delete the user
            $targetUser->delete();

            db()->commit();
            flash('success', "User {$targetUser->username} has been deleted successfully");
        } catch (\Throwable $e) {
            db()->rollBack();
            flash('error', 'Failed to delete user: ' . $e->getMessage());
        }

        return $this->redirect('/admin/users');
    }

    public function donate(Request $request, int $id): Response
    {
        $user = $this->auth()->user();

        if (!$user->hasAdminLevel(3)) {
            return $this->redirect('/game');
        }

        $targetUser = User::find($id);

        if (!$targetUser) {
            flash('error', 'User not found');
            return $this->redirect('/admin/users');
        }

        if ($request->isMethod('GET')) {
            return $this->view('admin.users.donate', [
                'user' => $user,
                'targetUser' => $targetUser,
            ]);
        }

        $amount = (int) $request->request->get('amount', 0);

        if ($amount <= 0) {
            flash('error', 'Please enter a valid amount');
            return $this->back();
        }

        $targetUser->bank += $amount;

        if ($targetUser->save()) {
            flash('success', "Donated {$amount} to {$targetUser->username}'s bank account");
        } else {
            flash('error', 'Failed to donate');
        }

        return $this->redirect("/admin/users/{$targetUser->id}");
    }

    public function changeLevel(Request $request, int $id): Response
    {
        $user = $this->auth()->user();

        if (!$user->hasAdminLevel(10)) {
            return $this->redirect('/game');
        }

        $targetUser = User::find($id);

        if (!$targetUser) {
            flash('error', 'User not found');
            return $this->redirect('/admin/users');
        }

        if ($targetUser->id === $user->id) {
            flash('error', 'You cannot change your own level');
            return $this->redirect('/admin/users');
        }

        if ($request->isMethod('GET')) {
            return $this->view('admin.users.change-level', [
                'user' => $user,
                'targetUser' => $targetUser,
            ]);
        }

        $level = (int) $request->request->get('level', 0);

        if ($level < 0 || $level > 10) {
            flash('error', 'Invalid level');
            return $this->back();
        }

        $targetUser->level = $level;

        if ($targetUser->save()) {
            flash('success', "Changed {$targetUser->username}'s admin level to {$level}");
        } else {
            flash('error', 'Failed to change level');
        }

        return $this->redirect("/admin/users/{$targetUser->id}");
    }
}