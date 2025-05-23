<?php

declare(strict_types=1);

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\User;
use Symfony\Component\HttpFoundation\Response;

class AdminDashboardController extends Controller
{
    public function index(): Response
    {
        $user = $this->auth()->user();

        if (!$user->hasAdminLevel(3)) {
            return $this->redirect('/game');
        }

        // Get statistics
        $stats = [
            'total_users' => User::query()->count(),
            'active_users' => User::query()->where('activated', 1)->count(),
            'online_users' => count(User::online()),
            'total_cash' => (int) db()->fetchColumn("SELECT SUM(cash) FROM users"),
            'total_bank' => (int) db()->fetchColumn("SELECT SUM(bank) FROM users"),
            'total_clans' => (int) db()->fetchColumn("SELECT COUNT(*) FROM clans"),
            'users_by_type' => db()->fetchAll(
                "SELECT type, COUNT(*) as count FROM users GROUP BY type"
            ),
        ];

        // Get recent registrations
        $recentUsers = User::query()
            ->orderBy('signup_date', 'DESC')
            ->limit(10)
            ->get();

        // Get top players
        $topPlayers = User::query()
            ->orderBy('attack_power', 'DESC')
            ->limit(10)
            ->get();

        return $this->view('admin.dashboard', [
            'user' => $user,
            'stats' => $stats,
            'recentUsers' => $recentUsers,
            'topPlayers' => $topPlayers,
        ]);
    }
}