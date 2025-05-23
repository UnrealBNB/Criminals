<?php $this->extends('layouts.admin') ?>

<?php $this->section('admin_content') ?>
    <div class="admin-dashboard">
        <h1>Admin Dashboard</h1>

        <div class="stats-grid">
            <div class="stat-box">
                <h3>User Statistics</h3>
                <table>
                    <tr>
                        <td>Total Users:</td>
                        <td><?= number_format($stats['total_users']) ?></td>
                    </tr>
                    <tr>
                        <td>Active Users:</td>
                        <td><?= number_format($stats['active_users']) ?></td>
                    </tr>
                    <tr>
                        <td>Online Now:</td>
                        <td><?= number_format($stats['online_users']) ?></td>
                    </tr>
                </table>
            </div>

            <div class="stat-box">
                <h3>Financial Statistics</h3>
                <table>
                    <tr>
                        <td>Total Cash:</td>
                        <td>€<?= number_format($stats['total_cash']) ?></td>
                    </tr>
                    <tr>
                        <td>Total Bank:</td>
                        <td>€<?= number_format($stats['total_bank']) ?></td>
                    </tr>
                    <tr>
                        <td>Total Economy:</td>
                        <td>€<?= number_format($stats['total_cash'] + $stats['total_bank']) ?></td>
                    </tr>
                </table>
            </div>

            <div class="stat-box">
                <h3>Game Statistics</h3>
                <table>
                    <tr>
                        <td>Total Clans:</td>
                        <td><?= number_format($stats['total_clans']) ?></td>
                    </tr>
                    <?php foreach ($stats['users_by_type'] as $type): ?>
                        <tr>
                            <td><?= ['', 'Drug Dealers', 'Scientists', 'Police'][$type['type']] ?>:</td>
                            <td><?= number_format($type['count']) ?></td>
                        </tr>
                    <?php endforeach; ?>
                </table>
            </div>
        </div>

        <div class="admin-sections">
            <div class="section">
                <h2>Recent Registrations</h2>
                <table class="admin-table">
                    <thead>
                    <tr>
                        <th>Username</th>
                        <th>Type</th>
                        <th>Registered</th>
                    </tr>
                    </thead>
                    <tbody>
                    <?php foreach ($recentUsers as $recentUser): ?>
                        <tr>
                            <td>
                                <a href="/admin/users/<?= $recentUser->id ?>">
                                    <?= $this->e($recentUser->username) ?>
                                </a>
                            </td>
                            <td><?= $this->e($recentUser->getTypeName()) ?></td>
                            <td><?= $recentUser->signup_date->format('Y-m-d H:i') ?></td>
                        </tr>
                    <?php endforeach; ?>
                    </tbody>
                </table>
            </div>

            <div class="section">
                <h2>Top Players</h2>
                <table class="admin-table">
                    <thead>
                    <tr>
                        <th>Username</th>
                        <th>Attack Power</th>
                        <th>Rank</th>
                    </tr>
                    </thead>
                    <tbody>
                    <?php foreach ($topPlayers as $topPlayer): ?>
                        <tr>
                            <td>
                                <a href="/admin/users/<?= $topPlayer->id ?>">
                                    <?= $this->e($topPlayer->username) ?>
                                </a>
                            </td>
                            <td><?= number_format($topPlayer->getTotalAttackPower()) ?></td>
                            <td><?= $this->e($topPlayer->getRank()) ?></td>
                        </tr>
                    <?php endforeach; ?>
                    </tbody>
                </table>
            </div>
        </div>
    </div>
<?php $this->endSection() ?>