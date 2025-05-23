<?php

declare(strict_types=1);

namespace App\Http\Controllers\Game;

use App\Http\Controllers\Controller;
use App\Models\Message;
use App\Models\User;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;

class MessageController extends Controller
{
    public function inbox(): Response
    {
        $user = $this->auth()->user();
        $messages = Message::getInbox($user->id);

        return $this->view('game.messages.inbox', [
            'messages' => $messages,
            'user' => $user,
        ]);
    }

    public function outbox(): Response
    {
        $user = $this->auth()->user();
        $messages = Message::getOutbox($user->id);

        return $this->view('game.messages.outbox', [
            'messages' => $messages,
            'user' => $user,
        ]);
    }

    public function read(int $id): Response
    {
        $user = $this->auth()->user();
        $message = Message::find($id);

        if (!$message) {
            flash('error', 'Message not found');
            return $this->redirect('/game/messages');
        }

        // Check if user can read this message
        if ($message->message_to_id !== $user->id && $message->message_from_id !== $user->id) {
            if (!$user->isAdmin()) {
                flash('error', 'You do not have permission to read this message');
                return $this->redirect('/game/messages');
            }
        }

        // Mark as read if recipient
        if ($message->message_to_id === $user->id) {
            $message->markAsRead();
        }

        return $this->view('game.messages.read', [
            'message' => $message,
            'user' => $user,
        ]);
    }

    public function compose(?int $to = null): Response
    {
        $user = $this->auth()->user();
        $recipient = null;

        if ($to) {
            $recipient = User::find($to);
        }

        return $this->view('game.messages.compose', [
            'recipient' => $recipient,
            'user' => $user,
        ]);
    }

    public function send(Request $request): Response
    {
        $user = $this->auth()->user();
        $data = $request->request->all();

        $errors = $this->validate($data, [
            'to' => 'required|string',
            'subject' => 'required|string|max:250',
            'message' => 'required|string|max:1000',
        ]);

        if (!empty($errors)) {
            $_SESSION['_errors'] = $errors;
            $_SESSION['_old_input'] = $data;
            return $this->back();
        }

        // Find recipient
        $recipient = User::query()
            ->where('username', $data['to'])
            ->first();

        if (!$recipient) {
            $_SESSION['_errors'] = ['to' => 'Recipient not found'];
            $_SESSION['_old_input'] = $data;
            return $this->back();
        }

        // Send message
        $message = Message::send(
            $user->id,
            $recipient->id,
            $data['subject'],
            $data['message']
        );

        if ($message) {
            flash('success', 'Message sent successfully to ' . $recipient->username);
            return $this->redirect('/game/messages/outbox');
        }

        flash('error', 'Failed to send message');
        return $this->back();
    }

    public function delete(Request $request): Response
    {
        $user = $this->auth()->user();
        $messageIds = $request->request->all()['id'] ?? [];
        $from = $request->request->get('from', 'inbox');

        if (empty($messageIds)) {
            flash('error', 'No messages selected');
            return $this->back();
        }

        $deleted = 0;
        foreach ($messageIds as $id => $value) {
            $message = Message::find($id);

            if (!$message) {
                continue;
            }

            // Delete based on inbox/outbox
            if ($from === 'inbox' && $message->message_to_id === $user->id) {
                if ($message->deleteForRecipient()) {
                    $deleted++;
                }
            } elseif ($from === 'outbox' && $message->message_from_id === $user->id) {
                if ($message->deleteForSender()) {
                    $deleted++;
                }
            }
        }

        if ($deleted > 0) {
            flash('success', "{$deleted} message(s) deleted successfully");
        }

        return $this->back();
    }
}