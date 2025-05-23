<?php $this->extends('layouts.game') ?>

<?php $this->section('game_content') ?>
    <div class="dashboard">
        <h1>Welcome, <?= $this->e($user->username) ?>!</h1>

        <div class="dashboard-grid">
            <div class="stat-box">
                <h3>Online Users</h3>
                <p>Total: <?= $onlineUsers['total'] ?></p>
                <p>Visible: <?= $onlineUsers['visible'] ?></p>
                <p>Hidden: <?= $onlineUsers['hidden'] ?></p>
                <p>Admins: <?= $onlineUsers['admins'] ?></p>
            </div>

            <div class="stat-box">
                <h3>Your Progress</h3>
                <p>Rank: <?= $this->e($user->getRank()) ?></p>
                <p>Clicks Today: <?= $clicksToday ?>/<?= config('game.max_clicks_per_day', 50) ?></p>
                <p>Attacks Won: <?= $user->attacks_won ?></p>
                <p>Attacks Lost: <?= $user->attacks_lost ?></p>
            </div>

            <div class="stat-box">
                <h3>Quick Actions</h3>
                <?php if ($user->isProtected()): ?>
                    <form method="POST" action="/game/remove-protection">
                        <?= $this->csrf() ?>
                        <button type="submit" class="btn btn-warning">Remove Protection</button>
                    </form>
                <?php endif; ?>

                <form method="POST" action="/game/toggle-online">
                    <?= $this->csrf() ?>
                    <button type="submit" class="btn btn-secondary">
                        <?= $user->showonline ? 'Hide Online Status' : 'Show Online Status' ?>
                    </button>
                </form>
            </div>

            <div class="stat-box">
                <h3>Messages</h3>
                <p>Unread: <?= $messageCount ?></p>
                <a href="/game/messages" class="btn btn-primary">View Messages</a>
                <a href="/game/messages/compose" class="btn btn-secondary">Compose</a>
            </div>
        </div>

        <div class="news-section">
            <h2>Latest News</h2>
            <p>Welcome to Criminals! Check the rules and start building your criminal empire.</p>
        </div>
    </div>
<?php $this->endSection() ?>