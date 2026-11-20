<?php use App\Core\View; ?>
<?php if ($erreur): ?>
    <div class="flash erreur"><?= View::e($erreur) ?></div>
<?php endif; ?>

<form method="post" action="<?= $v->url('/login') ?>" class="card etroit">
    <?= $v->csrf() ?>
    <label for="email">Email</label>
    <input type="email" id="email" name="email" value="<?= View::e($email) ?>" autocomplete="username" maxlength="255" required>

    <label for="password">Mot de passe</label>
    <input type="password" id="password" name="password" autocomplete="current-password" maxlength="1000" required>

    <p class="mt"><button type="submit" class="btn">Se connecter</button></p>
    <p class="muted">Démo (mot de passe <code>password123</code>) :<br>
        <code>admin@example.com</code> (admin), <code>chef@example.com</code> (chef de projet),<br>
        <code>dev@example.com</code>, <code>designer@example.com</code></p>
</form>
