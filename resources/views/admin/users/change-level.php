<?php $this->extends('layouts.admin') ?>

<?php $this->section('admin_content') ?>
    <div class="admin-action">
        <h1>Change Admin Level: <?= $this->e($targetUser->username) ?></h1>

        <div class="info-box">
            <p>Current Level: <?= $targetUser->level ?></p>
            <h3>Admin Levels:</h3>
            <ul>
                <li>0 - Regular User</li>
                <li>1-2 - Junior Moderator</li>
                <li>3-5 - Moderator</li>
                <li>6-9 - Senior Moderator</li>
                <li>10 - Administrator</li>
            </ul>
        </div>

        <form method="POST" action="/admin/users/<?= $targetUser->id ?>/level">
            <?= $this->csrf() ?>

            <div class="form-group">
                <label for="level">New Level:</label>
                <select name="level" id="level" required>
                    <?php for ($i = 0; $i <= 10; $i++): ?>
                        <option value="<?= $i ?>" <?= $i === $targetUser->level ? 'selected' : '' ?>>
                            Level <?= $i ?>
                        </option>
                    <?php endfor; ?>
                </select>
            </div>

            <button type="submit" class="btn btn-info">Change Level</button>
            <a href="/admin/users/<?= $targetUser->id ?>" class="btn btn-secondary">Cancel</a>
        </form>
    </div>
<?php $this->endSection() ?>