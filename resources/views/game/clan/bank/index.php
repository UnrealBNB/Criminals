<?php $this->extends('layouts.game') ?>

<?php $this->section('game_content') ?>
    <div class="clan-bank">
        <h1>Clan Bank</h1>

        <div class="bank-info">
            <table>
                <tr>
                    <td>Clan Cash:</td>
                    <td>€<?= number_format($clan->cash) ?></td>
                </tr>
                <tr>
                    <td>Clan Bank:</td>
                    <td>€<?= number_format($clan->bank) ?></td>
                </tr>
                <tr>
                    <td>Transactions Left:</td>
                    <td><?= $clan->bankleft ?></td>
                </tr>
            </table>
        </div>

        <div class="bank-forms">
            <div class="form-section">
                <h2>Deposit to Bank</h2>
                <form method="POST" action="/game/clan/bank/deposit">
                    <?= $this->csrf() ?>
                    <div class="form-group">
                        <label for="deposit_amount">Amount:</label>
                        <input type="number"
                               id="deposit_amount"
                               name="amount"
                               min="1"
                               max="<?= $clan->cash ?>"
                               required>
                    </div>
                    <button type="submit" class="btn btn-primary">Deposit</button>
                </form>
            </div>

            <div class="form-section">
                <h2>Withdraw from Bank</h2>
                <form method="POST" action="/game/clan/bank/withdraw">
                    <?= $this->csrf() ?>
                    <div class="form-group">
                        <label for="withdraw_amount">Amount:</label>
                        <input type="number"
                               id="withdraw_amount"
                               name="amount"
                               min="1"
                               max="<?= $clan->bank ?>"
                               required>
                    </div>
                    <button type="submit" class="btn btn-primary">Withdraw</button>
                </form>
            </div>
        </div>

        <div class="bank-note">
            <p><strong>Note:</strong> Clan bank earns 5% interest daily!</p>
            <p>Only members with Banker role (Level 6+) can manage the clan bank.</p>
        </div>
    </div>
<?php $this->endSection() ?>