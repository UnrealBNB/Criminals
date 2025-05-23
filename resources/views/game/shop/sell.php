<?php $this->extends('layouts.game') ?>

<?php $this->section('game_content') ?>
    <div class="shop-sell">
        <h1>Sell Items</h1>

        <?php if (empty($items)): ?>
            <p>You have no items to sell.</p>
        <?php else: ?>
            <form method="POST" action="/game/shop/sell">
                <?= $this->csrf() ?>

                <table class="shop-items">
                    <thead>
                    <tr>
                        <th>Item</th>
                        <th>Type</th>
                        <th>Owned</th>
                        <th>Sell Price</th>
                        <th>Sell Quantity</th>
                    </tr>
                    </thead>
                    <tbody>
                    <?php foreach ($items as $item): ?>
                        <tr>
                            <td><?= $this->e($item['item_name']) ?></td>
                            <td><?= ['', 'Weapons', 'Protection', 'Defense', 'Accessories'][$item['item_area']] ?></td>
                            <td><?= $item['item_count'] ?></td>
                            <td>€<?= number_format($item['item_sell']) ?></td>
                            <td>
                                <input type="number"
                                       name="sell<?= $item['item_id'] ?>"
                                       min="0"
                                       max="<?= $item['item_count'] ?>"
                                       value="0">
                            </td>
                        </tr>
                    <?php endforeach; ?>
                    </tbody>
                </table>

                <div class="shop-actions">
                    <p>Total value will be calculated upon submission.</p>
                    <button type="submit" class="btn btn-primary">Sell Selected</button>
                    <a href="/game/shop" class="btn btn-secondary">Back to Shop</a>
                </div>
            </form>
        <?php endif; ?>
    </div>
<?php $this->endSection() ?>