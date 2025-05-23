<?php $this->extends('layouts.app') ?>

<?php $this->section('content') ?>
    <div class="home">
        <div class="hero">
            <h1>Welcome to Criminals</h1>
            <p>Build your criminal empire and become the most powerful player!</p>

            <div class="cta-buttons">
                <a href="/register" class="btn btn-primary btn-large">Start Playing</a>
                <a href="/login" class="btn btn-secondary btn-large">Login</a>
            </div>
        </div>

        <div class="features">
            <div class="feature">
                <h3>Choose Your Path</h3>
                <p>Play as a Drug Dealer, Scientist, or Police officer. Each type has unique advantages!</p>
            </div>

            <div class="feature">
                <h3>Build Your Power</h3>
                <p>Buy weapons, protect yourself, and climb the ranks from Zwerver to Godfather!</p>
            </div>

            <div class="feature">
                <h3>Join a Clan</h3>
                <p>Team up with other players, share resources, and dominate together!</p>
            </div>
        </div>
    </div>
<?php $this->endSection() ?>