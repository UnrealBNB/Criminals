<?php

declare(strict_types=1);

namespace App\Http\Controllers;

use App\Models\User;
use App\Services\AttackService;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;

class AttackController extends Controller
{
    public function __construct(
        private readonly AttackService $attackService
    ) {}

    public function attack(Request $request): Response
    {
        $attacker = $this->auth->user();
        $targetId = (int) ($request->query->get('id') ?? $request->query->get('player'));

        if (!$targetId) {
            flash('error', 'No target specified');
            return back();
        }

        $defender = User::find($targetId);
        if (!$defender) {
            flash('error', 'Target player not found');
            return back();
        }

        // Check if attack is allowed
        $canAttack = $attacker->canAttack($defender);
        if (!$canAttack['allowed']) {
            flash('error', $canAttack['reason']);
            return back();
        }

        // Check cooldown
        if ($this->attackService->isOnCooldown($attacker->id)) {
            flash('error', 'You are still recovering from your last attack');
            return back();
        }

        // Perform attack
        $result = $this->attackService->performAttack($attacker, $defender);

        if ($result['success']) {
            flash('success', $result['message']);
        } else {
            flash('error', $result['message']);
        }

        return view('attack.result', [
            'result' => $result,
            'attacker' => $attacker,
            'defender' => $defender,
        ]);
    }
}