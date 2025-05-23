<?php

declare(strict_types=1);

namespace App\Http\Controllers;

use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;

class BankController extends Controller
{
    public function index(): Response
    {
        $user = $this->auth->user();

        return view('bank.index', [
            'user' => $user,
        ]);
    }

    public function deposit(Request $request): Response
    {
        $user = $this->auth->user();
        $amount = (int) $request->request->get('amount', 0);

        if ($amount <= 0) {
            flash('error', 'Please enter a valid amount');
            return back();
        }

        if ($user->bank_left < 1) {
            flash('error', 'You have reached your daily deposit limit');
            return back();
        }

        if (!$user->canAfford($amount)) {
            flash('error', 'You do not have enough cash');
            return back();
        }

        if ($user->deposit($amount)) {
            flash('success', "Successfully deposited {$amount} to your bank account");
        } else {
            flash('error', 'Deposit failed. Please try again.');
        }

        return back();
    }

    public function withdraw(Request $request): Response
    {
        $user = $this->auth->user();
        $amount = (int) $request->request->get('amount', 0);

        if ($amount <= 0) {
            flash('error', 'Please enter a valid amount');
            return back();
        }

        if ($user->bank_left < 1) {
            flash('error', 'You have reached your daily withdrawal limit');
            return back();
        }

        if ($user->bank < $amount) {
            flash('error', 'Insufficient bank balance');
            return back();
        }

        if ($user->withdraw($amount)) {
            flash('success', "Successfully withdrawn {$amount} from your bank account");
        } else {
            flash('error', 'Withdrawal failed. Please try again.');
        }

        return back();
    }
}