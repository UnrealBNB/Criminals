<?php

declare(strict_types=1);

namespace App\Http\Controllers\Game;

use App\Http\Controllers\Controller;
use App\Models\User;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;

class DonateController extends Controller
{
    public function index(Request $request): Response
    {
        $user = $this->auth()->user();
        $donateTo = null;

        if ($request->query->has('to')) {
            $donateTo = $request->query->get('to');
        }

        return $this->view('game.donate.index', [
            'user' => $user,
            'donateTo' => $donateTo,
        ]);
    }

    public function donate(Request $request): Response
    {
        $user = $this->auth()->user();
        $data = $request->request->all();

        $errors = $this->validate($data, [
            'username' => 'required|string',
            'amount' => 'required|integer|min:1',
        ]);

        if (!empty($errors)) {
            $_SESSION['_errors'] = $errors;
            $_SESSION['_old_input'] = $data;
            return $this->back();
        }

        $amount = (int) $data['amount'];
        $recipient = User::query()
            ->where('username', $data['username'])
            ->first();

        if (!$recipient) {
            flash('error', 'Recipient not found');
            return $this->back();
        }

        if ($recipient->id === $user->id) {
            flash('error', 'You cannot donate to yourself');
            return $this->back();
        }

        if ($recipient->isProtected()) {
            flash('error', 'This player is still under protection');
            return $this->back();
        }

        if ($user->isProtected()) {
            flash('error', 'You cannot donate while under protection');
            return $this->back();
        }

        if (!$user->canAfford($amount)) {
            flash('error', 'You do not have enough cash');
            return $this->back();
        }

        // Process donation
        db()->beginTransaction();

        try {
            $user->removeCash($amount);
            $recipient->addCash($amount);

            db()->commit();
            flash('success', "Successfully donated {$amount} to {$recipient->username}");
        } catch (\Throwable $e) {
            db()->rollBack();
            flash('error', 'Donation failed. Please try again.');
        }

        return $this->redirect('/game/donate');
    }
}