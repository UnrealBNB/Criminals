<?php

declare(strict_types=1);

namespace App\Services;

use App\Models\Click;
use App\Models\User;

class ClickService
{
    private array $clickTexts = [
        1 => [
            'intro' => '%username% gives you a plastic bag...',
            'resultText' => 'You take the bag and see white powder inside. When you smell this powder, a strange feeling comes over you. After a few minutes the feeling goes away, but you want more... You are addicted!',
            'type' => 'junkies'
        ],
        2 => [
            'intro' => '%username% asks if he can do a test with you...',
            'resultText' => 'You go inside, but suddenly you feel a hard blow to the back of your head. When you wake up, you are tied to a chair, and across from you lies someone else. When you look closer, it turns out to be you! You have been cloned!',
            'type' => 'clones'
        ],
        3 => [
            'intro' => '%username% gives you a form...',
            'resultText' => 'Immediately after filling out the form, you are blindfolded and thrown into a car. After a few hours of driving, the blindfold is removed, and you see that you are at a training base!',
            'type' => 'agents'
        ],
    ];

    public function getClickText(User $user): array
    {
        $text = $this->clickTexts[$user->type] ?? $this->clickTexts[1];

        return [
            'intro' => str_replace('%username%', $user->username, $text['intro']),
            'resultText' => $text['resultText'],
            'type' => $text['type'],
        ];
    }

    public function processClick(User $targetUser, string $ip): array
    {
        // Check if already clicked
        if (Click::hasClickedToday($targetUser->id, $ip)) {
            return [
                'success' => false,
                'message' => 'You have already clicked today!'
            ];
        }

        // Check daily limit
        if ($targetUser->clicks_today >= config('game.max_clicks_per_day', 50)) {
            return [
                'success' => false,
                'message' => $targetUser->username . ' has already received enough clicks today!'
            ];
        }

        // Process the click
        db()->beginTransaction();

        try {
            // Record click
            Click::recordClick($targetUser->id, $ip);

            // Update user stats
            $targetUser->clicks++;
            $targetUser->clicks_today++;
            $targetUser->save();

            $text = $this->getClickText($targetUser);

            db()->commit();

            return [
                'success' => true,
                'resultText' => $text['resultText'],
                'clickType' => $text['type'],
            ];
        } catch (\Throwable $e) {
            db()->rollBack();
            return [
                'success' => false,
                'message' => 'An error occurred processing your click.'
            ];
        }
    }
}