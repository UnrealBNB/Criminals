<?php $this->extends('layouts.admin') ?>

<?php $this->section('admin_content') ?>
    <div class="admin-country-message">
        <h1>Send Message by Country</h1>

        <form method="POST" action="/admin/messages/country">
            <?= $this->csrf() ?>

            <div class="form-group">
                <label for="country">Country:</label>
                <select name="country" id="country" required>
                    <option value="">Select country</option>
                    <?php foreach ($countries as $id => $name): ?>
                        <option value="<?= $id ?>"><?= $this->e($name) ?></option>
                    <?php endforeach; ?>
                </select>
                <?php if ($error = $this->error('country')): ?>
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