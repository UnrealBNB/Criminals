<?php $this->extends('layouts.game') ?>

<?php $this->section('game_content') ?>
    <div class="clan-donate">
        <h1>Donate to Clan</h1>

        <div class="donate-info">
            <p>Support your clan by donating cash!</p>
            <p>Your Cash: €<?= number_format($user->cash) ?></p>
            <p>Clan Cash: €<?= number_format($clan->cash) ?></p>
        </div>

        <form method="POST" action="/game/clan/bank/donate">
            <?= $this->csrf() ?>

            <div class="form-group">
                <label for="amount">Donation Amount:</label>
                <input type="number"
                       id="amount"
                       name="amount"
                       min="1"
                       max="<?= $user->cash ?>"
                       required>
            </div>

            <button type="submit" class="btn btn-primary">Donate</button>
            <a href="/game/clan/overview" class="btn btn-secondary">Back</a>
        </form>
    </div>
<?php $this->endSection() ?>