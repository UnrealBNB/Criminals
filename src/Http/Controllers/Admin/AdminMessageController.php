<?php

declare(strict_types=1);

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Message;
use App\Models\User;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;

class AdminMessageController extends Controller
{
    public function index(): Response
    {
        $user = $this->auth()->user();

        if (!$user->hasAdminLevel(3)) {
            return $this->redirect('/game');
        }

        return $this->view('admin.messages.index', [
            'user' => $user,
        ]);
    }

    public function sendMass(Request $request): Response
    {
        $user = $this->auth()->user();

        if (!$user->hasAdminLevel(3)) {
            return $this->redirect('/game');
        }

        if ($request->isMethod('GET')) {
            return $this->view('admin.messages.mass', [
                'user' => $user,
            ]);
        }

        $data = $request->request->all();

        $errors = $this->validate($data, [
            'subject' => 'required|string|max:250',
            'message' => 'required|string|max:2000',
        ]);

        if (!empty($errors)) {
            $_SESSION['_errors'] = $errors;
            $_SESSION['_old_input'] = $data;
            return $this->back();
        }

        // Get all active users
        $users = User::query()
            ->where('activated', 1)
            ->get();

        $sent = 0;
        foreach ($users as $targetUser) {
            if (Message::send($user->id, $targetUser->id, $data['subject'], $data['message'])) {
                $sent++;
            }
        }

        flash('success', "Message sent to {$sent} users");
        return $this->redirect('/admin/messages');
    }

    public function sendToType(Request $request): Response
    {
        $user = $this->auth()->user();

        if (!$user->hasAdminLevel(3)) {
            return $this->redirect('/game');
        }

        if ($request->isMethod('GET')) {
            return $this->view('admin.messages.type', [
                'user' => $user,
            ]);
        }

        $data = $request->request->all();

        $errors = $this->validate($data, [
            'type' => 'required|integer|in:1,2,3',
            'subject' => 'required|string|max:250',
            'message' => 'required|string|max:2000',
        ]);

        if (!empty($errors)) {
            $_SESSION['_errors'] = $errors;
            $_SESSION['_old_input'] = $data;
            return $this->back();
        }

        // Get users of specific type
        $users = User::query()
            ->where('activated', 1)
            ->where('type', $data['type'])
            ->get();

        $sent = 0;
        foreach ($users as $targetUser) {
            if (Message::send($user->id, $targetUser->id, $data['subject'], $data['message'])) {
                $sent++;
            }
        }

        flash('success', "Message sent to {$sent} users");
        return $this->redirect('/admin/messages');
    }

    public function sendToCountry(Request $request): Response
    {
        $user = $this->auth()->user();

        if (!$user->hasAdminLevel(3)) {
            return $this->redirect('/game');
        }

        if ($request->isMethod('GET')) {
            $countries = config('game.countries', []);

            return $this->view('admin.messages.country', [
                'user' => $user,
                'countries' => $countries,
            ]);
        }

        $data = $request->request->all();

        $errors = $this->validate($data, [
            'country' => 'required|integer|min:1|max:7',
            'subject' => 'required|string|max:250',
            'message' => 'required|string|max:2000',
        ]);

        if (!empty($errors)) {
            $_SESSION['_errors'] = $errors;
            $_SESSION['_old_input'] = $data;
            return $this->back();
        }

        // Get users in specific country
        $users = User::query()
            ->where('activated', 1)
            ->where('country_id', $data['country'])
            ->get();

        $sent = 0;
        foreach ($users as $targetUser) {
            if (Message::send($user->id, $targetUser->id, $data['subject'], $data['message'])) {
                $sent++;
            }
        }

        flash('success', "Message sent to {$sent} users");
        return $this->redirect('/admin/messages');
    }
}