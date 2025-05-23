<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?= $this->e($title ?? 'Criminals') ?> - <?= $this->e($app_name) ?></title>
    <link rel="stylesheet" href="<?= $this->asset('css/style.css') ?>">
    <?php $this->yield('styles') ?>
</head>
<body>
<div id="wrapper">
    <?php $this->include('partials.header') ?>

    <div id="container">
        <?php if ($success = session('_flash.success')): ?>
            <div class="alert alert-success">
                <?= $this->e($success) ?>
            </div>
        <?php endif; ?>

        <?php if ($error = session('_flash.error')): ?>
            <div class="alert alert-danger">
                <?= $this->e($error) ?>
            </div>
        <?php endif; ?>

        <?php $this->yield('content') ?>
    </div>

    <?php $this->include('partials.footer') ?>
</div>

<script src="<?= $this->asset('js/app.js') ?>"></script>
<?php $this->yield('scripts') ?>
</body>
</html>