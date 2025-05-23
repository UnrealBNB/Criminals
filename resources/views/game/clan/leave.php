<?php $this->extends('layouts.game') ?>

<?php $this->section('game_content') ?>
    <div class="clan-leave">
        <h1>Leave Clan</h1>

        <div class="warning-box">
            <p><strong>Warning!</strong></p>
            <p>Are you sure you want to leave <?= $this->e($clan->clan_name) ?>?</p>
            <p>You will lose all clan privileges and need to reapply to join again.</p>
        </div>

        <form method="POST" action="/game/clan/leave">
            <?= $this->csrf() ?>

            <div class="form-group">
                <label>
                    <input type="checkbox" name="confirm" value="yes" required>
                    Yes, I want to leave this clan
                </label>
            </div>

            <button type="submit" class="btn btn-danger">Leave Clan</button>
            <a href="/game/clan/overview" class="btn btn-secondary">Cancel</a>
        </form>
    </div>
<?php $this->endSection() ?>