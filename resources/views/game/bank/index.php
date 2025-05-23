<?php $this->extends('layouts.game') ?>

<?php $this->section('game_content') ?>
    <div class="bank">
        <h1>Bank</h1>

        <div class="bank-info">
            <table>
                <tr>
                    <td>Cash:</td>
                    <td>€<?= number_format($user->cash) ?></td>
                </tr>
                <tr>
                    <td>Bank:</td>
                    <td>€<?= number_format($user->bank) ?></td>
                </tr>
                <tr>
                    <td>Transactions Left:</td>
                    <td><?= $user->bank_left ?></td>
                </tr>
            </table>
        </div>

        <div class="bank-forms">
            <div class="form-section">
                <h2>Deposit</h2>
                <form method="POST" action="/game/bank/deposit">
                    <?= $this->csrf() ?>
                    <div class="form-group">
                        <label for="deposit_amount">Amount</label>
                        <input type="number"
                               id="deposit_amount"
                               name="amount"
                               min="1"
                               max="<?= $user->cash ?>"
                               required>
                    </div>
                    <button type="submit" class="btn btn-primary">Deposit</button>
                </form>
            </div>

            <div class="form-section">
                <h2>Withdraw</h2>
                <form method="POST" action="/game/bank/withdraw">
                    <?= $this->csrf() ?>
                    <div class="form-group">
                        <label for="withdraw_amount">Amount</label>
                        <input type="number"
                               id="withdraw_amount"
                               name="amount"
                               min="1"
                               max="<?= $user->bank ?>"
                               required>
                    </div>
                    <button type="submit" class="btn btn-primary">Withdraw</button>
                </form>
            </div>
        </div>

        <div class="bank-note">
            <p><strong>Note:</strong> Your bank account earns 5% interest daily!</p>
        </div>
    </div>
<?php $this->endSection() ?>