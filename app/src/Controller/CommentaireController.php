<?php
declare(strict_types=1);

namespace App\Controller;

use App\Core\HttpException;
use App\Model\Commentaire;
use App\Model\Tache;
use App\Model\Utilisateur;
use PDOException;

final class CommentaireController extends Controller
{
    private const MAX = 2000;

    /** Nouveau commentaire de premier niveau sur une tâche. */
    public function ajouter(int $idTache): void
    {
        $this->verifierCsrf();
        $u = $this->utilisateur();
        $t = $this->tacheAccessible($idTache, $u);
        $this->publier($u, $t, null);
    }

    /** Réponse à un commentaire existant (même tâche). */
    public function repondre(int $idParent): void
    {
        $this->verifierCsrf();
        $u = $this->utilisateur();
        $parent = $this->app->commentaires->trouver($idParent) ?? throw HttpException::introuvable('Commentaire introuvable.');
        if ($parent->supprime) {
            throw HttpException::interdit('Ce commentaire a été supprimé : répondez à une des réponses.');
        }
        $t = $this->tacheAccessible($parent->idTache, $u);
        $this->publier($u, $t, $parent);
    }

    public function supprimer(int $id): void
    {
        $this->verifierCsrf();
        $u = $this->utilisateur();
        $c = $this->app->commentaires->trouver($id) ?? throw HttpException::introuvable('Commentaire introuvable.');
        $t = $this->tacheAccessible($c->idTache, $u);
        if (!$this->app->permissions->peutSupprimerCommentaire($u, $t, $c)) {
            throw HttpException::interdit('Vous ne pouvez pas supprimer ce commentaire.');
        }

        if ($c->supprime) {
            throw HttpException::introuvable('Commentaire déjà supprimé.');
        }
        $pdo = $this->app->pdo;
        $pdo->beginTransaction();
        if ($this->app->commentaires->aDesReponses($id)) {
            $this->app->commentaires->marquerSupprime($id); // les réponses sont conservées
            $this->app->historique->tache($t->id, $u->id, 'Commentaire supprimé (réponses conservées)');
        } else {
            $this->app->commentaires->supprimerFeuille($id);
            $this->app->historique->tache($t->id, $u->id, 'Commentaire supprimé');
        }
        $pdo->commit();
        $this->flash('ok', 'Commentaire supprimé.');
        $this->rediriger('/taches/' . $t->id . '#commentaires');
    }

    private function publier(Utilisateur $u, Tache $t, ?Commentaire $parent): void
    {
        if (!$this->app->permissions->peutCommenter($u, $t)) {
            throw HttpException::interdit('Vous ne pouvez pas commenter cette tâche.');
        }
        $message = $this->app->request->postMultiligne('message');
        if ($message === '' || mb_strlen($message) > self::MAX) {
            $this->flash('erreur', 'Le commentaire doit contenir de 1 à ' . self::MAX . ' caractères.');
            $this->rediriger('/taches/' . $t->id . '#commentaires');
        }

        $pdo = $this->app->pdo;
        try {
            $pdo->beginTransaction();
            $id = $this->app->commentaires->creer($t->id, $u->id, $message, $parent?->id);
            $this->app->historique->commentaire($id, $u->id, $parent ? 'Réponse au commentaire #' . $parent->id : 'Création du commentaire');
            $this->app->historique->tache($t->id, $u->id, $parent ? 'Réponse ajoutée à ' . ($parent->auteurNom ?? 'un commentaire') : 'Commentaire ajouté');
            $pdo->commit();
            $this->flash('ok', $parent ? 'Réponse publiée.' : 'Commentaire ajouté.');
        } catch (PDOException $e) {
            if ($pdo->inTransaction()) {
                $pdo->rollBack();
            }
            error_log($e->getMessage());
            $this->flash('erreur', 'Publication impossible.');
        }
        $this->rediriger('/taches/' . $t->id . '#commentaires');
    }
}
