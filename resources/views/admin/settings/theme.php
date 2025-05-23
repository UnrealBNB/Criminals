<?php $this->extends('layouts.admin') ?>

<?php $this->section('admin_content') ?>
    <div class="admin-theme">
        <h1>Theme Settings</h1>

        <div class="current-theme">
            <p>Current Theme: <strong><?= $this->e($currentTheme) ?></strong></p>
        </div>

        <form method="POST" action="/admin/settings/theme">
            <?= $this->csrf() ?>

            <div class="theme-options">
                <?php foreach ($themes as $theme): ?>
                    <label class="theme-option">
                        <input type="radio"
                               name="theme"
                               value="<?= $theme ?>"
                            <?= $theme === $currentTheme ? 'checked' : '' ?>>
                        <span><?= ucfirst($theme) ?></span>
                    </label>
                <?php endforeach; ?>
            </div>

            <button type="submit" class="btn btn-primary">Update Theme</button>
        </form>
    </div>
<?php $this->endSection() ?>