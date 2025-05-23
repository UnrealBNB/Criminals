<?php $this->extends('layouts.game') ?>

<?php $this->section('game_content') ?>
    <div class="messages compose">
        <h1>Compose Message</h1>

        <form method="POST" action="/game/messages/send">
            <?= $this->csrf() ?>

            <div class="form-group">
                <label for="to">To:</label>
                <input type="text"
                       id="to"
                       name="to"
                       value="<?= $this->e($recipient ? $recipient->username : $this->old('to')) ?>"
                       placeholder="Username"
                       required>
                <?php if ($error = $this->error('to')): ?>
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
                          maxlength="1000"
                          required><?= $this->e($this->old('message')) ?></textarea>
                <?php if ($error = $this->error('message')): ?>
                    <span class="error"><?= $this->e($error) ?></span>
                <?php endif; ?>
            </div>

            <button type="submit" class="btn btn-primary">Send Message</button>
            <a href="/game/messages" class="btn btn-secondary">Cancel</a>
        </form>
    </div>
<?php $this->endSection() ?>