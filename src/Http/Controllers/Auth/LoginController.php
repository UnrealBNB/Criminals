<?php

declare(strict_types=1);

namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;

class LoginController extends Controller
{
    public function showLoginForm(): Response
    {
        if ($this->auth()->check()) {
            return $this->redirect('/game');
        }

        return $this->view('auth.login');
    }

    public function login(Request $request): Response
    {
        $credentials = $request->request->all();

        $errors = $this->validate($credentials, [
            'username' => 'required|string',
            'password' => 'required|string',
        ]);

        if (!empty($errors)) {
            $_SESSION['_errors'] = $errors;
            $_SESSION['_old_input'] = $credentials;
            return $this->back();
        }

        if ($this->auth()->attempt($credentials['username'], $credentials['password'])) {
            unset($_SESSION['_errors'], $_SESSION['_old_input']);
            return $this->redirect('/game');
        }

        $_SESSION['_errors'] = ['login' => 'Invalid username or password'];
        $_SESSION['_old_input'] = ['username' => $credentials['username']];

        return $this->back();
    }

    public function logout(): Response
    {
        $this->auth()->logout();
        return $this->redirect('/');
    }
}