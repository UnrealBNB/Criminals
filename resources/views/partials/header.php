<header class="header">
    <div class="header-content">
        <div class="logo">
            <a href="/"><?= $this->e($app_name) ?></a>
        </div>

        <?php if (auth()->check()): ?>
            <nav class="main-nav">
                <a href="/game">Game</a>
                <a href="/game/messages">Messages
                    <?php if ($unread = auth()->user()->unreadMessageCount()): ?>
                        <span class="badge"><?= $unread ?></span>
                    <?php endif; ?>
                </a>
                <a href="/game/profile">Profile</a>
                <?php if (auth()->user()->isAdmin()): ?>
                    <a href="/admin">Admin</a>
                <?php endif; ?>
            </nav>

            <div class="user-info">
                <span><?= $this->e(auth()->user()->username) ?></span>
                <form method="POST" action="/logout" style="display: inline;">
                    <?= $this->csrf() ?>
                    <button type="submit" class="btn-link">Logout</button>
                </form>
            </div>
        <?php else: ?>
            <nav class="main-nav">
                <a href="/login">Login</a>
                <a href="/register">Register</a>
            </nav>
        <?php endif; ?>
    </div>
</header>