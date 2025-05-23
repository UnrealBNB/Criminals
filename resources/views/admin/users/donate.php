<?php $this->extends('layouts.admin') ?>

<?php $this->section('admin_content') ?>
    <div class="admin-action">
        <h1>Donate to: <?= $this->e($targetUser->username) ?></h1>

        <div class="user-finances">
            <p>Current Bank: €<?= number_format($targetUser->bank) ?></p>
            <p>Current Cash: €<?= number_format($targetUser->cash) ?></p>
        </div>

        <form method="POST" action="/admin/users/<?= $targetUser->id ?>/donate">
            <?= $this->csrf() ?>

            <div class="form-group">
                <label for="amount">Amount to add to bank:</label>
                <input type="number"
                       id="amount"
                       name="amount"
                       min="1"
                       required>
            </div>

            <button type="submit" class="btn btn-success">Donate</button>
            <a href="/admin/users/<?= $targetUser->id ?>" class="btn btn-secondary">Cancel</a>
        </form>
    </div>
<?php $this->endSection() ?>