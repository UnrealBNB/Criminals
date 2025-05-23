<?php $this->extends('layouts.admin') ?>

<?php $this->section('admin_content') ?>
    <div class="admin-action">
        <h1>Delete User: <?= $this->e($targetUser->username) ?></h1>

        <div class="danger-box">
            <p><strong>⚠️ DANGER!</strong> This action cannot be undone!</p>
            <p>This will permanently delete the user and all associated data:</p>
            <ul>
                <li>User account and profile</li>
                <li>All items and stats</li>
                <li>All messages sent and received</li>
                <li>Clan membership</li>
            </ul>
        </div>

        <form method="POST" action="/admin/users/<?= $targetUser->id ?>/delete">
            <?= $this->csrf() ?>

            <div class="form-group">
                <label>
                    <input type="checkbox" name="confirm" value="yes" required>
                    I understand this action is permanent
                </label>
            </div>

            <button type="submit" class="btn btn-danger">Delete User Permanently</button>
            <a href="/admin/users/<?= $targetUser->id ?>" class="btn btn-secondary">Cancel</a>
        </form>
    </div>
<?php $this->endSection() ?>