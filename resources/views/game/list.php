<?php $this->extends('layouts.game') ?>

<?php $this->section('game_content') ?>
    <div class="user-list">
        <h1>User List</h1>

        <div class="online-stats">
            <p>Online: <?= $onlineUsers['visible'] ?> visible, <?= $onlineUsers['hidden'] ?> hidden (<?= $onlineUsers['total'] ?> total)</p>
            <p>Admins online: <?= $onlineUsers['admins'] ?></p>
        </div>

        <div class="list-controls">
            <form method="GET" action="/game/list">
                <label>Sort by:</label>
                <select name="order" onchange="this.form.submit()">
                    <option value="username" <?= $order === 'username' ? 'selected' : '' ?>>Username</option>
                    <option value="attack_power" <?= $order === 'attack_power' ? 'selected' : '' ?>>Attack Power</option>
                    <option value="type" <?= $order === 'type' ? 'selected' : '' ?>>Type</option>
                    <option value="cash" <?= $order === 'cash' ? 'selected' : '' ?>>Cash</option>
                    <option value="bank" <?= $order === 'bank' ? 'selected' : '' ?>>Bank</option>
                </select>
            </form>
        </div>

        <table class="user-table">
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
            <?php foreach ($users as $listedUser): ?>
                <tr>
                    <td>
                        <a href="/game/profile/<?= $listedUser['id'] ?>">
                            <?= $this->e($listedUser['username']) ?>
                        </a>
                    </td>
                    <td><?= $listedUser['type'] == 1 ? 'Drug Dealer' : ($listedUser['type'] == 2 ? 'Scientist' : 'Police') ?></td>
                    <td><?= number_format($listedUser['attack_power'] + ($listedUser['clicks'] * 5)) ?></td>
                    <td>€<?= number_format($listedUser['cash']) ?></td>
                    <td>€<?= number_format($listedUser['bank']) ?></td>
                    <td>
                        <?php if ($listedUser['id'] !== $user->id): ?>
                            <a href="/game/attack/<?= $listedUser['id'] ?>" class="btn-small">Attack</a>
                            <a href="/game/messages/compose/<?= $listedUser['id'] ?>" class="btn-small">Message</a>
                        <?php endif; ?>
                    </td>
                </tr>
            <?php endforeach; ?>
            </tbody>
        </table>

        <?php if ($pagination['total'] > 1): ?>
            <div class="pagination">
                <?php for ($i = 1; $i <= $pagination['total']; $i++): ?>
                    <a href="?page=<?= $i ?>&order=<?= $order ?>"
                       class="<?= $i === $pagination['current'] ? 'active' : '' ?>">
                        <?= $i ?>
                    </a>
                <?php endfor; ?>
            </div>
        <?php endif; ?>
    </div>
<?php $this->endSection() ?>