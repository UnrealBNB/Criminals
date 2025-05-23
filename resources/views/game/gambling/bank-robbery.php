<?php $this->extends('layouts.game') ?>

<?php $this->section('game_content') ?>
    <div class="gambling bank-robbery">
        <h1>Bank Robbery</h1>

        <div class="game-info">
            <p>Attempt to rob a bank! Small chance of success.</p>
            <p>Success: Win €10,000 | Failure: Lose €10,000</p>
            <p>Your Cash: €<?= number_format($user->cash) ?></p>
        </div>

        <?php if ($user->cash < 10000): ?>
            <div class="alert alert-warning">
                You need at least €10,000 to attempt a bank robbery!
            </div>
        <?php else: ?>
            <form method="POST" action="/game/gambling/bank-robbery" onsubmit="return confirm('This is very risky! Are you sure?')">
                <?= $this->csrf() ?>

                <div class="bank-icon">🏦💰</div>

                <button type="submit" class="btn btn-danger btn-large">Rob the Bank</button>
            </form>
        <?php endif; ?>
    </div>
<?php $this->endSection() ?>