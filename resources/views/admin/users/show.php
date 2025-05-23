<?php $this->extends('layouts.admin') ?>

<?php $this->section('admin_content') ?>
    <div class="admin-user-detail">
        <h1>User: <?= $this->e($targetUser->username) ?></h1>

        <div class="user-sections">
            <div class="section">
                <h2>User Information</h2>
                <table>
                    <tr>
                        <td>ID:</td>
                        <td><?= $targetUser->id ?></td>
                    </tr>
                    <tr>
                        <td>Email:</td>
                        <td><?= $this->e($targetUser->email) ?></td>
                    </tr>
                    <tr>
                        <td>Type:</td>
                        <td><?= $this->e($targetUser->getTypeName()) ?></td>
                    </tr>
                    <tr>
                        <td>Admin Level:</td>
                        <td><?= $targetUser->level ?></td>
                    </tr>
                    <tr>
                        <td>Registered:</td>
                        <td><?= $targetUser->signup_date->format('Y-m-d H:i:s') ?></td>
                    </tr>
                    <tr>
                        <td>Last Online:</td>
                        <td><?= $targetUser->online_time ? $targetUser->online_time->format('Y-m-d H:i:s') : 'Never' ?></td>
                    </tr>
                </table>
            </div>

            <div class="section">
                <h2>Game Statistics</h2>
                <table>
                    <tr>
                        <td>Cash:</td>
                        <td>€<?= number_format($targetUser->cash) ?></td>
                    </tr>
                    <tr>
                        <td>Bank:</td>
                        <td>€<?= number_format($targetUser->bank) ?></td>
                    </tr>
                    <tr>
                        <td>Attack Power:</td>
                        <td><?= number_format($targetUser->getTotalAttackPower()) ?></td>
                    </tr>
                    <tr>
                        <td>Defence Power:</td>
                        <td><?= number_format($targetUser->getTotalDefencePower()) ?></td>
                    </tr>
                    <tr>
                        <td>Clicks:</td>
                        <td><?= number_format($targetUser->clicks) ?></td>
                    </tr>
                    <tr>
                        <td>Attacks Won:</td>
                        <td><?= number_format($targetUser->attacks_won) ?></td>
                    </tr>
                    <tr>
                        <td>Attacks Lost:</td>
                        <td><?= number_format($targetUser->attacks_lost) ?></td>
                    </tr>
                </table>
            </div>

            <div class="section">
                <h2>Admin Actions</h2>
                <div class="admin-actions">
                    <a href="/admin/users/<?= $targetUser->id ?>/donate" class="btn btn-success">Donate Money</a>
                    <a href="/admin/users/<?= $targetUser->id ?>/reset" class="btn btn-warning">Reset User</a>
                    <?php if ($user->hasAdminLevel(10)): ?>
                        <a href="/admin/users/<?= $targetUser->id ?>/level" class="btn btn-info">Change Admin Level</a>
                    <?php endif; ?>
                    <a href="/admin/users/<?= $targetUser->id ?>/delete" class="btn btn-danger">Delete User</a>
                </div>
            </div>

            <?php if (!empty($items)): ?>
                <div class="section">
                    <h2>User Items</h2>
                    <table class="admin-table">
                        <thead>
                        <tr>
                            <th>Item</th>
                            <th>Attack</th>
                            <th>Defence</th>
                            <th>Quantity</th>
                        </tr>
                        </thead>
                        <tbody>
                        <?php foreach ($items as $item): ?>
                            <tr>
                                <td><?= $this->e($item->item_name) ?></td>
                                <td><?= $item->item_attack_power ?></td>
                                <td><?= $item->item_defence_power ?></td>
                                <td><?= $item->item_count ?></td>
                            </tr>
                        <?php endforeach; ?>
                        </tbody>
                    </table>
                </div>
            <?php endif; ?>
        </div>
    </div>
<?php $this->endSection() ?>