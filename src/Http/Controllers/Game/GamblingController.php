<?php

declare(strict_types=1);

namespace App\Http\Controllers\Game;

use App\Http\Controllers\Controller;
use App\Services\GamblingService;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;

class GamblingController extends Controller
{
    public function __construct(
        Container $container,
        private readonly GamblingService $gamblingService
    ) {
        parent::__construct($container);
    }

    public function numberGame(Request $request): Response
    {
        if ($request->isMethod('GET')) {
            return $this->view('game.gambling.number-game', [
                'user' => $this->auth()->user(),
            ]);
        }

        $user = $this->auth()->user();
        $number = (int) $request->request->get('number');
        $amount = (int) $request->request->get('amount');

        // Validate
        if ($number < 1 || $number > 10) {
            flash('error', 'Please select a number between 1 and 10');
            return $this->back();
        }

        if ($amount <= 0 || !$user->canAfford($amount)) {
            flash('error', 'Invalid bet amount');
            return $this->back();
        }

        $result = $this->gamblingService->playNumberGame($user, $number, $amount);

        if ($result['won']) {
            flash('success', "You won! The number was {$result['winningNumber']}. You won {$result['winnings']}!");
        } else {
            flash('error', "You lost! The number was {$result['winningNumber']}. You lost {$amount}!");
        }

        return $this->back();
    }

    public function russianRoulette(Request $request): Response
    {
        if ($request->isMethod('GET')) {
            return $this->view('game.gambling.russian-roulette', [
                'user' => $this->auth()->user(),
            ]);
        }

        $user = $this->auth()->user();
        $result = $this->gamblingService->playRussianRoulette($user);

        if ($result['survived']) {
            flash('success', 'You pull the trigger and the gun clicks, you survived and won 500!');
        } else {
            flash('error', 'You pull the trigger and before you know it the bullet goes through your head! You lost 500!');
        }

        return $this->back();
    }

    public function coinFlip(Request $request): Response
    {
        if ($request->isMethod('GET')) {
            return $this->view('game.gambling.coin-flip', [
                'user' => $this->auth()->user(),
            ]);
        }

        $user = $this->auth()->user();
        $choice = (int) $request->request->get('choice'); // 0 = heads, 1 = tails
        $amount = (int) $request->request->get('amount');

        // Validate
        if ($choice !== 0 && $choice !== 1) {
            flash('error', 'Please choose heads or tails');
            return $this->back();
        }

        if (!in_array($amount, [250, 500, 1000])) {
            flash('error', 'Invalid bet amount');
            return $this->back();
        }

        if (!$user->canAfford($amount)) {
            flash('error', 'You cannot afford this bet');
            return $this->back();
        }

        $result = $this->gamblingService->playCoinFlip($user, $choice, $amount);

        $choiceName = $choice === 0 ? 'heads' : 'tails';
        if ($result['won']) {
            flash('success', "You chose {$choiceName} and that was right! You won {$result['winnings']}!");
        } else {
            flash('error', "Sorry... you got it wrong! You lost {$amount}!");
        }

        return $this->back();
    }

    public function rockPaperScissors(Request $request): Response
    {
        if ($request->isMethod('GET')) {
            return $this->view('game.gambling.rock-paper-scissors', [
                'user' => $this->auth()->user(),
            ]);
        }

        $user = $this->auth()->user();
        $choice = (int) $request->request->get('choice'); // 1=rock, 2=paper, 3=scissors

        // Validate
        if (!in_array($choice, [1, 2, 3])) {
            flash('error', 'Please make a valid choice');
            return $this->back();
        }

        $result = $this->gamblingService->playRockPaperScissors($user, $choice);

        $choices = [1 => 'rock', 2 => 'paper', 3 => 'scissors'];
        $userChoice = $choices[$choice];
        $compChoice = $choices[$result['computerChoice']];

        if ($result['won']) {
            flash('success', "You won! You had {$userChoice} and the computer had {$compChoice}! You win 500 cash!");
        } elseif ($result['draw']) {
            flash('info', "It's a draw! You both had {$userChoice}!");
        } else {
            flash('error', "You lost! You had {$userChoice} and the computer had {$compChoice}! You lose 500 cash!");
        }

        return $this->back();
    }

    public function higherLower(Request $request): Response
    {
        if ($request->isMethod('GET')) {
            $user = $this->auth()->user();
            $gameState = $this->gamblingService->getHigherLowerState($user);

            return $this->view('game.gambling.higher-lower', [
                'user' => $user,
                'round' => $gameState['round'],
                'costMoney' => $gameState['costMoney'],
                'winMoney' => $gameState['winMoney'],
                'number' => $gameState['number'],
            ]);
        }

        $user = $this->auth()->user();