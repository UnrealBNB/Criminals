<?php $this->extends('layouts.admin') ?>

<?php $this->section('admin_content') ?>
    <div class="admin-action">
        <h1>Reset User: <?= $this->e($targetUser->username) ?></h1>

        <div class="warning-box">
            <p><strong>Warning!</strong> This will reset the user to starting stats:</p>
            <ul>
                <li>All items will be deleted</li>
                <li>Cash and bank will be set to 0</li>
                <li>Attack and defence power will be reset</li>
                <li>User will be removed from their clan</li>
                <li>Protection will be enabled</li>
            </ul>
        </div>

        <form method="POST" action="/admin/users/<?= $targetUser->id ?>/reset">
            <?= $this->csrf() ?>

            <div class="form-group">
                <label>
                    <input type="checkbox" name="confirm" value="yes" required>
                    I confirm this reset
                </label>
            </div>

            <button type="submit" class="btn btn-warning">Reset User</button>
            <a href="/admin/users/<?= $targetUser->id ?>" class="btn btn-secondary">Cancel</a>
        </form>
    </div>
<?php $this->endSection() ?>