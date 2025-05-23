<?php

declare(strict_types=1);

namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
use App\Models\User;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;

class RegisterController extends Controller
{
    public function showRegistrationForm(): Response
    {
        if ($this->auth()->check()) {
            return $this->redirect('/game');
        }

        return $this->view('auth.register');
    }

    public function register(Request $request): Response
    {
        $data = $request->request->all();

        $errors = $this->validate($data, [
            'username' => 'required|string|min:3|max:16|unique:users',
            'email' => 'required|email|unique:users',
            'password' => 'required|string|min:6|confirmed',
            'type' => 'required|integer|in:1,2,3',
        ]);

        if (!empty($errors)) {
            $_SESSION['_errors'] = $errors;
            $_SESSION['_old_input'] = $data;
            return $this->back();
        }

        $user = User::create([
            'username' => $data['username'],
            'email' => $data['email'],
            'password' => bcrypt($data['password']),
            'type' => (int) $data['type'],
            'activated' => 1,
            'protection' => 1,
            'attack_power' => 0,
            'defence_power' => 0,
            'cash' => 0,
            'bank' => 0,
            'clicks' => 0,
            'showonline' => 1,
            'country_id' => 1,
        ]);

        $this->auth()->login($user);

        return $this->redirect('/game');
    }
}