<?php $this->extends('layouts.game') ?>

<?php $this->section('game_content') ?>
    <div class="profile-edit">
        <h1>Edit Profile</h1>

        <form method="POST" action="/game/profile/update">
            <?= $this->csrf() ?>

            <div class="form-group">
                <label for="website">Website:</label>
                <input type="url"
                       id="website"
                       name="website"
                       value="<?= $this->e($user->website) ?>"
                       maxlength="200"
                       placeholder="https://example.com">
                <?php if ($error = $this->error('website')): ?>
                    <span class="error"><?= $this->e($error) ?></span>
                <?php endif; ?>
            </div>

            <div class="form-group">
                <label for="info">About Me:</label>
                <textarea id="info"
                          name="info"
                          rows="5"
                          maxlength="1000"
                          placeholder="Tell others about yourself..."><?= $this->e($user->info) ?></textarea>
                <?php if ($error = $this->error('info')): ?>
                    <span class="error"><?= $this->e($error) ?></span>
                <?php endif; ?>
            </div>

            <h3>Change Password</h3>
            <p class="info">Leave blank to keep current password</p>

            <div class="form-group">
                <label for="password">New Password:</label>
                <input type="password"
                       id="password"
                       name="password"
                       minlength="6">
                <?php if ($error = $this->error('password')): ?>
                    <span class="error"><?= $this->e($error) ?></span>
                <?php endif; ?>
            </div>

            <div class="form-group">
                <label for="password_confirmation">Confirm Password:</label>
                <input type="password"
                       id="password_confirmation"
                       name="password_confirmation">
            </div>

            <button type="submit" class="btn btn-primary">Update Profile</button>
            <a href="/game/profile" class="btn btn-secondary">Cancel</a>
        </form>
    </div>
<?php $this->endSection() ?>