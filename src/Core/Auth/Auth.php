<?php

declare(strict_types=1);

namespace App\Core\Auth;

use App\Core\Database\Database;
use App\Models\User;

class Auth
{
    private ?User $user = null;
    private string $sessionKey = 'user_id';

    public function __construct(
        private readonly Database $db
    ) {
        $this->checkSession();
    }

    public function attempt(string $username, string $password): bool
    {
        $userData = $this->db->fetchOne(
            "SELECT * FROM users WHERE username = :username AND activated = 1",
            ['username' => $username]
        );

        if (!$userData) {
            return false;
        }

        $user = new User($userData);

        if (!$user->verifyPassword($password)) {
            return false;
        }

        return $this->login($user);
    }

    public function login(User $user): bool
    {
        $sessionId = bin2hex(random_bytes(32));

        $user->session_id = $sessionId;
        $user->online_time = new \DateTime();
        $user->save();

        $_SESSION[$this->sessionKey] = $user->id;
        $_SESSION['session_id'] = $sessionId;

        setcookie('game_session_id', $sessionId, [
            'expires' => time() + 86400,
            'path' => '/',
            'httponly' => true,
            'samesite' => 'Lax',
            'secure' => $_SERVER['HTTPS'] ?? false
        ]);

        $this->user = $user;
        return true;
    }

    public function logout(): void
    {
        if ($this->user) {
            $this->user->session_id = null;
            $this->user->save();
        }

        unset($_SESSION[$this->sessionKey]);
        unset($_SESSION['session_id']);

        setcookie('game_session_id', '', [
            'expires' => time() - 3600,
            'path' => '/',
            'httponly' => true
        ]);

        $this->user = null;
    }

    public function check(): bool
    {
        return $this->user !== null;
    }

    public function guest(): bool
    {
        return !$this->check();
    }

    public function user(): ?User
    {
        return $this->user;
    }

    public function id(): ?int
    {
        return $this->user?->id;
    }

    private function checkSession(): void
    {
        if (isset($_COOKIE['game_session_id'])) {
            $sessionId = $_COOKIE['game_session_id'];

            $userData = $this->db->fetchOne(
                "SELECT * FROM users WHERE session_id = :session_id",
                ['session_id' => $sessionId]
            );

            if ($userData) {
                $this->user = new User($userData);
                $_SESSION[$this->sessionKey] = $this->user->id;

                // Update online time
                $this->user->updateOnlineTime();
            }
        } elseif (isset($_SESSION[$this->sessionKey])) {
            $user = User::find($_SESSION[$this->sessionKey]);
            if ($user) {
                $this->user = $user;
            }
        }
    }
}