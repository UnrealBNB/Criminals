<?php $this->extends('layouts.game') ?>

<?php $this->section('game_content') ?>
    <div class="clan-join">
        <h1>Join a Clan</h1>

        <div class="info-box">
            <p>You can only join clans of your type: <strong><?= $this->e($user->getTypeName()) ?></strong></p>
        </div>

        <form method="POST" action="/game/clan/join">
            <?= $this->csrf() ?>

            <div class="form-group">
                <label for="name">Clan Name:</label>
                <input type="text"
                       id="name"
                       name="name"
                       value="<?= $this->e($this->old('name')) ?>"
                       placeholder="Enter exact clan name"
                       required>
                <?php if ($error = $this->error('name')): ?>
                    <span class="error"><?= $this->e($error) ?></span>
                <?php endif; ?>
            </div>

            <button type="submit" class="btn btn-primary">Apply to Join</button>
            <a href="/game/clan/list" class="btn btn-secondary">Browse Clans</a>
        </form>
    </div>
<?php $this->endSection() ?>