<?php $this->extends('layouts.game') ?>

<?php $this->section('game_content') ?>
    <div class="profile">
        <h1><?= $this->e($user->username) ?>'s Profile</h1>

        <div class="profile-sections">
            <div class="section">
                <h2>Player Information</h2>
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
                        <td>Country:</td>
                        <td><?= $this->e($user->getCountryName()) ?></td>
                    </tr>
                    <tr>
                        <td>Attack Power:</td>
                        <td><?= number_format($user->getTotalAttackPower()) ?></td>
                    </tr>
                    <tr>
                        <td>Defence Power:</td>
                        <td><?= number_format($user->getTotalDefencePower()) ?></td>
                    </tr>
                    <tr>
                        <td>Clicks:</td>
                        <td><?= number_format($user->clicks) ?></td>
                    </tr>
                    <?php if ($user->isInClan()): ?>
                        <tr>
                            <td>Clan:</td>
                            <td><?= $this->e($user->clan()->clan_name) ?></td>
                        </tr>
                    <?php endif; ?>
                </table>
            </div>

            <div class="section">
                <h2>Statistics</h2>
                <table>
                    <tr>
                        <td>Attacks Won:</td>
                        <td><?= number_format($user->attacks_won) ?></td>
                    </tr>
                    <tr>
                        <td>Attacks Lost:</td>
                        <td><?= number_format($user->attacks_lost) ?></td>
                    </tr>
                    <tr>
                        <td>Win Rate:</td>
                        <td>
                            <?php
                            $total = $user->attacks_won + $user->attacks_lost;
                            echo $total > 0 ? round(($user->attacks_won / $total) * 100, 1) . '%' : 'N/A';
                            ?>
                        </td>
                    </tr>
                </table>
            </div>

            <?php if ($user->website || $user->info): ?>
                <div class="section">
                    <h2>About</h2>
                    <?php if ($user->website): ?>
                        <p><strong>Website:</strong> <a href="<?= $this->e($user->website) ?>" target="_blank" rel="noopener"><?= $this->e($user->website) ?></a></p>
                    <?php endif; ?>
                    <?php if ($user->info): ?>
                        <div class="user-info">
                            <?= nl2br($this->e($user->info)) ?>
                        </div>
                    <?php endif; ?>
                </div>
            <?php endif; ?>
        </div>

        <div class="profile-actions">
            <?php if ($isOwnProfile): ?>
                <a href="/game/profile/edit" class="btn btn-primary">Edit Profile</a>
                <p><strong>Your Click Link:</strong></p>
                <input type="text" value="<?= $this->url('click/' . $user->id) ?>" readonly onclick="this.select()">
            <?php else: ?>
                <?php if ($currentUser->type !== $user->type && !$user->isProtected()): ?>
                    <a href="/game/attack/<?= $user->id ?>" class="btn btn-danger">Attack</a>
                <?php endif; ?>
                <a href="/game/messages/compose/<?= $user->id ?>" class="btn btn-primary">Send Message</a>
                <a href="/game/donate?to=<?= $this->e($user->username) ?>" class="btn btn-success">Donate Money</a>
            <?php endif; ?>
        </div>
    </div>
<?php $this->endSection() ?>