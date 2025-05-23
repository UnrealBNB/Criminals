<?php $user = auth()->user(); ?>
<div class="user-stats">
    <h3>Your Stats</h3>
    <table>
        <tr>
            <td>Type:</td>
            <td><?= $this->e($user->getTypeName()) ?></td>
        </tr>
        <tr>
            <td>Rank:</td>
            <td><?= $this->e($user->getRank()) ?></td>
        </tr>
        <tr>
            <td>Cash:</td>
            <td>€<?= number_format($user->cash) ?></td>
        </tr>
        <tr>
            <td>Bank:</td>
            <td>€<?= number_format($user->bank) ?></td>
        </tr>
        <tr>
            <td>Attack:</td>
            <td><?= number_format($user->getTotalAttackPower()) ?></td>
        </tr>
        <tr>
            <td>Defense:</td>
            <td><?= number_format($user->getTotalDefencePower()) ?></td>
        </tr>
        <tr>
            <td>Clicks:</td>
            <td><?= number_format($user->clicks) ?></td>
        </tr>
        <?php if ($user->isProtected()): ?>
            <tr>
                <td colspan="2" class="protected">Under Protection</td>
            </tr>
        <?php endif; ?>
    </table>
</div>