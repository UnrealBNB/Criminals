<?php $this->extends('layouts.game') ?>

<?php $this->section('game_content') ?>
    <div class="clan-overview">
        <h1><?= $this->e($clan->clan_name) ?></h1>

        <div class="clan-stats">
            <div class="stat-box">
                <h3>Clan Information</h3>
                <table>
                    <tr>
                        <td>Owner:</td>
                        <td><?= $this->e($clan->owner()->username) ?></td>
                    </tr>
                    <tr>
                        <td>Type:</td>
                        <td><?= ['', 'Drug Dealer', 'Scientist', 'Police'][$clan->clan_type] ?></td>
                    </tr>
                    <tr>
                        <td>Members:</td>
                        <td><?= count($members) ?></td>
                    </tr>
                    <tr>
                        <td>Total Power:</td>
                        <td><?= number_format($totalPower) ?></td>
                    </tr>
                </table>
            </div>

            <div class="stat-box">
                <h3>Clan Finances</h3>
                <table>
                    <tr>
                        <td>Cash:</td>
                        <td>€<?= number_format($clan->cash) ?></td>
                    </tr>
                    <tr>
                        <td>Bank:</td>
                        <td>€<?= number_format($clan->bank) ?></td>
                    </tr>
                    <tr>
                        <td>Bank Left:</td>
                        <td><?= $clan->bankleft ?></td>
                    </tr>
                </table>
            </div>

            <div class="stat-box">
                <h3>Your Clan Role</h3>
                <p>Level: <?= $user->clan_level ?></p>
                <p>Role: <?= match($user->clan_level) {
                        10 => 'Owner',
                        9 => 'Co-Owner',
                        8 => 'Admin',
                        7 => 'Moderator',
                        6 => 'Banker',
                        5 => 'Recruiter',
                        default => 'Member'
                    } ?></p>
            </div>
        </div>

        <div class="clan-actions">
            <?php if ($user->isClanOwner()): ?>
                <a href="/game/clan/members/change-owner" class="btn btn-warning">Transfer Ownership</a>
                <a href="/game/clan/delete" class="btn btn-danger">Delete Clan</a>
            <?php elseif (!$user->isClanOwner()): ?>
                <a href="/game/clan/leave" class="btn btn-warning">Leave Clan</a>
            <?php endif; ?>
        </div>
    </div>
<?php $this->endSection() ?>