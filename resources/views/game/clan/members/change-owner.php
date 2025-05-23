<?php $this->extends('layouts.game') ?>

<?php $this->section('game_content') ?>
    <div class="clan-change-owner">
        <h1>Transfer Clan Ownership</h1>

        <div class="warning-box">
            <p><strong>Warning!</strong></p>
            <p>This will transfer complete ownership of <?= $this->e($clan->clan_name) ?> to another member.</p>
            <p>You will become a regular member after the transfer.</p>
        </div>

        <form method="POST" action="/game/clan/members/change-owner">
            <?= $this->csrf() ?>

            <div class="form-group">
                <label for="new_owner">Select New Owner:</label>
                <select name="new_owner" id="new_owner" required>
                    <option value="">Choose a member</option>
                    <?php foreach ($members as $member): ?>
                        <option value="<?= $member['id'] ?>">
                            <?= $this->e($member['username']) ?> (Level <?= $member['clan_level'] ?>)
                        </option>
                    <?php endforeach; ?>
                </select>
            </div>

            <button type="submit" class="btn btn-warning">Transfer Ownership</button>
            <a href="/game/clan/overview" class="btn btn-secondary">Cancel</a>
        </form>
    </div>
<?php $this->endSection() ?>