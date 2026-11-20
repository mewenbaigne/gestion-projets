<?php
/** Commentaire + ses réponses (récursif). Indentation visuelle plafonnée à 4 niveaux. */
use App\Core\View;
$nbRep = $c->compterReponses();
?>
<div class="fil niveau-<?= min($niveau, 4) ?>" id="commentaire-<?= $c->id ?>">
    <?php if ($c->supprime): ?>
    <div class="card commentaire supprime">
        <p class="muted mb0"><em>Commentaire supprimé</em></p>
    </div>
    <?php else: ?>
    <div class="card commentaire">
        <div class="actions entre">
            <strong><?= $c->auteurNom !== null ? View::e($c->auteurNom) : '<em>Utilisateur supprimé</em>' ?></strong>
            <span class="muted"><?= View::e($c->date) ?></span>
        </div>
        <p><?= nl2br(View::e($c->message)) ?></p>
        <div class="actions">
            <?php if ($peutCommenter): ?>
            <details class="repondre">
                <summary class="btn petit secondaire">Répondre</summary>
                <form method="post" action="<?= $v->url('/commentaires/' . $c->id . '/repondre') ?>">
                    <?= $v->csrf() ?>
                    <label class="sr" for="rep-<?= $c->id ?>">Votre réponse à <?= View::e($c->auteurNom ?? 'ce commentaire') ?></label>
                    <textarea id="rep-<?= $c->id ?>" name="message" rows="2" maxlength="2000" required
                              placeholder="Répondre à <?= View::e($c->auteurNom ?? 'ce commentaire') ?>…"></textarea>
                    <p class="mt-s"><button type="submit" class="btn petit">Publier la réponse</button></p>
                </form>
            </details>
            <?php endif; ?>
            <?php if ($gerer || $c->idAuteur === $u->id): ?>
            <form method="post" action="<?= $v->url('/commentaires/' . $c->id . '/supprimer') ?>" class="inline"
                  data-confirm="Supprimer ce commentaire ?<?= $nbRep ? ' Ses ' . $nbRep . ' réponse(s) seront conservées.' : '' ?>">
                <?= $v->csrf() ?>
                <button type="submit" class="btn petit danger">Supprimer</button>
            </form>
            <?php endif; ?>
        </div>
    </div>
    <?php endif; ?>
    <?php foreach ($c->reponses as $r): ?>
        <?= $v->fragment('partials/commentaire', ['c' => $r, 'niveau' => $niveau + 1] + compact('t', 'u', 'gerer', 'peutCommenter')) ?>
    <?php endforeach; ?>
</div>
