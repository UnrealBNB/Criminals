<?php

declare(strict_types=1);

namespace App\Http\Controllers\Game\Clan;

use App\Http\Controllers\Controller;
use App\Services\ClanService;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;

class ClanBankController extends Controller
{
    public function __construct(
        Container $container,
        private readonly ClanService $clanService
    ) {
        parent::__construct($container);
    }

    public function index(): Response
    {
        $user = $this->auth()->user();

        if (!$user->hasClanLevel(6)) {
            flash('error', 'Insufficient clan permissions');
            return $this->redirect('/game/clan');
        }

        $clan = $user->clan();

        return $this->view('game.clan.bank.index', [
            'user' => $user,
            'clan' => $clan,
        ]);
    }

    public function deposit(Request $request): Response
    {
        $user = $this->auth()->user();

        if (!$user->hasClanLevel(6)) {
            flash('error', 'Insufficient clan permissions');
            return $this->redirect('/game/clan');
        }

        $clan = $user->clan();
        $amount = (int) $request->request->get('amount', 0);

        if ($amount <= 0) {
            flash('error', 'Please enter a valid amount');
            return $this->back();
        }

        if ($amount > $clan->cash) {
            flash('error', 'Clan does not have enough cash');
            return $this->back();
        }

        if ($clan->bankleft < 1) {
            flash('error', 'Clan has reached daily deposit limit');
            return $this->back();
        }

        $result = $this->clanService->clanBankDeposit($user, $amount);

        if ($result['success']) {
            flash('success', "Successfully deposited {$amount} to clan bank");
        } else {
            flash('error', $result['message']);
        }

        return $this->back();
    }

    public function withdraw(Request $request): Response
    {
        $user = $this->auth()->user();

        if (!$user->hasClanLevel(6)) {
            flash('error', 'Insufficient clan permissions');
            return $this->redirect('/game/clan');
        }

        $clan = $user->clan();
        $amount = (int) $request->request->get('amount', 0);

        if ($amount <= 0) {
            flash('error', 'Please enter a valid amount');
            return $this->back();
        }

        if ($amount > $clan->bank) {
            flash('error', 'Insufficient clan bank balance');
            return $this->back();
        }

        $result = $this->clanService->clanBankWithdraw($user, $amount);

        if ($result['success']) {
            flash('success', "Successfully withdrawn {$amount} from clan bank");
        } else {
            flash('error', $result['message']);
        }

        return $this->back();
    }

    public function donate(Request $request): Response
    {
        $user = $this->auth()->user();

        if (!$user->isInClan()) {
            flash('error', 'You must be in a clan to donate');
            return $this->redirect('/game/clan');
        }

        if ($request->isMethod('GET')) {
            return $this->view('game.clan.bank.donate', [
                'user' => $user,
                'clan' => $user->clan(),
            ]);
        }

        $amount = (int) $request->request->get('amount', 0);

        if ($amount <= 0) {
            flash('error', 'Please enter a valid amount');
            return $this->back();
        }

        if (!$user->canAfford($amount)) {
            flash('error', 'You do not have enough cash');
            return $this->back();
        }

        $result = $this->clanService->donateToClean($user, $amount);

        if ($result['success']) {
            flash('success', "Successfully donated {$amount} to your clan");
        } else {
            flash('error', $result['message']);
        }

        return $this->back();
    }
}