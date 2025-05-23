<?php $this->extends('layouts.game') ?>

<?php $this->section('game_content') ?>
    <div class="messages">
        <h1>Outbox</h1>

        <div class="message-actions">
            <a href="/game/messages/compose" class="btn btn-primary">Compose</a>
            <a href="/game/messages" class="btn btn-secondary">Inbox</a>
        </div>

        <?php if (empty($messages)): ?>
            <p>No sent messages.</p>
        <?php else: ?>
            <form method="POST" action="/game/messages/delete">
                <?= $this->csrf() ?>
                <input type="hidden" name="from" value="outbox">

                <table class="messages-table">
                    <thead>
                    <tr>
                        <th><input type="checkbox" id="select-all"></th>
                        <th>To</th>
                        <th>Subject</th>
                        <th>Date</th>
                    </tr>
                    </thead>
                    <tbody>
                    <?php foreach ($messages as $message): ?>
                        <tr>
                            <td>
                                <input type="checkbox" name="id[<?= $message->message_id ?>]" value="1">
                            </td>
                            <td>
                                <?php $recipient = $message->recipient(); ?>
                                <?= $recipient ? $this->e($recipient->username) : 'Unknown' ?>
                            </td>
                            <td>
                                <a href="/game/messages/read/<?= $message->message_id ?>">
                                    <?= $this->e($message->message_subject) ?>
                                </a>
                            </td>
                            <td><?= $message->message_time->format('Y-m-d H:i') ?></td>
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