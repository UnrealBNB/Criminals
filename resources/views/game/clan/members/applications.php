<?php $this->extends('layouts.game') ?>

<?php $this->section('game_content') ?>
    <div class="clan-applications">
        <h1>Clan Applications</h1>

        <?php if (empty($applications)): ?>
            <p>No pending applications.</p>
        <?php else: ?>
            <table class="applications-table">
                <thead>
                <tr>
                    <th>Username</th>
                    <th>Type</th>
                    <th>Attack Power</th>
                    <th>Cash</th>
                    <th>Bank</th>
                    <th>Actions</th>
                </tr>
                </thead>
                <tbody>
                <?php foreach ($applications as $applicant): ?>
                    <tr>
                        <td>
                            <a href="/game/profile/<?= $applicant->id ?>">
                                <?= $this->e($applicant->username) ?>
                            </a>
                        </td>
                        <td><?= $this->e($applicant->getTypeName()) ?></td>
                        <td><?= number_format($applicant->getTotalAttackPower()) ?></td>
                        <td>€<?= number_format($applicant->cash) ?></td>
                        <td>€<?= number_format($applicant->bank) ?></td>
                        <td>
                            <form method="POST" action="/game/clan/members/accept/<?= $applicant->id ?>" style="display: inline;">
                                <?= $this->csrf() ?>
                                <button type="submit" class="btn-small btn-success">Accept</button>
                            </form>
                            <form method="POST" action="/game/clan/members/reject/<?= $applicant->id ?>" style="display: inline;">
                                <?= $this->csrf() ?>
                                <button type="submit" class="btn-small btn-danger">Reject</button>
                            </form>
                        </td>
                    </tr>
                <?php endforeach; ?>
                </tbody>
            </table>
        <?php endif; ?>
    </div>
<?php $this->endSection() ?>