<?php $this->extends('layouts.app') ?>

<?php $this->section('content') ?>
    <div class="admin-layout">
        <div class="admin-sidebar">
            <?php $this->include('partials.admin-menu') ?>
        </div>

        <div class="admin-content">
            <div class="admin-header">
                <h1><?= $this->e($page_title ?? 'Admin Panel') ?></h1>
            </div>
            <?php $this->yield('admin_content') ?>
        </div>
    </div>
<?php $this->endSection() ?>