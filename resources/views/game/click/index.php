<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Click for <?= htmlspecialchars($user->username) ?></title>
    <style>
        body { font-family: Arial, sans-serif; text-align: center; padding: 50px; background: #f5f5f5; }
        .container { max-width: 600px; margin: 0 auto; background: white; padding: 30px; border-radius: 10px; box-shadow: 0 2px 10px rgba(0,0,0,0.1); }
        .btn { display: inline-block; padding: 10px 20px; background: #007bff; color: white; text-decoration: none; border-radius: 5px; margin: 10px; border: none; cursor: pointer; }
        .btn:hover { background: #0056b3; }
        .success { color: #28a745; }
        .info { color: #17a2b8; margin: 20px 0; }
    </style>
</head>
<body>
<div class="container">
    <h1>Click for <?= htmlspecialchars($user->username) ?></h1>

    <?php if ($clicked): ?>
        <div class="success">
            <h2>Success!</h2>
            <p><?= $text ?></p>
            <p>You are now one of <?= htmlspecialchars($user->username) ?>'s <?= $clickType ?>!</p>
        </div>
    <?php else: ?>
        <div class="info">
            <p><?= $text ?></p>
        </div>

        <form method="POST" action="/click/<?= $user->id ?>">
            <button type="submit" class="btn">Click Here!</button>
        </form>
    <?php endif; ?>

    <a href="/" class="btn">Back to Game</a>
</div>
</body>
</html>