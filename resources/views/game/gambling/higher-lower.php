<?php $this->extends('layouts.game') ?>

<?php $this->section('game_content') ?>
    <div class="gambling higher-lower">
        <h1>Higher or Lower</h1>

        <div class="game-info">
            <p>Round <?= $round ?></p>
            <p>Cost: €<?= number_format($costMoney) ?> | Win: €<?= number_format($winMoney) ?></p>
            <p>Your Cash: €<?= number_format($user->cash) ?></p>
        </div>

        <div class="current-number">
            <h2><?= $number ?></h2>
        </div>

        <?php if ($user->cash < $costMoney): ?>
            <div class="alert alert-warning">
                You don't have enough cash for this round!
            </div>
        <?php else: ?>
            <form method="POST" action="/game/gambling/higher-lower">
                <?= $this->csrf() ?>
                <input type="hidden" name="number" value="<?= $number ?>">

                <div class="hl-options">
                    <button type="submit" name="guess" value="1" class="btn btn-success">Higher</button>
                    <button type="submit" name="guess" value="2" class="btn btn-danger">Lower</button>
                </div>
            </form>
        <?php endif; ?>
    </div>
<?php $this->endSection() ?>