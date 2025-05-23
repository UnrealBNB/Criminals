<?php $this->extends('layouts.game') ?>

<?php $this->section('game_content') ?>
    <div class="gambling roulette">
        <h1>Roulette</h1>

        <div class="game-info">
            <p>Bet on a number (0-36). Win 36x your bet!</p>
            <p>Your Cash: €<?= number_format($user->cash) ?></p>
        </div>

        <form method="POST" action="/game/gambling/roulette">
            <?= $this->csrf() ?>

            <div class="roulette-board">
                <div class="number-zero">
                    <label>
                        <input type="radio" name="number" value="0" required>
                        <span>0</span>
                    </label>
                </div>

                <div class="number-grid">
                    <?php for ($i = 1; $i <= 36; $i++): ?>
                        <?php
                        $isRed = in_array($i, [1,3,5,7,9,12,14,16,18,19,21,23,25,27,30,32,34,36]);
                        $colorClass = $isRed ? 'red' : 'black';
                        ?>
                        <label class="number-option <?= $colorClass ?>">
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

            <!-- Hidden color field for future color betting implementation -->
            <input type="hidden" name="color" value="0">

            <button type="submit" class="btn btn-primary">Spin the Wheel</button>
        </form>
    </div>
<?php $this->endSection() ?>