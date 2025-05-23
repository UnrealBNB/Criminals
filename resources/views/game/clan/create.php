<?php $this->extends('layouts.game') ?>

<?php $this->section('game_content') ?>
    <div class="clan-create">
        <h1>Create a Clan</h1>

        <form method="POST" action="/game/clan/create">
            <?= $this->csrf() ?>

            <div class="form-group">
                <label for="name">Clan Name:</label>
                <input type="text"
                       id="name"
                       name="name"
                       value="<?= $this->e($this->old('name')) ?>"
                       minlength="3"
                       maxlength="200"
                       pattern="[A-Za-z0-9_\- ]+"
                       title="Only letters, numbers, spaces, hyphens and underscores allowed"
                       required>
                <?php if ($error = $this->error('name')): ?>
                    <span class="error"><?= $this->e($error) ?></span>
                <?php endif; ?>
            </div>

            <div class="clan-type-info">
                <p><strong>Clan Type:</strong> <?= $this->e($user->getTypeName()) ?></p>
                <p class="info">Your clan will be the same type as you are.</p>
            </div>

            <button type="submit" class="btn btn-primary">Create Clan</button>
            <a href="/game/clan" class="btn btn-secondary">Cancel</a>
        </form>
    </div>
<?php $this->endSection() ?>