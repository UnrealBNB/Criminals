<?php

declare(strict_types=1);

namespace App\Http\Controllers\Game\Clan;

use App\Http\Controllers\Controller;
use App\Models\User;
use App\Services\ClanService;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;

class ClanMembersController extends Controller
{
    public function __construct(
        Container $container,
        private readonly ClanService $clanService
    ) {
        parent::__construct($container);
    }

    public function index(Request $request): Response
    {
        $user = $this->auth()->user();
        $clan = $user->clan();

        if (!$clan) {
            return $this->redirect('/game/clan');
        }

        $sort = $request->query->get('sort', 'username');
        $validSorts = ['username', 'attack_power', 'clan_level'];

        if (!in_array($sort, $validSorts)) {
            $sort = 'username';
        }

        $members = db()->fetchAll(
            "SELECT * FROM users 
             WHERE clan_id = :clan_id 
             ORDER BY {$sort}",
            ['clan_id' => $clan->clan_id]
        );

        return $this->view('game.clan.members.index', [
            'user' => $user,
            'clan' => $clan,
            'members' => $members,
            'sort' => $sort,
        ]);
    }

    public function kick(Request $request, int $id): Response
    {
        $user = $this->auth()->user();

        if (!$user->hasClanLevel(8)) {
            flash('error', 'Insufficient permissions');
            return $this->back();
        }

        $targetUser = User::find($id);

        if (!$targetUser || $targetUser->clan_id !== $user->clan_id) {
            flash('error', 'Invalid target');
            return $this->back();
        }

        if ($targetUser->clan_level >= $user->clan_level) {
            flash('error', 'Cannot kick members with equal or higher rank');
            return $this->back();
        }

        $result = $this->clanService->kickMember($user, $targetUser);

        if ($result['success']) {
            flash('success', "Kicked {$targetUser->username} from the clan");
        } else {
            flash('error', $result['message']);
        }

        return $this->back();
    }

    public function promote(Request $request): Response
    {
        $user = $this->auth()->user();

        if (!$user->hasClanLevel(9)) {
            flash('error', 'Insufficient permissions');
            return $this->back();
        }

        $targetId = (int) $request->request->get('user_id');
        $newLevel = (int) $request->request->get('level');

        $targetUser = User::find($targetId);

        if (!$targetUser || $targetUser->clan_id !== $user->clan_id) {
            flash('error', 'Invalid target');
            return $this->back();
        }

        if ($newLevel >= $user->clan_level) {
            flash('error', 'Cannot promote to equal or higher rank than yourself');
            return $this->back();
        }

        $result = $this->clanService->promoteMember($user, $targetUser, $newLevel);

        if ($result['success']) {
            flash('success', "Updated {$targetUser->username}'s clan rank");
        } else {
            flash('error', $result['message']);
        }

        return $this->back();
    }

    public function applications(): Response
    {
        $user = $this->auth()->user();

        if (!$user->hasClanLevel(5)) {
            flash('error', 'Insufficient permissions');
            return $this->redirect('/game/clan');
        }

        $clan = $user->clan();
        $applications = $clan->getPendingApplications();

        return $this->view('game.clan.members.applications', [
            'user' => $user,
            'clan' => $clan,
            'applications' => $applications,
        ]);
    }

    public function acceptApplication(Request $request, int $id): Response
    {
        $user = $this->auth()->user();

        if (!$user->hasClanLevel(5)) {
            flash('error', 'Insufficient permissions');
            return $this->back();
        }

        $applicant = User::find($id);
        $result = $this->clanService->acceptApplication($user, $applicant);

        if ($result['success']) {
            flash('success', "Accepted {$applicant->username} into the clan");
        } else {
            flash('error', $result['message']);
        }

        return $this->back();
    }

    public function rejectApplication(Request $request, int $id): Response
    {
        $user = $this->auth()->user();

        if (!$user->hasClanLevel(5)) {
            flash('error', 'Insufficient permissions');
            return $this->back();
        }

        $applicant = User::find($id);
        $result = $this->clanService->rejectApplication($user, $applicant);

        if ($result['success']) {
            flash('success', "Rejected application");
        } else {
            flash('error', $result['message']);
        }

        return $this->back();
    }

    public function changeOwner(Request $request): Response
    {
        $user = $this->auth()->user();

        if (!$user->isClanOwner()) {
            flash('error', 'Only clan owners can transfer ownership');
            return $this->back();
        }

        if ($request->isMethod('GET')) {
            $members = db()->fetchAll(
                "SELECT * FROM users 
                 WHERE clan_id = :clan_id AND id != :user_id",
                ['clan_id' => $user->clan_id, 'user_id' => $user->id]
            );

            return $this->view('game.clan.members.change-owner', [
                'user' => $user,
                'clan' => $user->clan(),
                'members' => $members,
            ]);
        }

        $newOwnerId = (int) $request->request->get('new_owner');
        $newOwner = User::find($newOwnerId);

        if (!$newOwner || $newOwner->clan_id !== $user->clan_id) {
            flash('error', 'Invalid selection');
            return $this->back();
        }

        $result = $this->clanService->transferOwnership($user, $newOwner);

        if ($result['success']) {
            flash('success', 'Ownership transferred successfully');
            return $this->redirect('/game/clan');
        }

        flash('error', $result['message']);
        return $this->back();
    }
}