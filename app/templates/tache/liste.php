<?php use App\Core\View; use App\Model\FiltreTaches; use App\Model\Tache; ?>
<div class="toolbar">
    <span class="muted"><?= count($taches) ?> tâche(s)<?= $f->actif() ? ' · filtres actifs' : '' ?></span>
    <?php if ($peutCreer): ?><a class="btn" href="<?= $v->url('/taches/nouvelle') ?>">+ Nouvelle tâche</a><?php endif; ?>
</div>

<form method="get" action="<?= $v->url('/taches') ?>" class="card actions filtres">
    <div class="groupe">
        <label class="nomargin" for="f-q">Recherche</label>
        <input type="search" id="f-q" name="q" value="<?= View::e($f->recherche) ?>" maxlength="<?= FiltreTaches::RECHERCHE_MAX ?>" placeholder="Titre ou description">
    </div>
    <div class="groupe">
        <label class="nomargin" for="f-projet">Projet</label>
        <select id="f-projet" name="projet" class="auto">
            <option value="0">Tous</option>
            <?php foreach ($projets as $p): ?>
                <option value="<?= (int) $p['id'] ?>" <?= $f->projet === (int) $p['id'] ? 'selected' : '' ?>><?= View::e($p['nom']) ?></option>
            <?php endforeach; ?>
        </select>
    </div>
    <div class="groupe">
        <label class="nomargin" for="f-etat">État</label>
        <select id="f-etat" name="etat" class="auto">
            <option value="">Tous</option>
            <?php foreach (Tache::ETATS as $cle => $lib): ?>
                <option value="<?= View::e($cle) ?>" <?= $f->etat === $cle ? 'selected' : '' ?>><?= View::e($lib) ?></option>
            <?php endforeach; ?>
        </select>
    </div>
    <div class="groupe">
        <label class="nomargin" for="f-echeance">Échéance</label>
        <select id="f-echeance" name="echeance" class="auto">
            <option value="">Toutes</option>
            <?php foreach (FiltreTaches::ECHEANCES as $cle => $lib): ?>
                <option value="<?= View::e($cle) ?>" <?= $f->echeance === $cle ? 'selected' : '' ?>><?= View::e($lib) ?></option>
            <?php endforeach; ?>
        </select>
    </div>
    <div class="groupe">
        <label class="nomargin" for="f-tri">Trier par</label>
        <select id="f-tri" name="tri" class="auto">
            <?php foreach (FiltreTaches::TRIS as $cle => $lib): ?>
                <option value="<?= View::e($cle) ?>" <?= $f->tri === $cle ? 'selected' : '' ?>><?= View::e($lib) ?></option>
            <?php endforeach; ?>
        </select>
    </div>
    <label class="nomargin"><input type="checkbox" name="mes" value="1" class="auto" <?= $f->mesTaches ? 'checked' : '' ?>> Mes tâches</label>
    <button type="submit" class="btn petit">Filtrer</button>
    <a class="btn petit secondaire" href="<?= $v->url('/taches') ?>">Réinitialiser</a>
</form>

<?php if (!$taches): ?>
    <div class="card">Aucune tâche<?= $f->actif() ? ' ne correspond à ces filtres' : '' ?>.</div>
<?php else: ?>
<table>
    <thead>
        <tr><th>Tâche</th><th>Projet</th><th>État</th><th>Échéance</th><th>Attribuée à</th><th></th></tr>
    </thead>
    <tbody>
    <?php foreach ($taches as $t): ?>
        <tr>
            <td><a href="<?= $v->url('/taches/' . (int) $t['id']) ?>"><?= View::e($t['nom']) ?></a></td>
            <td><?= View::e($t['projet_nom']) ?></td>
            <td><span class="badge <?= View::e($t['etat']) ?>"><?= View::e(Tache::ETATS[$t['etat']]) ?></span></td>
            <td><?= View::e($t['dateFin'] ?? '—') ?></td>
            <td><?= View::e($t['attribues'] ?? '—') ?></td>
            <td><a class="btn petit secondaire" href="<?= $v->url('/taches/' . (int) $t['id']) ?>">Détail</a></td>
        </tr>
    <?php endforeach; ?>
    </tbody>
</table>
<?php endif; ?>
