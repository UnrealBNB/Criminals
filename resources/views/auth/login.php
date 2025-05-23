<?php $this->extends('layouts.app') ?>

<?php $this->section('content') ?>
    <div class="auth-container">
        <div class="auth-box">
            <h1>Login</h1>

            <form method="POST" action="/login">
                <?= $this->csrf() ?>

                <?php if ($error = $this->error('login')): ?>
                    <div class="alert alert-error"><?= $this->e($error) ?></div>
                <?php endif; ?>

                <div class="form-group">
                    <label for="username">Username</label>
                    <input type="text"
                           id="username"
                           name="username"
                           value="<?= $this->e($this->old('username')) ?>"
                           required
                           autofocus>
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

                <button type="submit" class="btn btn-primary">Login</button>
            </form>

            <p class="auth-links">
                Don't have an account? <a href="/register">Register here</a>
            </p>
        </div>
    </div>
<?php $this->endSection() ?>