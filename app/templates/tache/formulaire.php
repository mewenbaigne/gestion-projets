<?php use App\Core\View; use App\Model\Tache; ?>
<?php foreach ($erreurs as $message): ?>
    <div class="flash erreur"><?= View::e($message) ?></div>
<?php endforeach; ?>

<form method="post" action="<?= $v->url($id ? '/taches/' . (int) $id . '/modifier' : '/taches/nouvelle') ?>" class="card">
    <?= $v->csrf() ?>

    <label for="id_projet">Projet</label>
    <?php if ($id): ?>
        <input type="text" value="<?= View::e($tache['projet_nom']) ?>" disabled>
    <?php else: ?>
        <select id="id_projet" name="id_projet">
            <?php foreach ($gerables as $p): ?>
                <option value="<?= (int) $p['id'] ?>" <?= $projetCourant === (int) $p['id'] ? 'selected' : '' ?>><?= View::e($p['nom']) ?></option>
            <?php endforeach; ?>
        </select>
    <?php endif; ?>

    <label for="nom">Titre</label>
    <input type="text" id="nom" name="nom" maxlength="150" value="<?= View::e($tache['nom']) ?>" required>

    <label for="description">Description</label>
    <textarea id="description" name="description" rows="4"><?= View::e($tache['description']) ?></textarea>

    <label for="dateDebut">Date de début</label>
    <input type="date" id="dateDebut" name="dateDebut" value="<?= View::e($tache['dateDebut']) ?>">

    <label for="dateFin">Date de fin</label>
    <input type="date" id="dateFin" name="dateFin" value="<?= View::e($tache['dateFin']) ?>">

    <label for="etat">État</label>
    <select id="etat" name="etat">
        <?php foreach (Tache::ETATS as $cle => $lib): ?>
            <option value="<?= View::e($cle) ?>" <?= $tache['etat'] === $cle ? 'selected' : '' ?>><?= View::e($lib) ?></option>
        <?php endforeach; ?>
    </select>

    <label>Utilisateurs attribués</label>
    <div id="membres">
    <?php foreach ($membresParProjet as $pid => $membres): ?>
        <div class="membres-projet" data-projet="<?= (int) $pid ?>" <?= $pid === $projetCourant ? '' : 'hidden' ?>>
            <?php foreach ($membres as $m): ?>
                <label class="check"><input type="checkbox" name="utilisateurs[]" value="<?= (int) $m['id'] ?>"
                    <?= in_array((int) $m['id'], $attribues, true) ? 'checked' : '' ?>
                    <?= $pid === $projetCourant ? '' : 'disabled' ?>> <?= View::e($m['prenom'] . ' ' . $m['nom']) ?></label>
            <?php endforeach; ?>
        </div>
    <?php endforeach; ?>
    </div>

    <p class="actions mt">
        <button type="submit" class="btn"><?= $id ? 'Enregistrer' : 'Créer' ?></button>
        <a class="btn secondaire" href="<?= $v->url($id ? '/taches/' . (int) $id : '/taches') ?>">Annuler</a>
    </p>
</form>
