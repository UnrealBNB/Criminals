<?php $this->extends('layouts.admin') ?>

<?php $this->section('admin_content') ?>
    <div class="admin-settings">
        <h1>Game Settings</h1>

        <div class="settings-grid">
            <div class="setting-box">
                <h3>Theme Settings</h3>
                <p>Change the game's visual theme</p>
                <a href="/admin/settings/theme" class="btn btn-primary">Manage Theme</a>
            </div>

            <div class="setting-box">
                <h3>Game Rules</h3>
                <p>Edit the game rules text</p>
                <a href="/admin/settings/rules" class="btn btn-info">Edit Rules</a>
            </div>

            <div class="setting-box">
                <h3>Prize Settings</h3>
                <p>Configure game prizes and rewards</p>
                <a href="/admin/settings/prices" class="btn btn-success">Edit Prizes</a>
            </div>

            <div class="setting-box">
                <h3>Game Configuration</h3>
                <p>Core game mechanics settings</p>
                <a href="/admin/settings/game" class="btn btn-warning">Game Config</a>
            </div>

            <div class="setting-box">
                <h3>Maintenance Mode</h3>
                <p>Enable or disable maintenance mode</p>
                <a href="/admin/settings/maintenance" class="btn btn-danger">Maintenance</a>
            </div>
        </div>
    </div>
<?php $this->endSection() ?>