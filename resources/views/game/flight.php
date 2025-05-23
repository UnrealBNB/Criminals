<?php $this->extends('layouts.game') ?>

<?php $this->section('game_content') ?>
    <div class="flight">
        <h1>Travel to Another Country</h1>

        <div class="flight-info">
            <p>Current Location: <strong><?= $this->e($currentCountry) ?></strong></p>
            <p>Flight Cost: <strong>€<?= number_format($flightCost) ?></strong></p>
            <p>Your Cash: €<?= number_format($user->cash) ?></p>
        </div>

        <form method="POST" action="/game/flight">
            <?= $this->csrf() ?>

            <div class="country-grid">
                <?php foreach ($countries as $id => $name): ?>
                    <?php if ($id !== $user->country_id): ?>
                        <label class="country-option">
                            <input type="radio" name="country" value="<?= $id ?>" required>
                            <span>🛫 <?= $this->e($name) ?></span>
                        </label>
                    <?php endif; ?>
                <?php endforeach; ?>
            </div>

            <?php if ($user->cash >= $flightCost): ?>
                <button type="submit" class="btn btn-primary">Buy Ticket & Fly</button>
            <?php else: ?>
                <div class="alert alert-warning">
                    You need at least €<?= number_format($flightCost) ?> to travel.
                </div>
            <?php endif; ?>
        </form>
    </div>
<?php $this->endSection() ?>