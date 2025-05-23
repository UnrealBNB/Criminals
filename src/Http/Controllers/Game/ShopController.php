<?php

declare(strict_types=1);

namespace App\Http\Controllers\Game;

use App\Http\Controllers\Controller;
use App\Models\Item;
use App\Models\User;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;

class ShopController extends Controller
{
    public function index(Request $request): Response
    {
        $user = $this->auth()->user();
        $tab = $request->query->get('tab', 'weapons');

        $area = match($tab) {
            'weapons' => Item::AREA_WEAPONS,
            'protection' => Item::AREA_PROTECTION,
            'defense' => Item::AREA_DEFENSE,
            'accessories' => Item::AREA_ACCESSORIES,
            'special' => $this->getSpecialArea($user),
            default => Item::AREA_WEAPONS,
        };

        $items = Item::getByArea($area);

        // Get user's current items
        $userItems = db()->fetchAll(
            "SELECT item_id, item_count FROM user_items WHERE user_id = :user_id",
            ['user_id' => $user->id]
        );

        $userItemsMap = [];
        foreach ($userItems as $userItem) {
            $userItemsMap[$userItem['item_id']] = $userItem['item_count'];
        }

        return $this->view('game.shop.index', [
            'items' => $items,
            'userItems' => $userItemsMap,
            'currentTab' => $tab,
            'user' => $user,
        ]);
    }

    public function buy(Request $request): Response
    {
        $user = $this->auth()->user();
        $purchases = [];
        $totalCost = 0;

        // Collect all purchase requests
        foreach ($request->request->all() as $key => $value) {
            if (str_starts_with($key, 'buy') && $value > 0) {
                $itemId = (int) str_replace('buy', '', $key);
                $quantity = (int) $value;

                $item = Item::find($itemId);
                if (!$item || !$item->canBePurchasedByType($user->type)) {
                    flash('error', 'Invalid item selection');
                    return $this->back();
                }

                $cost = $item->item_costs * $quantity;
                $totalCost += $cost;

                $purchases[] = [
                    'item' => $item,
                    'quantity' => $quantity,
                    'cost' => $cost,
                ];
            }
        }

        // Validate total cost
        if ($totalCost > $user->cash) {
            flash('error', 'You cannot afford this purchase');
            return $this->back();
        }

        // Process purchases
        db()->beginTransaction();

        try {
            foreach ($purchases as $purchase) {
                $item = $purchase['item'];
                $quantity = $purchase['quantity'];

                // Give item to user
                $user->giveItem($item->item_id, $quantity);

                // Update user stats
                $user->attack_power += $item->item_attack_power * $quantity;
                $user->defence_power += $item->item_defence_power * $quantity;
            }

            // Deduct cash
            $user->cash -= $totalCost;
            $user->save();

            db()->commit();
            flash('success', 'Purchase successful!');
        } catch (\Throwable $e) {
            db()->rollBack();
            flash('error', 'Purchase failed. Please try again.');
        }

        return $this->back();
    }

    public function sell(Request $request): Response
    {
        if ($request->isMethod('GET')) {
            return $this->showSellPage();
        }

        $user = $this->auth()->user();
        $sales = [];
        $totalRevenue = 0;

        // Collect all sell requests
        foreach ($request->request->all() as $key => $value) {
            if (str_starts_with($key, 'sell') && $value > 0) {
                $itemId = (int) str_replace('sell', '', $key);
                $quantity = (int) $value;

                $item = Item::find($itemId);
                if (!$item) {
                    flash('error', 'Invalid item selection');
                    return $this->back();
                }

                // Check if user has the item
                if (!$user->hasItem($itemId, $quantity)) {
                    flash('error', "You don't have enough {$item->item_name} to sell");
                    return $this->back();
                }

                $revenue = $item->item_sell * $quantity;
                $totalRevenue += $revenue;

                $sales[] = [
                    'item' => $item,
                    'quantity' => $quantity,
                    'revenue' => $revenue,
                ];
            }
        }

        // Process sales
        db()->beginTransaction();

        try {
            foreach ($sales as $sale) {
                $item = $sale['item'];
                $quantity = $sale['quantity'];

                // Remove item from user
                $user->removeItem($item->item_id, $quantity);

                // Update user stats
                $user->attack_power -= $item->item_attack_power * $quantity;
                $user->defence_power -= $item->item_defence_power * $quantity;
            }

            // Add cash
            $user->cash += $totalRevenue;
            $user->save();

            db()->commit();
            flash('success', 'Items sold successfully!');
        } catch (\Throwable $e) {
            db()->rollBack();
            flash('error', 'Sale failed. Please try again.');
        }

        return $this->back();
    }

    private function showSellPage(): Response
    {
        $user = $this->auth()->user();

        // Get user's items
        $userItems = db()->fetchAll(
            "SELECT i.*, ui.item_count 
             FROM user_items ui 
             JOIN items i ON ui.item_id = i.item_id 
             WHERE ui.user_id = :user_id 
             AND i.item_area BETWEEN 1 AND 4
             ORDER BY i.item_area, i.item_name",
            ['user_id' => $user->id]
        );

        return $this->view('game.shop.sell', [
            'items' => $userItems,
            'user' => $user,
        ]);
    }

    private function getSpecialArea(User $user): int
    {
        return match($user->type) {
            2 => Item::AREA_SCIENTIST_SPECIAL,
            3 => Item::AREA_POLICE_SPECIAL,
            default => Item::AREA_SPECIAL,
        };
    }
}