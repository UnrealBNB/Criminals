<?php $this->extends('layouts.game') ?>

<?php $this->section('game_content') ?>
    <div class="clan-message">
        <h1>Send Clan Message</h1>

        <div class="message-info">
            <p>Send a message to all clan members.</p>
            <p>Members: <?= $clan->getMemberCount() ?></p>
        </div>

        <form method="POST" action="/game/clan/message/send">
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
                          maxlength="1000"
                          required><?= $this->e($this->old('message')) ?></textarea>
                <?php if ($error = $this->error('message')): ?>
                    <span class="error"><?= $this->e($error) ?></span>
                <?php endif; ?>
            </div>

            <button type="submit" class="btn btn-primary">Send to All Members</button>
            <a href="/game/clan/overview" class="btn btn-secondary">Cancel</a>
        </form>
    </div>
<?php $this->endSection() ?>