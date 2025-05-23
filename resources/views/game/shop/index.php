<?php $this->extends('layouts.game') ?>

<?php $this->section('game_content') ?>
    <div class="shop">
        <h1>Shop</h1>

        <div class="shop-tabs">
            <a href="?tab=weapons" class="<?= $currentTab === 'weapons' ? 'active' : '' ?>">Weapons</a>
            <a href="?tab=protection" class="<?= $currentTab === 'protection' ? 'active' : '' ?>">Protection</a>
            <a href="?tab=defense" class="<?= $currentTab === 'defense' ? 'active' : '' ?>">Defense</a>
            <a href="?tab=accessories" class="<?= $currentTab === 'accessories' ? 'active' : '' ?>">Accessories</a>
            <a href="?tab=special" class="<?= $currentTab === 'special' ? 'active' : '' ?>">Special</a>
        </div>

        <form method="POST" action="/game/shop/buy">
            <?= $this->csrf() ?>

            <table class="shop-items">
                <thead>
                <tr>
                    <th>Item</th>
                    <th>Attack</th>
                    <th>Defense</th>
                    <th>Price</th>
                    <th>Owned</th>
                    <th>Buy</th>
                </tr>
                </thead>
                <tbody>
                <?php foreach ($items as $item): ?>
                    <tr>
                        <td><?= $this->e($item->item_name) ?></td>
                        <td><?= $item->item_attack_power ?></td>
                        <td><?= $item->item_defence_power ?></td>
                        <td>€<?= number_format($item->item_costs) ?></td>
                        <td><?= $userItems[$item->item_id] ?? 0 ?></td>
                        <td>
                            <input type="number"
                                   name="buy<?= $item->item_id ?>"
                                   min="0"
                                   max="99"
                                   value="0">
                        </td>
                    </tr>
                <?php endforeach; ?>
                </tbody>
            </table>

            <div class="shop-actions">
                <p>Your Cash: €<?= number_format($user->cash) ?></p>
                <button type="submit" class="btn btn-primary">Buy Selected</button>
                <a href="/game/shop/sell" class="btn btn-secondary">Sell Items</a>
            </div>
        </form>
    </div>
<?php $this->endSection() ?>