<?php $this->extends('layouts.game') ?>

<?php $this->section('game_content') ?>
    <div class="clan-shop">
        <h1>Clan Shop</h1>

        <div class="shop-info">
            <p>Clan Cash: €<?= number_format($clan->cash) ?></p>
            <p>Only members with Shop Manager role (Level 7+) can buy items.</p>
        </div>

        <form method="POST" action="/game/clan/shop/buy">
            <?= $this->csrf() ?>

            <table class="shop-items">
                <thead>
                <tr>
                    <th>Item</th>
                    <th>Type</th>
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
                        <td><?= $this->e($item->getAreaName()) ?></td>
                        <td><?= $item->item_attack_power ?></td>
                        <td><?= $item->item_defence_power ?></td>
                        <td>€<?= number_format($item->item_costs) ?></td>
                        <td><?= $clanItems[$item->item_id] ?? 0 ?></td>
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
                <button type="submit" class="btn btn-primary">Buy Selected</button>
                <a href="/game/clan/shop/sell" class="btn btn-secondary">Sell Items</a>
            </div>
        </form>
    </div>
<?php $this->endSection() ?>