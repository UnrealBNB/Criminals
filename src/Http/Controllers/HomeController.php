<?php

declare(strict_types=1);

namespace App\Http\Controllers;

use Symfony\Component\HttpFoundation\Response;

class HomeController extends Controller
{
    public function index(): Response
    {
        if ($this->auth()->check()) {
            return $this->redirect('/game');
        }

        return $this->view('home.index');
    }
}