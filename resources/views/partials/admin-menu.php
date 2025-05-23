<div class="admin-menu">
    <h3>Admin Panel</h3>
    <ul>
        <li><a href="/admin">Dashboard</a></li>
        <?php if (auth()->user()->hasAdminLevel(3)): ?>
            <li><a href="/admin/users">Users</a></li>
            <li><a href="/admin/messages">Messages</a></li>
        <?php endif; ?>
        <?php if (auth()->user()->hasAdminLevel(10)): ?>
            <li><a href="/admin/settings">Settings</a></li>
            <li><a href="/admin/settings/theme">Theme</a></li>
            <li><a href="/admin/settings/rules">Rules</a></li>
            <li><a href="/admin/settings/prices">Prices</a></li>
            <li><a href="/admin/settings/game">Game Settings</a></li>
            <li><a href="/admin/settings/maintenance">Maintenance</a></li>
        <?php endif; ?>
    </ul>

    <h3>Quick Links</h3>
    <ul>
        <li><a href="/game">Back to Game</a></li>
    </ul>
</div>