<?php $this->extends('layouts.app') ?>

<?php $this->section('content') ?>
    <div class="login-container">
        <h1>Login</h1>

        <form method="POST" action="/login">
            <?= $this->csrf() ?>

            <?php if ($error = $this->error('login')): ?>
                <div class="error"><?= $this->e($error) ?></div>
            <?php endif; ?>

            <div class="form-group">
                <label for="username">Username</label>
                <input type="text"
                       id="username"
                       name="username"
                       value="<?= $this->e($this->old('username')) ?>"
                       required>
                <?php if ($error = $this->error('username')): ?>
                    <span class="error"><?= $this->e($error) ?></span>
                <?php endif; ?>
            </div>

            <div class="form-group">
                <label for="password">Password</label>
                <input type="password"
                       id="password"
                       name="password"
                       required>
                <?php if ($error = $this->error('password')): ?>
                    <span class="error"><?= $this->e($error) ?></span>
                <?php endif; ?>
            </div>

            <button type="submit">Login</button>
        </form>

        <p>Don't have an account? <a href="/register">Register here</a></p>
    </div>
<?php $this->endSection() ?>