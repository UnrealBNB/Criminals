<?php $this->extends('layouts.game') ?>

<?php $this->section('game_content') ?>
    <div class="clan-delete">
        <h1>Delete Clan</h1>

        <div class="danger-box">
            <p><strong>⚠️ DANGER ZONE ⚠️</strong></p>
            <p>This action cannot be undone!</p>
            <p>Deleting <?= $this->e($clan->clan_name) ?> will:</p>
            <ul>
                <li>Remove all members from the clan</li>
                <li>Delete all clan items and buildings</li>
                <li>All clan money will be lost</li>
            </ul>
        </div>

        <form method="POST" action="/game/clan/delete">
            <?= $this->csrf() ?>

            <div class="form-group">
                <label>
                    <input type="checkbox" name="confirm" value="yes" required>
                    I understand this action cannot be undone
                </label>
            </div>

            <button type="submit" class="btn btn-danger">Delete Clan Permanently</button>
            <a href="/game/clan/overview" class="btn btn-secondary">Cancel</a>
        </form>
    </div>
<?php $this->endSection() ?>