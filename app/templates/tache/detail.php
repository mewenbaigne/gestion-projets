<?php use App\Core\View; use App\Model\Tache; ?>
<div class="toolbar">
    <a class="btn secondaire" href="<?= $v->url('/taches') ?>">← Retour à la liste</a>
    <?php if ($gerer): ?>
    <span class="actions">
        <a class="btn" href="<?= $v->url('/taches/' . $t->id . '/modifier') ?>">Modifier</a>
        <form method="post" action="<?= $v->url('/taches/' . $t->id . '/supprimer') ?>" class="inline"
              data-confirm="Supprimer cette tâche et ses commentaires ?">
            <?= $v->csrf() ?>
            <button type="submit" class="btn danger">Supprimer</button>
        </form>
    </span>
    <?php endif; ?>
</div>

<div class="card">
    <p><span class="badge <?= View::e($t->etat) ?>"><?= View::e($t->libelleEtat()) ?></span>
       · Projet : <strong><?= View::e($t->projetNom) ?></strong></p>
    <p class="muted">Du <?= View::e($t->dateDebut ?? '—') ?> au <?= View::e($t->dateFin ?? '—') ?></p>
    <p><?= $t->description ? nl2br(View::e($t->description)) : '<em class="muted">Pas de description.</em>' ?></p>
    <p><strong>Attribuée à :</strong>
        <?= $attribues ? View::e(implode(', ', array_map(fn($a) => $a['prenom'] . ' ' . $a['nom'], $attribues))) : '<span class="muted">personne</span>' ?></p>

    <?php if ($changerEtat): ?>
    <form method="post" action="<?= $v->url('/taches/' . $t->id . '/etat') ?>" class="actions">
        <?= $v->csrf() ?>
        <label class="nomargin" for="etat">Changer l'état</label>
        <select id="etat" name="etat" class="auto">
            <?php foreach (Tache::ETATS as $cle => $lib): ?>
                <option value="<?= View::e($cle) ?>" <?= $t->etat === $cle ? 'selected' : '' ?>><?= View::e($lib) ?></option>
            <?php endforeach; ?>
        </select>
        <button type="submit" class="btn petit">Mettre à jour</button>
    </form>
    <?php endif; ?>
</div>

<h2 id="commentaires">Commentaires (<?= (int) $nbCommentaires ?>)</h2>
<?php foreach ($commentaires as $c): ?>
    <?= $v->fragment('partials/commentaire', ['c' => $c, 'niveau' => 0, 't' => $t, 'u' => $u, 'gerer' => $gerer, 'peutCommenter' => $peutCommenter]) ?>
<?php endforeach; ?>
<?php if (!$commentaires): ?><p class="muted">Aucun commentaire.</p><?php endif; ?>

<?php if ($peutCommenter): ?>
<form method="post" action="<?= $v->url('/taches/' . $t->id . '/commentaires') ?>" class="card">
    <?= $v->csrf() ?>
    <label for="message">Ajouter un commentaire</label>
    <textarea id="message" name="message" rows="3" maxlength="2000" required></textarea>
    <p class="mb0"><button type="submit" class="btn">Publier</button></p>
</form>
<?php endif; ?>

<h2>Historique</h2>
<?php if (!$historique): ?>
    <p class="muted">Aucune modification enregistrée.</p>
<?php else: ?>
<table>
    <thead><tr><th>Date</th><th>Utilisateur</th><th>Modification</th></tr></thead>
    <tbody>
    <?php foreach ($historique as $h): ?>
        <tr>
            <td><?= View::e($h['date']) ?></td>
            <td><?= $h['prenom'] !== null ? View::e($h['prenom'] . ' ' . $h['nom']) : '—' ?></td>
            <td><?= View::e($h['descriptionModification']) ?></td>
        </tr>
    <?php endforeach; ?>
    </tbody>
</table>
<?php endif; ?>
