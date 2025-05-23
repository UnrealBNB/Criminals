<?php $this->extends('layouts.game') ?>

<?php $this->section('game_content') ?>
    <div class="attack-result">
        <h1>Attack Result</h1>

        <div class="result-box <?= $result['success'] ? 'success' : 'failure' ?>">
            <?php if ($result['success']): ?>
                <h2>Victory! ⚔️</h2>
                <p>You defeated <?= $this->e($defender->username) ?>!</p>
                <p class="money-won">You stole €<?= number_format($result['money_taken']) ?></p>
            <?php else: ?>
                <h2>Defeat! 💀</h2>
                <p><?= $this->e($defender->username) ?> was too strong!</p>
                <p class="money-lost">You lost €<?= number_format($result['money_lost']) ?></p>
            <?php endif; ?>
        </div>

        <div class="combat-stats">
            <div class="stat-comparison">
                <div class="your-stats">
                    <h3>Your Attack Power</h3>
                    <p><?= number_format($result['attack_power']) ?></p>
                </div>
                <div class="vs">VS</div>
                <div class="their-stats">
                    <h3>Their Defense Power</h3>
                    <p><?= number_format($result['defense_power']) ?></p>
                </div>
            </div>
        </div>

        <div class="actions">
            <a href="/game/list" class="btn btn-primary">Back to List</a>
            <a href="/game/messages/compose/<?= $defender->id ?>" class="btn btn-secondary">Send Message</a>
        </div>
    </div>
<?php $this->endSection() ?>