<?php

declare(strict_types=1);

namespace App\Http\Controllers\Game\Clan;

use App\Http\Controllers\Controller;
use App\Models\Item;
use App\Services\ClanService;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;

class ClanShopController extends Controller
{
    public function __construct(
        Container $container,
        private readonly ClanService $clanService
    ) {
        parent::__construct($container);
    }

    public function index(Request $request): Response
    {
        $user = $this->auth()->user();

        if (!$user->hasClanLevel(7)) {
            flash('error', 'Insufficient clan permissions');
            return $this->redirect('/game/clan');
        }

        $clan = $user->clan();
        $items = Item::getClanShopItems($clan->clan_type);

        // Get clan's current items
        $clanItems = db()->fetchAll(
            "SELECT item_id, item_count FROM clan_items WHERE clan_id = :clan_id",
            ['clan_id' => $clan->clan_id]
        );

        $clanItemsMap = [];
        foreach ($clanItems as $item) {
            $clanItemsMap[$item['item_id']] = $item['item_count'];
        }

        return $this->view('game.clan.shop.index', [
            'user' => $user,
            'clan' => $clan,
            'items' => $items,
            'clanItems' => $clanItemsMap,
        ]);
    }

    public function buy(Request $request): Response
    {
        $user = $this->auth()->user();

        if (!$user->hasClanLevel(7)) {
            flash('error', 'Insufficient clan permissions');
            return $this->back();
        }

        $clan = $user->clan();
        $purchases = [];
        $totalCost = 0;

        foreach ($request->request->all() as $key => $value) {
            if (str_starts_with($key, 'buy') && $value > 0) {
                $itemId = (int) str_replace('buy', '', $key);
                $quantity = (int) $value;

                $item = Item::find($itemId);

                if (!$item || !$item->isClanItem()) {
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

        if (empty($purchases)) {
            flash('error', 'No items selected');
            return $this->back();
        }

        if ($totalCost > $clan->cash) {
            flash('error', 'Clan cannot afford this purchase');
            return $this->back();
        }

        $result = $this->clanService->buyItems($user, $purchases);

        if ($result['success']) {
            flash('success', 'Purchase successful!');
        } else {
            flash('error', $result['message']);
        }

        return $this->back();
    }

    public function sell(Request $request): Response
    {
        $user = $this->auth()->user();

        if (!$user->hasClanLevel(7)) {
            flash('error', 'Insufficient clan permissions');
            return $this->redirect('/game/clan');
        }

        if ($request->isMethod('GET')) {
            return $this->showSellPage();
        }

        $clan = $user->clan();
        $sales = [];
        $totalRevenue = 0;

        foreach ($request->request->all() as $key => $value) {
            if (str_starts_with($key, 'sell') && $value > 0) {
                $itemId = (int) str_replace('sell', '', $key);
                $quantity = (int) $value;

                $item = Item::find($itemId);

                if (!$item) {
                    flash('error', 'Invalid item selection');
                    return $this->back();
                }

                // Check if clan has the item
                $hasItem = db()->fetchOne(
                    "SELECT item_count FROM clan_items 
                     WHERE clan_id = :clan_id AND item_id = :item_id",
                    ['clan_id' => $clan->clan_id, 'item_id' => $itemId]
                );

                if (!$hasItem || $hasItem['item_count'] < $quantity) {
                    flash('error', "Clan doesn't have enough {$item->item_name} to sell");
                    return $this->back();
                }

                // Houses cannot be sold
                if ($itemId === 27) {
                    flash('error', 'Houses cannot be sold');
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

        if (empty($sales)) {
            flash('error', 'No items selected');
            return $this->back();
        }

        $result = $this->clanService->sellItems($user, $sales);

        if ($result['success']) {
            flash('success', 'Items sold successfully!');
        } else {
            flash('error', $result['message']);
        }

        return $this->back();
    }

    private function showSellPage(): Response
    {
        $user = $this->auth()->user();
        $clan = $user->clan();

        $clanItems = db()->fetchAll(
            "SELECT i.*, ci.item_count 
             FROM clan_items ci 
             JOIN items i ON ci.item_id = i.item_id 
             WHERE ci.clan_id = :clan_id 
             AND i.item_area BETWEEN 8 AND 11
             ORDER BY i.item_area, i.item_name",
            ['clan_id' => $clan->clan_id]
        );

        return $this->view('game.clan.shop.sell', [
            'user' => $user,
            'clan' => $clan,
            'items' => $clanItems,
        ]);
    }
}