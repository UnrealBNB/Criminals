<?php $this->extends('layouts.game') ?>

<?php $this->section('game_content') ?>
    <div class="clan-members">
        <h1>Clan Members</h1>

        <div class="members-controls">
            <form method="GET" action="/game/clan/members">
                <select name="sort" onchange="this.form.submit()">
                    <option value="username" <?= $sort === 'username' ? 'selected' : '' ?>>Username</option>
                    <option value="attack_power" <?= $sort === 'attack_power' ? 'selected' : '' ?>>Attack Power</option>
                    <option value="clan_level" <?= $sort === 'clan_level' ? 'selected' : '' ?>>Clan Level</option>
                </select>
            </form>
        </div>

        <table class="members-table">
            <thead>
            <tr>
                <th>Username</th>
                <th>Type</th>
                <th>Attack Power</th>
                <th>Clan Level</th>
                <th>Online</th>
                <?php if ($user->hasClanLevel(8)): ?>
                    <th>Actions</th>
                <?php endif; ?>
            </tr>
            </thead>
            <tbody>
            <?php foreach ($members as $member): ?>
                <tr>
                    <td>
                        <a href="/game/profile/<?= $member['id'] ?>">
                            <?= $this->e($member['username']) ?>
                        </a>
                    </td>
                    <td><?= ['', 'Drug Dealer', 'Scientist', 'Police'][$member['type']] ?></td>
                    <td><?= number_format($member['attack_power'] + ($member['clicks'] * 5)) ?></td>
                    <td><?= $member['clan_level'] ?></td>
                    <td><?= (time() - strtotime($member['online_time'])) < 300 ? 'Yes' : 'No' ?></td>
                    <?php if ($user->hasClanLevel(8) && $member['id'] !== $user->id && $member['clan_level'] < $user->clan_level): ?>
                        <td>
                            <form method="POST" action="/game/clan/members/kick/<?= $member['id'] ?>" style="display: inline;">
                                <?= $this->csrf() ?>
                                <button type="submit" class="btn-small btn-danger" onclick="return confirm('Kick this member?')">Kick</button>
                            </form>
                            <?php if ($user->hasClanLevel(9)): ?>
                                <button onclick="showPromoteDialog(<?= $member['id'] ?>, '<?= $this->e($member['username']) ?>')" class="btn-small">Promote</button>
                            <?php endif; ?>
                        </td>
                    <?php else: ?>
                        <td>-</td>
                    <?php endif; ?>
                </tr>
            <?php endforeach; ?>
            </tbody>
        </table>
    </div>

<?php if ($user->hasClanLevel(9)): ?>
    <div id="promoteDialog" style="display: none;">
        <form method="POST" action="/game/clan/members/promote">
            <?= $this->csrf() ?>
            <input type="hidden" name="user_id" id="promoteUserId">

            <h3>Promote: <span id="promoteUsername"></span></h3>

            <div class="form-group">
                <label for="level">New Level:</label>
                <select name="level" id="level" required>
                    <?php for ($i = 1; $i < $user->clan_level; $i++): ?>
                        <option value="<?= $i ?>">Level <?= $i ?></option>
                    <?php endfor; ?>
                </select>
            </div>

            <button type="submit" class="btn btn-primary">Promote</button>
            <button type="button" onclick="hidePromoteDialog()" class="btn btn-secondary">Cancel</button>
        </form>
    </div>
<?php endif; ?>

    <script>
        function showPromoteDialog(userId, username) {
            document.getElementById('promoteUserId').value = userId;
            document.getElementById('promoteUsername').textContent = username;
            document.getElementById('promoteDialog').style.display = 'block';
        }

        function hidePromoteDialog() {
            document.getElementById('promoteDialog').style.display = 'none';
        }
    </script>
<?php $this->endSection() ?>