<?php

declare(strict_types=1);

namespace App\Http\Controllers\Game;

use App\Http\Controllers\Controller;
use App\Models\User;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;

class UserListController extends Controller
{
    public function index(Request $request): Response
    {
        $user = $this->auth()->user();
        $orderBy = $request->query->get('order', 'username');
        $page = max(1, (int) $request->query->get('page', 1));
        $perPage = 50;

        // Validate order
        $validOrders = ['username', 'attack_power', 'type', 'cash', 'bank'];
        if (!in_array($orderBy, $validOrders)) {
            $orderBy = 'username';
        }

        // Get total count
        $totalUsers = (int) db()->fetchColumn(
            "SELECT COUNT(*) FROM users WHERE activated = 1 AND showonline = 1"
        );

        $totalPages = ceil($totalUsers / $perPage);
        $offset = ($page - 1) * $perPage;

        // Get users
        $users = db()->fetchAll(
            "SELECT id, username, attack_power, type, cash, bank, clicks 
             FROM users 
             WHERE activated = 1 AND showonline = 1 
             ORDER BY {$orderBy} 
             LIMIT :limit OFFSET :offset",
            ['limit' => $perPage, 'offset' => $offset]
        );

        // Get online stats
        $onlineUsers = User::online();
        $onlineCount = count($onlineUsers);
        $visibleCount = count(array_filter($onlineUsers, fn($u) => $u->showonline));
        $hiddenCount = $onlineCount - $visibleCount;
        $adminCount = count(array_filter($onlineUsers, fn($u) => $u->isAdmin()));

        return $this->view('game.list', [
            'users' => $users,
            'user' => $user,
            'order' => $orderBy,
            'pagination' => [
                'current' => $page,
                'total' => $totalPages,
                'perPage' => $perPage,
            ],
            'onlineUsers' => [
                'total' => $onlineCount,
                'visible' => $visibleCount,
                'hidden' => $hiddenCount,
                'admins' => $adminCount,
            ],
        ]);
    }

    public function toggleOnlineStatus(): Response
    {
        $user = $this->auth()->user();
        $user->showonline = !$user->showonline;
        $user->save();

        flash('success', 'Online status updated!');
        return $this->back();
    }
}