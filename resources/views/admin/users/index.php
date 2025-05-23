<?php $this->extends('layouts.admin') ?>

<?php $this->section('admin_content') ?>
    <div class="admin-users">
        <h1>User Management</h1>

        <div class="search-form">
            <form method="GET" action="/admin/users">
                <input type="text"
                       name="search"
                       value="<?= $this->e($search) ?>"
                       placeholder="Search by username or email...">
                <button type="submit" class="btn btn-primary">Search</button>
                <?php if ($search): ?>
                    <a href="/admin/users" class="btn btn-secondary">Clear</a>
                <?php endif; ?>
            </form>
        </div>

        <table class="admin-table">
            <thead>
            <tr>
                <th>ID</th>
                <th>Username</th>
                <th>Email</th>
                <th>Type</th>
                <th>Level</th>
                <th>Cash</th>
                <th>Bank</th>
                <th>Actions</th>
            </tr>
            </thead>
            <tbody>
            <?php foreach ($users as $userItem): ?>
                <tr>
                    <td><?= $userItem->id ?></td>
                    <td>
                        <a href="/admin/users/<?= $userItem->id ?>">
                            <?= $this->e($userItem->username) ?>
                        </a>
                    </td>
                    <td><?= $this->e($userItem->email) ?></td>
                    <td><?= $this->e($userItem->getTypeName()) ?></td>
                    <td><?= $userItem->level ?></td>
                    <td>€<?= number_format($userItem->cash) ?></td>
                    <td>€<?= number_format($userItem->bank) ?></td>
                    <td>
                        <a href="/admin/users/<?= $userItem->id ?>" class="btn-small">View</a>
                        <a href="/admin/users/<?= $userItem->id ?>/reset" class="btn-small btn-warning">Reset</a>
                        <a href="/admin/users/<?= $userItem->id ?>/delete" class="btn-small btn-danger">Delete</a>
                    </td>
                </tr>
            <?php endforeach; ?>
            </tbody>
        </table>

        <?php if ($pagination['total'] > 1): ?>
            <div class="pagination">
                <?php for ($i = 1; $i <= $pagination['total']; $i++): ?>
                    <a href="?page=<?= $i ?>&search=<?= urlencode($search) ?>"
                       class="<?= $i === $pagination['current'] ? 'active' : '' ?>">
                        <?= $i ?>
                    </a>
                <?php endfor; ?>
            </div>
        <?php endif; ?>
    </div>
<?php $this->endSection() ?>