<?php

declare(strict_types=1);

namespace App\Http\Controllers\Game\Clan;

use App\Http\Controllers\Controller;
use App\Models\Message;
use App\Services\ClanService;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;

class ClanMessageController extends Controller
{
    public function __construct(
        Container $container,
        private readonly ClanService $clanService
    ) {
        parent::__construct($container);
    }

    public function compose(): Response
    {
        $user = $this->auth()->user();

        if (!$user->hasClanLevel(7)) {
            flash('error', 'Insufficient clan permissions');
            return $this->redirect('/game/clan');
        }

        return $this->view('game.clan.message.compose', [
            'user' => $user,
            'clan' => $user->clan(),
        ]);
    }

    public function send(Request $request): Response
    {
        $user = $this->auth()->user();

        if (!$user->hasClanLevel(7)) {
            flash('error', 'Insufficient clan permissions');
            return $this->redirect('/game/clan');
        }

        $data = $request->request->all();

        $errors = $this->validate($data, [
            'subject' => 'required|string|max:250',
            'message' => 'required|string|max:1000',
        ]);

        if (!empty($errors)) {
            $_SESSION['_errors'] = $errors;
            $_SESSION['_old_input'] = $data;
            return $this->back();
        }

        $result = $this->clanService->sendClanMessage(
            $user,
            $data['subject'],
            $data['message']
        );

        if ($result['success']) {
            flash('success', "Message sent to all {$result['sent']} clan members");
        } else {
            flash('error', $result['message']);
        }

        return $this->redirect('/game/clan/message');
    }
}