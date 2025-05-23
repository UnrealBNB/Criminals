<?php

declare(strict_types=1);

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;

class AdminSettingsController extends Controller
{
    public function index(): Response
    {
        $user = $this->auth()->user();

        if (!$user->hasAdminLevel(10)) {
            return $this->redirect('/game');
        }

        // Get current settings
        $settings = db()->fetchAll("SELECT * FROM settings ORDER BY setting_id");

        $settingsMap = [];
        foreach ($settings as $setting) {
            $settingsMap[$setting['setting_name']] = $setting;
        }

        return $this->view('admin.settings.index', [
            'user' => $user,
            'settings' => $settingsMap,
        ]);
    }

    public function updateTheme(Request $request): Response
    {
        $user = $this->auth()->user();

        if (!$user->hasAdminLevel(10)) {
            return $this->redirect('/game');
        }

        $availableThemes = ['begangster', 'blue', 'dark', 'modern'];

        if ($request->isMethod('GET')) {
            $currentTheme = db()->fetchColumn(
                "SELECT setting_value FROM settings WHERE setting_name = 'layout'"
            ) ?? 'begangster';

            return $this->view('admin.settings.theme', [
                'user' => $user,
                'themes' => $availableThemes,
                'currentTheme' => $currentTheme,
            ]);
        }

        $theme = $request->request->get('theme');

        if (!in_array($theme, $availableThemes)) {
            flash('error', 'Invalid theme selected');
            return $this->back();
        }

        $updated = db()->execute(
            "UPDATE settings SET setting_value = :theme WHERE setting_name = 'layout'",
            ['theme' => $theme]
        );

        if ($updated) {
            flash('success', 'Theme updated successfully');
        } else {
            flash('error', 'Failed to update theme');
        }

        return $this->back();
    }

    public function updateRules(Request $request): Response
    {
        $user = $this->auth()->user();

        if (!$user->hasAdminLevel(10)) {
            return $this->redirect('/game');
        }

        if ($request->isMethod('GET')) {
            $rules = db()->fetchColumn(
                "SELECT setting_value FROM settings WHERE setting_name = 'rules'"
            ) ?? '';

            return $this->view('admin.settings.rules', [
                'user' => $user,
                'rules' => $rules,
            ]);
        }

        $rules = $request->request->get('rules', '');

        $updated = db()->execute(
            "UPDATE settings SET setting_value = :rules WHERE setting_name = 'rules'",
            ['rules' => $rules]
        );

        if ($updated) {
            flash('success', 'Rules updated successfully');
        } else {
            flash('error', 'Failed to update rules');
        }

        return $this->back();
    }

    public function updatePrices(Request $request): Response
    {
        $user = $this->auth()->user();

        if (!$user->hasAdminLevel(10)) {
            return $this->redirect('/game');
        }

        if ($request->isMethod('GET')) {
            $prices = db()->fetchColumn(
                "SELECT setting_value FROM settings WHERE setting_name = 'price'"
            ) ?? '';

            return $this->view('admin.settings.prices', [
                'user' => $user,
                'prices' => $prices,
            ]);
        }

        $prices = $request->request->get('prices', '');

        $updated = db()->execute(
            "UPDATE settings SET setting_value = :prices WHERE setting_name = 'price'",
            ['prices' => $prices]
        );

        if ($updated) {
            flash('success', 'Prices updated successfully');
        } else {
            flash('error', 'Failed to update prices');
        }

        return $this->back();
    }

    public function gameSettings(Request $request): Response
    {
        $user = $this->auth()->user();

        if (!$user->hasAdminLevel(10)) {
            return $this->redirect('/game');
        }

        if ($request->isMethod('GET')) {
            return $this->view('admin.settings.game', [
                'user' => $user,
                'settings' => [
                    'max_clicks_per_day' => config('game.max_clicks_per_day', 50),
                    'protection_hours' => config('game.protection_hours', 11),
                    'bank_deposits_per_day' => config('game.bank_deposits_per_day', 5),
                    'max_attacks_per_target' => config('game.max_attacks_per_target', 5),
                    'attack_cooldown_seconds' => config('game.attack_cooldown_seconds', 10),
                ],
            ]);
        }

        // This would update a config file or database settings
        flash('info', 'Game settings update not implemented yet');
        return $this->back();
    }

    public function maintenance(Request $request): Response
    {
        $user = $this->auth()->user();

        if (!$user->hasAdminLevel(10)) {
            return $this->redirect('/game');
        }

        $maintenanceFile = app()->storagePath('framework/down');
        $isInMaintenance = file_exists($maintenanceFile);

        if ($request->isMethod('GET')) {
            return $this->view('admin.settings.maintenance', [
                'user' => $user,
                'isInMaintenance' => $isInMaintenance,
            ]);
        }

        $action = $request->request->get('action');

        if ($action === 'enable' && !$isInMaintenance) {
            file_put_contents($maintenanceFile, json_encode([
                'time' => time(),
                'message' => 'The game is currently under maintenance.',
                'retry' => 60,
            ]));
            flash('success', 'Maintenance mode enabled');
        } elseif ($action === 'disable' && $isInMaintenance) {
            unlink($maintenanceFile);
            flash('success', 'Maintenance mode disabled');
        }

        return $this->back();
    }
}