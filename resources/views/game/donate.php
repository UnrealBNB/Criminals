<?php $this->extends('layouts.game') ?>

<?php $this->section('game_content') ?>
    <div class="donate">
        <h1>Donate Money</h1>

        <div class="donate-info">
            <p>Send money to other players.</p>
            <p>Your Cash: €<?= number_format($user->cash) ?></p>

            <?php if ($user->isProtected()): ?>
                <div class="alert alert-warning">
                    You cannot donate while under protection.
                </div>
            <?php endif; ?>
        </div>

        <?php if (!$user->isProtected()): ?>
            <form method="POST" action="/game/donate">
                <?= $this->csrf() ?>

                <div class="form-group">
                    <label for="username">Recipient Username:</label>
                    <input type="text"
                           id="username"
                           name="username"
                           value="<?= $this->e($donateTo ?? $this->old('username')) ?>"
                           required>
                    <?php if ($error = $this->error('username')): ?>
                        <span class="error"><?= $this->e($error) ?></span>
                    <?php endif; ?>
                </div>

                <div class="form-group">
                    <label for="amount">Amount:</label>
                    <input type="number"
                           id="amount"
                           name="amount"
                           min="1"
                           max="<?= $user->cash ?>"
                           value="<?= $this->e($this->old('amount')) ?>"
                           required>
                    <?php if ($error = $this->error('amount')): ?>
                        <span class="error"><?= $this->e($error) ?></span>
                    <?php endif; ?>
                </div>

                <button type="submit" class="btn btn-primary">Send Money</button>
            </form>
        <?php endif; ?>
    </div>
<?php $this->endSection() ?>