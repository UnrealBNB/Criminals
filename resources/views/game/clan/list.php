<?php $this->extends('layouts.game') ?>

<?php $this->section('game_content') ?>
    <div class="clan-list">
        <h1>All Clans</h1>

        <table class="clans-table">
            <thead>
            <tr>
                <th>Name</th>
                <th>Type</th>
                <th>Owner</th>
                <th>Members</th>
                <th>Attack Power</th>
                <th>Defence Power</th>
            </tr>
            </thead>
            <tbody>
            <?php foreach ($clans as $clan): ?>
                <tr>
                    <td><?= $this->e($clan['clan_name']) ?></td>
                    <td><?= ['', 'Drug Dealer', 'Scientist', 'Police'][$clan['clan_type']] ?></td>
                    <td><?= $this->e($clan['owner_name']) ?></td>
                    <td><?= $clan['member_count'] ?></td>
                    <td><?= number_format($clan['attack_power']) ?></td>
                    <td><?= number_format($clan['defence_power']) ?></td>
                </tr>
            <?php endforeach; ?>
            </tbody>
        </table>
    </div>
<?php $this->endSection() ?>