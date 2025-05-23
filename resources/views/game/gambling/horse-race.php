<?php $this->extends('layouts.game') ?>

<?php $this->section('game_content') ?>
    <div class="gambling horse-race">
        <h1>Horse Race</h1>

        <div class="game-info">
            <p>Pick a horse and buy a ticket. Race happens every hour!</p>
            <p>Your Cash: €<?= number_format($user->cash) ?></p>
        </div>

        <?php if ($currentBet): ?>
            <div class="current-bet">
                <h3>Your Current Bet</h3>
                <p>Horse #<?= $currentBet['horse'] ?></p>
                <p>Ticket Type: <?= ['', 'Bronze (€250)', 'Silver (€500)', 'Gold (€1000)'][$currentBet['ticket']] ?></p>
            </div>
        <?php endif; ?>

        <form method="POST" action="/game/gambling/horse-race">
            <?= $this->csrf() ?>

            <div class="form-group">
                <label for="horse">Select Horse (1-50):</label>
                <input type="number"
                       id="horse"
                       name="horse"
                       min="1"
                       max="50"
                       required>
            </div>

            <div class="form-group">
                <label for="ticket">Ticket Type:</label>
                <select id="ticket" name="ticket" required>
                    <option value="">Select ticket</option>
                    <option value="1" <?= $user->cash < 250 ? 'disabled' : '' ?>>Bronze - €250</option>
                    <option value="2" <?= $user->cash < 500 ? 'disabled' : '' ?>>Silver - €500</option>
                    <option value="3" <?= $user->cash < 1000 ? 'disabled' : '' ?>>Gold - €1000</option>
                </select>
            </div>

            <button type="submit" class="btn btn-primary">Place Bet</button>
        </form>
    </div>
<?php $this->endSection() ?>