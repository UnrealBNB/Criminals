<?php $this->extends('layouts.game') ?>

<?php $this->section('game_content') ?>
    <div class="messages read">
        <h1>Message</h1>

        <div class="message-header">
            <table>
                <tr>
                    <td>From:</td>
                    <td>
                        <?php $sender = $message->sender(); ?>
                        <?php if ($sender): ?>
                            <a href="/game/profile/<?= $sender->id ?>">
                                <?= $this->e($sender->username) ?>
                            </a>
                        <?php else: ?>
                            System
                        <?php endif; ?>
                    </td>
                </tr>
                <tr>
                    <td>To:</td>
                    <td>
                        <?php $recipient = $message->recipient(); ?>
                        <?php if ($recipient): ?>
                            <a href="/game/profile/<?= $recipient->id ?>">
                                <?= $this->e($recipient->username) ?>
                            </a>
                        <?php else: ?>
                            Unknown
                        <?php endif; ?>
                    </td>
                </tr>
                <tr>
                    <td>Subject:</td>
                    <td><?= $this->e($message->message_subject) ?></td>
                </tr>
                <tr>
                    <td>Date:</td>
                    <td><?= $message->message_time->format('Y-m-d H:i:s') ?></td>
                </tr>
            </table>
        </div>

        <div class="message-body">
            <?= nl2br($this->e($message->message_message)) ?>
        </div>

        <div class="message-actions">
            <?php if ($message->message_to_id === $user->id && $sender): ?>
                <a href="/game/messages/compose/<?= $sender->id ?>?subject=Re: <?= urlencode($message->message_subject) ?>"
                   class="btn btn-primary">Reply</a>
            <?php endif; ?>

            <form method="POST" action="/game/messages/delete" style="display: inline;">
                <?= $this->csrf() ?>
                <input type="hidden" name="from" value="<?= $message->message_to_id === $user->id ? 'inbox' : 'outbox' ?>">
                <input type="hidden" name="id[<?= $message->message_id ?>]" value="1">
                <button type="submit" class="btn btn-danger">Delete</button>
            </form>

            <a href="/game/messages" class="btn btn-secondary">Back to Inbox</a>
        </div>
    </div>
<?php $this->endSection() ?>