<?php $this->extends('layouts.game') ?>

<?php $this->section('game_content') ?>
    <div class="gambling coin-flip">
        <h1>Coin Flip</h1>

        <div class="game-info">
            <p>Choose heads or tails. Win 1.5x your bet!</p>
            <p>Your Cash: €<?= number_format($user->cash) ?></p>
        </div>

        <form method="POST" action="/game/gambling/coin-flip">
            <?= $this->csrf() ?>

            <div class="form-group">
                <label>Choose:</label>
                <div class="coin-options">
                    <label class="coin-option">
                        <input type="radio" name="choice" value="0" required>
                        <span>Heads</span>
                    </label>
                    <label class="coin-option">
                        <input type="radio" name="choice" value="1" required>
                        <span>Tails</span>
                    </label>
                </div>
            </div>

            <div class="form-group">
                <label>Bet Amount:</label>
                <select name="amount" required>
                    <option value="">Select amount</option>
                    <option value="250" <?= $user->cash < 250 ? 'disabled' : '' ?>>€250</option>
                    <option value="500" <?= $user->cash < 500 ? 'disabled' : '' ?>>€500</option>
                    <option value="1000" <?= $user->cash < 1000 ? 'disabled' : '' ?>>€1000</option>
                </select>
            </div>

            <button type="submit" class="btn btn-primary">Flip Coin</button>
        </form>
    </div>
<?php $this->endSection() ?>