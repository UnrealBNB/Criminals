<?php $this->extends('layouts.admin') ?>

<?php $this->section('admin_content') ?>
    <div class="admin-mass-message">
        <h1>Send Mass Message</h1>

        <div class="info-box">
            <p>This will send a message to ALL active users in the game.</p>
        </div>

        <form method="POST" action="/admin/messages/mass">
            <?= $this->csrf() ?>

            <div class="form-group">
                <label for="subject">Subject:</label>
                <input type="text"
                       id="subject"
                       name="subject"
                       value="<?= $this->e($this->old('subject')) ?>"
                       maxlength="250"
                       required>
                <?php if ($error = $this->error('subject')): ?>
                    <span class="error"><?= $this->e($error) ?></span>
                <?php endif; ?>
            </div>

            <div class="form-group">
                <label for="message">Message:</label>
                <textarea id="message"
                          name="message"
                          rows="10"
                          maxlength="2000"
                          required><?= $this->e($this->old('message')) ?></textarea>
                <?php if ($error = $this->error('message')): ?>
                    <span class="error"><?= $this->e($error) ?></span>
                <?php endif; ?>
            </div>

            <button type="submit" class="btn btn-primary">Send to All Users</button>
            <a href="/admin/messages" class="btn btn-secondary">Cancel</a>
        </form>
    </div>
<?php $this->endSection() ?>