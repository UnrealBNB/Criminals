<?php $this->extends('layouts.game') ?>

<?php $this->section('game_content') ?>
    <div class="gambling number-game">
        <h1>Number Game</h1>

        <div class="game-info">
            <p>Choose a number between 1 and 10. If you guess correctly, you win 8x your bet!</p>
            <p>Your Cash: €<?= number_format($user->cash) ?></p>
        </div>

        <form method="POST" action="/game/gambling/number-game">
            <?= $this->csrf() ?>

            <div class="form-group">
                <label>Choose your number:</label>
                <div class="number-grid">
                    <?php for ($i = 1; $i <= 10; $i++): ?>
                        <label class="number-option">
                            <input type="radio" name="number" value="<?= $i ?>" required>
                            <span><?= $i ?></span>
                        </label>
                    <?php endfor; ?>
                </div>
            </div>

            <div class="form-group">
                <label for="amount">Bet Amount:</label>
                <input type="number"
                       id="amount"
                       name="amount"
                       min="1"
                       max="<?= $user->cash ?>"
                       required>
            </div>

            <button type="submit" class="btn btn-primary">Play</button>
        </form>
    </div>
<?php $this->endSection() ?>