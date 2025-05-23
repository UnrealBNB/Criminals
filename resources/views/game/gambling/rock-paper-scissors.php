<?php $this->extends('layouts.game') ?>

<?php $this->section('game_content') ?>
    <div class="gambling rps">
        <h1>Rock Paper Scissors</h1>

        <div class="game-info">
            <p>Play against the computer. Win €500, lose €500, or draw!</p>
            <p>Your Cash: €<?= number_format($user->cash) ?></p>
        </div>

        <?php if ($user->cash < 500): ?>
            <div class="alert alert-warning">
                You need at least €500 to play this game.
            </div>
        <?php else: ?>
            <form method="POST" action="/game/gambling/rock-paper-scissors">
                <?= $this->csrf() ?>

                <div class="rps-options">
                    <label class="rps-option">
                        <input type="radio" name="choice" value="1" required>
                        <span>🪨 Rock</span>
                    </label>
                    <label class="rps-option">
                        <input type="radio" name="choice" value="2" required>
                        <span>📄 Paper</span>
                    </label>
                    <label class="rps-option">
                        <input type="radio" name="choice" value="3" required>
                        <span>✂️ Scissors</span>
                    </label>
                </div>

                <button type="submit" class="btn btn-primary">Play</button>
            </form>
        <?php endif; ?>
    </div>
<?php $this->endSection() ?>