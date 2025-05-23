<?php $this->extends('layouts.admin') ?>

<?php $this->section('admin_content') ?>
    <div class="admin-messages">
        <h1>Mass Messaging</h1>

        <div class="message-options">
            <div class="option-box">
                <h2>Send to All Users</h2>
                <p>Send a message to every active user</p>
                <a href="/admin/messages/mass" class="btn btn-primary">Mass Message</a>
            </div>

            <div class="option-box">
                <h2>Send by Type</h2>
                <p>Send to all Drug Dealers, Scientists, or Police</p>
                <a href="/admin/messages/type" class="btn btn-info">By Type</a>
            </div>

            <div class="option-box">
                <h2>Send by Country</h2>
                <p>Send to all users in a specific country</p>
                <a href="/admin/messages/country" class="btn btn-success">By Country</a>
            </div>
        </div>
    </div>
<?php $this->endSection() ?>