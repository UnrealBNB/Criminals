<?php $this->extends('layouts.game') ?>

<?php $this->section('game_content') ?>
    <div class="gambling russian-roulette">
        <h1>Russian Roulette</h1>

        <div class="game-info">
            <p class="warning">⚠️ Dangerous Game!</p>
            <p>50/50 chance: Win €500 or lose €500</p>
            <p>Your Cash: €<?= number_format($user->cash) ?></p>
        </div>

        <?php if ($user->cash < 500): ?>
            <div class="alert alert-warning">
                You need at least €500 to play this game.
            </div>
        <?php else: ?>
            <form method="POST" action="/game/gambling/russian-roulette" onsubmit="return confirm('Are you sure? This is risky!')">
                <?= $this->csrf() ?>

                <div class="roulette-chamber">
                    <div class="gun-icon">🔫</div>
                </div>

                <button type="submit" class="btn btn-danger">Pull the Trigger</button>
            </form>
        <?php endif; ?>
    </div>
<?php $this->endSection() ?>