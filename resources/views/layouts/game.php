<?php $this->extends('layouts.app') ?>

<?php $this->section('content') ?>
    <div class="game-layout">
        <div class="game-sidebar">
            <?php $this->include('partials.game-menu') ?>
            <?php $this->include('partials.user-stats') ?>
        </div>

        <div class="game-content">
            <?php $this->yield('game_content') ?>
        </div>
    </div>
<?php $this->endSection() ?>