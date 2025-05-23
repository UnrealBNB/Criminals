<?php

declare(strict_types=1);

namespace App\Http\Controllers\Game;

use App\Http\Controllers\Controller;
use App\Models\User;
use App\Services\AttackService;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;

class AttackController extends Controller
{
    public function __construct(
        Container $container,
        private readonly AttackService $attackService
    ) {
        parent::__construct($container);
    }

    public function attack(int $id): Response
    {
        $attacker = $this->auth()->user();
        $defender = User::find($id);

        if (!$defender) {
            flash('error', 'Target player not found');
            return $this->redirect('/game/list');
        }

        // Check if attack is allowed
        $canAttack = $attacker->canAttack($defender);
        if (!$canAttack['allowed']) {
            flash('error', $canAttack['reason']);
            return $this->back();
        }

        // Check cooldown
        if ($this->attackService->isOnCooldown($attacker->id)) {
            flash('error', 'You are still recovering from your last attack');
            return $this->back();
        }

        // Perform attack
        $result = $this->attackService->performAttack($attacker, $defender);

        if ($result['success']) {
            flash('success', $result['message']);
        } else {
            flash('error', $result['message']);
        }

        return $this->view('game.attack.result', [
            'result' => $result,
            'attacker' => $attacker,
            'defender' => $defender,
        ]);
    }
}