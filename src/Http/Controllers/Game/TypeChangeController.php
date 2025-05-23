<?php

declare(strict_types=1);

namespace App\Http\Controllers\Game;

use App\Http\Controllers\Controller;
use App\Enums\UserType;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;

class TypeChangeController extends Controller
{
    public function index(): Response
    {
        $user = $this->auth()->user();

        return $this->view('game.type-change', [
            'user' => $user,
            'types' => UserType::options(),
        ]);
    }

    public function change(Request $request): Response
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

        $type = UserType::from($newType);
        $user->type = $newType;

        if ($user->save()) {
            flash('success', 'Successfully changed to ' . $type->label() . '!');
        } else {
            flash('error', 'Failed to change type');
        }

        return $this->back();
    }
}