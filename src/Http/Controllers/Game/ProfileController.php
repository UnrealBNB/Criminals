<?php

declare(strict_types=1);

namespace App\Http\Controllers\Game;

use App\Http\Controllers\Controller;
use App\Models\User;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;

class ProfileController extends Controller
{
    public function show(?int $id = null): Response
    {
        $currentUser = $this->auth()->user();

        if ($id === null) {
            $user = $currentUser;
        } else {
            $user = User::find($id);
            if (!$user) {
                flash('error', 'User not found');
                return $this->redirect('/game');
            }
        }

        $items = $user->items();

        return $this->view('game.profile.show', [
            'user' => $user,
            'items' => $items,
            'currentUser' => $currentUser,
            'isOwnProfile' => $user->id === $currentUser->id,
        ]);
    }

    public function edit(): Response
    {
        $user = $this->auth()->user();

        return $this->view('game.profile.edit', [
            'user' => $user,
        ]);
    }

    public function update(Request $request): Response
    {
        $user = $this->auth()->user();
        $data = $request->request->all();

        $rules = [
            'website' => 'nullable|url|max:200',
            'info' => 'nullable|string|max:1000',
        ];

        // Check if password change requested
        if (!empty($data['password']) || !empty($data['password_confirmation'])) {
            $rules['password'] = 'required|string|min:6|confirmed';
        }

        $errors = $this->validate($data, $rules);

        if (!empty($errors)) {
            $_SESSION['_errors'] = $errors;
            $_SESSION['_old_input'] = $data;
            return $this->back();
        }

        // Update profile
        if (isset($data['website'])) {
            $user->website = $data['website'];
        }

        if (isset($data['info'])) {
            $user->info = $data['info'];
        }

        // Update password if provided
        if (!empty($data['password'])) {
            $user->setPassword($data['password']);
        }

        if ($user->save()) {
            flash('success', 'Profile updated successfully');
        } else {
            flash('error', 'Failed to update profile');
        }

        return $this->redirect('/game/profile/edit');
    }

    public function changeType(Request $request): Response
    {
        $user = $this->auth()->user();
        $newType = (int) $request->request->get('type');

        $errors = $this->validate(['type' => $newType], [
            'type' => 'required|integer|in:1,2,3',
        ]);

        if (!empty($errors)) {
            flash('error', 'Invalid type selected');
            return $this->back();
        }

        if ($newType === $user->type) {
            flash('error', 'You are already this type');
            return $this->back();
        }

        $user->type = $newType;

        if ($user->save()) {
            flash('success', 'Type changed successfully to ' . $user->getTypeName());
        } else {
            flash('error', 'Failed to change type');
        }

        return $this->back();
    }
}