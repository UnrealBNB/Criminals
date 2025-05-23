<?php $this->extends('layouts.game') ?>

<?php $this->section('game_content') ?>
    <div class="type-change">
        <h1>Change Your Type</h1>

        <div class="current-type">
            <p>Current Type: <strong><?= $this->e($user->getTypeName()) ?></strong></p>
        </div>

        <div class="type-options">
            <?php foreach ($types as $type): ?>
                <div class="type-card <?= $user->type == $type['value'] ? 'current' : '' ?>">
                    <h3><?= $this->e($type['label']) ?></h3>

                    <?php if ($user->type != $type['value']): ?>
                        <form method="POST" action="/game/type-change">
                            <?= $this->csrf() ?>
                            <input type="hidden" name="type" value="<?= $type['value'] ?>">
                            <button type="submit" class="btn btn-primary">Change to <?= $this->e($type['label']) ?></button>
                        </form>
                    <?php else: ?>
                        <p class="current-badge">Current Type</p>
                    <?php endif; ?>
                </div>
            <?php endforeach; ?>
        </div>

        <div class="warning-box">
            <p><strong>Warning:</strong> Changing your type will affect which items you can buy and which clans you can join!</p>
        </div>
    </div>
<?php $this->endSection() ?>