<?php

declare(strict_types=1);

namespace App\Http\Controllers;

use App\Models\Click;
use App\Models\User;
use App\Services\ClickService;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;

class ClickController extends Controller
{
    public function __construct(
        Container $container,
        private readonly ClickService $clickService
    ) {
        parent::__construct($container);
    }

    public function index(int $id): Response
    {
        $targetUser = User::find($id);

        if (!$targetUser) {
            return $this->view('click.error', [
                'error' => 'User not found!'
            ]);
        }

        $ip = request()->getClientIp();
        $hasClicked = Click::hasClickedToday($targetUser->id, $ip);

        if ($hasClicked) {
            return $this->view('click.error', [
                'error' => 'You have already clicked today!'
            ]);
        }

        if ($targetUser->clicks_today >= config('game.max_clicks_per_day', 50)) {
            return $this->view('click.error', [
                'error' => $targetUser->username . ' has already received enough clicks today!'
            ]);
        }

        $clickText = $this->clickService->getClickText($targetUser);

        return $this->view('click.index', [
            'user' => $targetUser,
            'text' => $clickText['intro'],
            'clicked' => false,
        ]);
    }

    public function process(Request $request, int $id): Response
    {
        $targetUser = User::find($id);

        if (!$targetUser) {
            return $this->view('click.error', [
                'error' => 'User not found!'
            ]);
        }

        $ip = request()->getClientIp();

        // Process click
        $result = $this->clickService->processClick($targetUser, $ip);

        if (!$result['success']) {
            return $this->view('click.error', [
                'error' => $result['message']
            ]);
        }

        return $this->view('click.index', [
            'user' => $targetUser,
            'text' => $result['resultText'],
            'clickType' => $result['clickType'],
            'clicked' => true,
        ]);
    }
}