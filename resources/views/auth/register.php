<?php $this->extends('layouts.app') ?>

<?php $this->section('content') ?>
    <div class="auth-container">
        <div class="auth-box">
            <h1>Register</h1>

            <form method="POST" action="/register">
                <?= $this->csrf() ?>

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
                    <label for="email">Email</label>
                    <input type="email"
                           id="email"
                           name="email"
                           value="<?= $this->e($this->old('email')) ?>"
                           required>
                    <?php if ($error = $this->error('email')): ?>
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

                <div class="form-group">
                    <label for="password_confirmation">Confirm Password</label>
                    <input type="password"
                           id="password_confirmation"
                           name="password_confirmation"
                           required>
                </div>

                <div class="form-group">
                    <label for="type">Choose Your Type</label>
                    <select id="type" name="type" required>
                        <option value="">Select Type</option>
                        <option value="1" <?= $this->old('type') == 1 ? 'selected' : '' ?>>Drug Dealer</option>
                        <option value="2" <?= $this->old('type') == 2 ? 'selected' : '' ?>>Scientist</option>
                        <option value="3" <?= $this->old('type') == 3 ? 'selected' : '' ?>>Police</option>
                    </select>
                    <?php if ($error = $this->error('type')): ?>
                        <span class="error"><?= $this->e($error) ?></span>
                    <?php endif; ?>
                </div>

                <button type="submit" class="btn btn-primary">Register</button>
            </form>

            <p class="auth-links">
                Already have an account? <a href="/login">Login here</a>
            </p>
        </div>
    </div>
<?php $this->endSection() ?>