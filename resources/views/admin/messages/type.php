<?php $this->extends('layouts.admin') ?>

<?php $this->section('admin_content') ?>
    <div class="admin-type-message">
        <h1>Send Message by Type</h1>

        <form method="POST" action="/admin/messages/type">
            <?= $this->csrf() ?>

            <div class="form-group">
                <label for="type">User Type:</label>
                <select name="type" id="type" required>
                    <option value="">Select type</option>
                    <option value="1">Drug Dealers</option>
                    <option value="2">Scientists</option>
                    <option value="3">Police</option>
                </select>
                <?php if ($error = $this->error('type')): ?>
                    <span class="error"><?= $this->e($error) ?></span>
                <?php endif; ?>
            </div>

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

            <button type="submit" class="btn btn-primary">Send Message</button>
            <a href="/admin/messages" class="btn btn-secondary">Cancel</a>
        </form>
    </div>
<?php $this->endSection() ?>