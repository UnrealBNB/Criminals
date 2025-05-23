<?php $this->extends('layouts.admin') ?>

<?php $this->section('admin_content') ?>
    <div class="admin-maintenance">
        <h1>Maintenance Mode</h1>

        <div class="maintenance-status">
            <?php if ($isInMaintenance): ?>
                <div class="status-active">
                    <h2>⚠️ Maintenance Mode is ACTIVE</h2>
                    <p>The game is currently unavailable to regular users.</p>
                </div>
            <?php else: ?>
                <div class="status-inactive">
                    <h2>✅ Game is Online</h2>
                    <p>All users can access the game normally.</p>
                </div>
            <?php endif; ?>
        </div>

        <form method="POST" action="/admin/settings/maintenance">
            <?= $this->csrf() ?>

            <?php if ($isInMaintenance): ?>
                <input type="hidden" name="action" value="disable">
                <button type="submit" class="btn btn-success">Disable Maintenance Mode</button>
            <?php else: ?>
                <input type="hidden" name="action" value="enable">
                <button type="submit" class="btn btn-danger" onclick="return confirm('This will prevent all users from accessing the game. Continue?')">
                    Enable Maintenance Mode
                </button>
            <?php endif; ?>
        </form>
    </div>
<?php $this->endSection() ?>