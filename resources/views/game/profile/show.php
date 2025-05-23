<?php $this->extends('layouts.game') ?>

<?php $this->section('game_content') ?>
    <div class="messages">
        <h1>Inbox</h1>

        <div class="message-actions">
            <a href="/game/messages/compose" class="btn btn-primary">Compose</a>
            <a href="/game/messages/outbox" class="btn btn-secondary">Outbox</a>
        </div>

        <?php if (empty($messages)): ?>
            <p>No messages in your inbox.</p>
        <?php else: ?>
            <form method="POST" action="/game/messages/delete">
                <?= $this->csrf() ?>
                <input type="hidden" name="from" value="inbox">

                <table class="messages-table">
                    <thead>
                    <tr>
                        <th><input type="checkbox" id="select-all"></th>
                        <th>From</th>
                        <th>Subject</th>
                        <th>Date</th>
                        <th>Status</th>
                    </tr>
                    </thead>
                    <tbody>
                    <?php foreach ($messages as $message): ?>
                        <tr class="<?= !$message->isRead() ? 'unread' : '' ?>">
                            <td>
                                <input type="checkbox" name="id[<?= $message->message_id ?>]" value="1">
                            </td>
                            <td>
                                <?php $sender = $message->sender(); ?>
                                <?= $sender ? $this->e($sender->username) : 'System' ?>
                            </td>
                            <td>
                                <a href="/game/messages/read/<?= $message->message_id ?>">
                                    <?= $this->e($message->message_subject) ?>
                                </a>
                            </td>
                            <td><?= $message->message_time->format('Y-m-d H:i') ?></td>
                            <td><?= !$message->isRead() ? 'Unread' : 'Read' ?></td>
                        </tr>
                    <?php endforeach; ?>
                    </tbody>
                </table>

                <button type="submit" class="btn btn-danger">Delete Selected</button>
            </form>
        <?php endif; ?>
    </div>

    <script>
        document.getElementById('select-all')?.addEventListener('change', function() {
            const checkboxes = document.querySelectorAll('input[type="checkbox"][name^="id["]');
            checkboxes.forEach(cb => cb.checked = this.checked);
        });
    </script>
<?php $this->endSection() ?>