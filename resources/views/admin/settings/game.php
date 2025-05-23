<?php $this->extends('layouts.admin') ?>

<?php $this->section('admin_content') ?>
    <div class="admin-game-settings">
        <h1>Game Configuration</h1>

        <form method="POST" action="/admin/settings/game">
            <?= $this->csrf() ?>

            <div class="settings-section">
                <h3>Daily Limits</h3>

                <div class="form-group">
                    <label for="max_clicks_per_day">Max Clicks Per Day:</label>
                    <input type="number"
                           id="max_clicks_per_day"
                           name="max_clicks_per_day"
                           value="<?= $settings['max_clicks_per_day'] ?>"
                           min="1"
                           max="100">
                </div>

                <div class="form-group">
                    <label for="bank_deposits_per_day">Bank Transactions Per Day:</label>
                    <input type="number"
                           id="bank_deposits_per_day"
                           name="bank_deposits_per_day"
                           value="<?= $settings['bank_deposits_per_day'] ?>"
                           min="1"
                           max="20">
                </div>
            </div>

            <div class="settings-section">
                <h3>Protection Settings</h3>

                <div class="form-group">
                    <label for="protection_hours">New Player Protection (hours):</label>
                    <input type="number"
                           id="protection_hours"
                           name="protection_hours"
                           value="<?= $settings['protection_hours'] ?>"
                           min="1"
                           max="72">
                </div>
            </div>

            <div class="settings-section">
                <h3>Attack Settings</h3>

                <div class="form-group">
                    <label for="max_attacks_per_target">Max Attacks Per Target Per Day:</label>
                    <input type="number"
                           id="max_attacks_per_target"
                           name="max_attacks_per_target"
                           value="<?= $settings['max_attacks_per_target'] ?>"
                           min="1"
                           max="20">
                </div>

                <div class="form-group">
                    <label for="attack_cooldown_seconds">Attack Cooldown (seconds):</label>
                    <input type="number"
                           id="attack_cooldown_seconds"
                           name="attack_cooldown_seconds"
                           value="<?= $settings['attack_cooldown_seconds'] ?>"
                           min="0"
                           max="300">
                </div>
            </div>

            <button type="submit" class="btn btn-primary">Update Settings</button>
            <a href="/admin/settings" class="btn btn-secondary">Cancel</a>
        </form>
    </div>
<?php $this->endSection() ?>