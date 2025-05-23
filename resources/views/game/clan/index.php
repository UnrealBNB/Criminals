<?php $this->extends('layouts.game') ?>

<?php $this->section('game_content') ?>
    <div class="clan-home">
        <h1>Clan System</h1>

        <div class="clan-options">
            <div class="option-box">
                <h2>Create a Clan</h2>
                <p>Start your own clan and recruit members!</p>
                <a href="/game/clan/create" class="btn btn-primary">Create Clan</a>
            </div>

            <div class="option-box">
                <h2>Join a Clan</h2>
                <p>Apply to join an existing clan!</p>
                <a href="/game/clan/join" class="btn btn-secondary">Find Clans</a>
            </div>

            <div class="option-box">
                <h2>Browse Clans</h2>
                <p>View all active clans!</p>
                <a href="/game/clan/list" class="btn btn-info">View All</a>
            </div>
        </div>

        <div class="clan-benefits">
            <h3>Benefits of Joining a Clan</h3>
            <ul>
                <li>Share resources with clan members</li>
                <li>Access to clan shop and special items</li>
                <li>Clan bank with daily interest</li>
                <li>Coordinate attacks and strategies</li>
                <li>Clan-only buildings generate income</li>
            </ul>
        </div>
    </div>
<?php $this->endSection() ?>