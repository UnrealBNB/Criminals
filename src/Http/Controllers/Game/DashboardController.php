<?php

declare(strict_types=1);

namespace App\Http\Controllers\Game;

use App\Http\Controllers\Controller;
use App\Models\User;
use Symfony\Component\HttpFoundation\Response;

class DashboardController extends Controller
{
    public function index(): Response
    {
        $user = $this->auth()->user();

        if (!$user) {
            return $this->redirect('/login');
        }

        $onlineUsers = User::online();
        $onlineCount = count($onlineUsers);
        $visibleCount = count(array_filter($onlineUsers, fn($u) => $u->showonline));
        $hiddenCount = $onlineCount - $visibleCount;
        $adminCount = count(array_filter($onlineUsers, fn($u) => $u->isAdmin()));

        $clicksToday = db()->fetchColumn(
            "SELECT COUNT(*) FROM clicks WHERE userid = :user_id",
            ['user_id' => $user->id]
        ) ?? 0;

        return $this->view('game.dashboard', [
            'user' => $user,
            'onlineUsers' => [
                'total' => $onlineCount,
                'visible' => $visibleCount,
                'hidden' => $hiddenCount,
                'admins' => $adminCount,
            ],
            'clicksToday' => $clicksToday,
            'messageCount' => $user->unreadMessageCount(),
        ]);
    }

    public function removeProtection(): Response
    {
        $user = $this->auth()->user();

        if ($user && $user->isProtected()) {
            $user->protection = 0;
            $user->save();

            flash('success', 'Your protection has been removed!');
        }

        return $this->back();
    }

    public function toggleOnlineStatus(): Response
    {
        $user = $this->auth()->user();

        if ($user) {
            $user->showonline = !$user->showonline;
            $user->save();

            flash('success', 'Online status updated!');
        }

        return $this->back();
    }
}