<?php

declare(strict_types=1);

namespace App\Http\Controllers\Game\Clan;

use App\Http\Controllers\Controller;
use App\Models\Clan;
use App\Models\User;
use App\Services\ClanService;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;

class ClanController extends Controller
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

        if ($user->isInClan()) {
            return $this->redirect('/game/clan/overview');
        }

        return $this->view('game.clan.index', [
            'user' => $user,
        ]);
    }

    public function overview(): Response
    {
        $user = $this->auth()->user();

        if (!$user->isInClan()) {
            return $this->redirect('/game/clan');
        }

        $clan = $user->clan();
        $members = $clan->members();
        $totalPower = $clan->getTotalPower();

        return $this->view('game.clan.overview', [
            'user' => $user,
            'clan' => $clan,
            'members' => $members,
            'totalPower' => $totalPower,
        ]);
    }

    public function create(Request $request): Response
    {
        $user = $this->auth()->user();

        if ($user->isInClan()) {
            flash('error', 'You already have or are in a clan');
            return $this->redirect('/game/clan');
        }

        if ($request->isMethod('GET')) {
            return $this->view('game.clan.create', [
                'user' => $user,
            ]);
        }

        $data = $request->request->all();

        $errors = $this->validate($data, [
            'name' => 'required|string|min:3|max:200|regex:/^[A-Za-z0-9_\- ]+$/',
        ]);

        if (!empty($errors)) {
            $_SESSION['_errors'] = $errors;
            $_SESSION['_old_input'] = $data;
            return $this->back();
        }

        // Check if clan name exists
        if (Clan::findByName($data['name'])) {
            $_SESSION['_errors'] = ['name' => 'Clan name already exists'];
            $_SESSION['_old_input'] = $data;
            return $this->back();
        }

        $result = $this->clanService->createClan($user, $data['name']);

        if ($result['success']) {
            flash('success', 'Clan created successfully!');
            return $this->redirect('/game/clan/overview');
        }

        flash('error', $result['message']);
        return $this->back();
    }

    public function join(Request $request): Response
    {
        $user = $this->auth()->user();

        if ($user->isInClan()) {
            flash('error', 'You are already in a clan');
            return $this->redirect('/game/clan');
        }

        if ($request->isMethod('GET')) {
            return $this->view('game.clan.join', [
                'user' => $user,
            ]);
        }

        $data = $request->request->all();

        $errors = $this->validate($data, [
            'name' => 'required|string',
        ]);

        if (!empty($errors)) {
            $_SESSION['_errors'] = $errors;
            $_SESSION['_old_input'] = $data;
            return $this->back();
        }

        $clan = Clan::findByName($data['name']);

        if (!$clan) {
            flash('error', 'Clan not found');
            return $this->back();
        }

        if ($clan->clan_type !== $user->type) {
            flash('error', 'You cannot join a clan of a different type');
            return $this->back();
        }

        $result = $this->clanService->applyToClan($user, $clan);

        if ($result['success']) {
            flash('success', 'Application submitted successfully!');
        } else {
            flash('error', $result['message']);
        }

        return $this->back();
    }

    public function leave(Request $request): Response
    {
        $user = $this->auth()->user();

        if (!$user->isInClan()) {
            flash('error', 'You are not in a clan');
            return $this->redirect('/game/clan');
        }

        if ($user->isClanOwner()) {
            flash('error', 'As clan owner, you cannot leave. Delete the clan instead.');
            return $this->back();
        }

        if ($request->isMethod('GET')) {
            return $this->view('game.clan.leave', [
                'user' => $user,
                'clan' => $user->clan(),
            ]);
        }

        if ($request->request->get('confirm') !== 'yes') {
            flash('error', 'Please confirm you want to leave');
            return $this->back();
        }

        $result = $this->clanService->leaveClan($user);

        if ($result['success']) {
            flash('success', 'You have left the clan');
            return $this->redirect('/game/clan');
        }

        flash('error', $result['message']);
        return $this->back();
    }

    public function delete(Request $request): Response
    {
        $user = $this->auth()->user();

        if (!$user->isClanOwner()) {
            flash('error', 'Only clan owners can delete clans');
            return $this->redirect('/game/clan');
        }

        if ($request->isMethod('GET')) {
            return $this->view('game.clan.delete', [
                'user' => $user,
                'clan' => $user->clan(),
            ]);
        }

        if ($request->request->get('confirm') !== 'yes') {
            flash('error', 'Please confirm deletion');
            return $this->back();
        }

        $result = $this->clanService->deleteClan($user);

        if ($result['success']) {
            flash('success', 'Clan deleted successfully');
            return $this->redirect('/game/clan');
        }

        flash('error', $result['message']);
        return $this->back();
    }

    public function list(): Response
    {
        $clans = db()->fetchAll(
            "SELECT c.*, u.username as owner_name, 
                    (SELECT COUNT(*) FROM users WHERE clan_id = c.clan_id) as member_count
             FROM clans c
             JOIN users u ON c.clan_owner_id = u.id
             ORDER BY c.clan_name"
        );

        return $this->view('game.clan.list', [
            'clans' => $clans,
            'user' => $this->auth()->user(),
        ]);
    }
}