<?php
declare(strict_types=1);

namespace App\Service;

use App\Model\Commentaire;
use App\Model\Tache;
use App\Model\Utilisateur;
use App\Repository\ProjetRepository;
use App\Repository\TacheRepository;

/** Règles d'autorisation de l'application (admin, chef de projet, membre, utilisateur attribué). */
final class Permissions
{
    private const CHEF = 'Chef de projet';

    public function __construct(private ProjetRepository $projets, private TacheRepository $taches)
    {
    }

    public function estMembre(Utilisateur $u, int $idProjet): bool
    {
        return $u->admin || $this->projets->roleDe($u->id, $idProjet) !== null;
    }

    public function peutGerer(Utilisateur $u, int $idProjet): bool
    {
        return $u->admin || $this->projets->roleDe($u->id, $idProjet) === self::CHEF;
    }

    public function peutChangerEtat(Utilisateur $u, Tache $t): bool
    {
        return $this->peutGerer($u, $t->idProjet) || $this->taches->estAttribue($u->id, $t->id);
    }

    /** Commenter et répondre : tout membre du projet. */
    public function peutCommenter(Utilisateur $u, Tache $t): bool
    {
        return $this->estMembre($u, $t->idProjet);
    }

    public function peutSupprimerCommentaire(Utilisateur $u, Tache $t, Commentaire $c): bool
    {
        return $c->idAuteur === $u->id || $this->peutGerer($u, $t->idProjet);
    }
}
