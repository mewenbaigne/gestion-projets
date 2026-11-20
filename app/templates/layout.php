<?php /** @var \App\Core\View $v */ use App\Core\View; ?>
<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title><?= View::e($titre) ?> - Gestion de projets</title>
    <link rel="stylesheet" href="<?= $v->url('/assets/style.css') ?>">
    <script src="<?= $v->url('/assets/app.js') ?>" defer></script>
</head>
<body>
<nav>
    <a href="<?= $v->url('/') ?>">Gestion de projets</a>
    <?php if ($user): ?>
        <a href="<?= $v->url('/taches') ?>">Tâches</a>
        <span class="spacer"></span>
        <span class="who"><?= View::e($user->nomComplet()) ?><?= $user->admin ? ' (Admin)' : '' ?></span>
        <form method="post" action="<?= $v->url('/logout') ?>" class="inline">
            <?= $v->csrf() ?>
            <button type="submit" class="link">Déconnexion</button>
        </form>
    <?php else: ?>
        <span class="spacer"></span>
        <a href="<?= $v->url('/login') ?>">Connexion</a>
    <?php endif; ?>
</nav>
<main>
    <?php if ($flash): ?>
        <div class="flash <?= View::e($flash['type']) ?>"><?= View::e($flash['message']) ?></div>
    <?php endif; ?>
    <h1><?= View::e($titre) ?></h1>
    <?= $contenu ?>
</main>
</body>
</html>
