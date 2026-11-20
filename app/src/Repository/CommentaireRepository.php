<?php
declare(strict_types=1);

namespace App\Repository;

use App\Model\Commentaire;

final class CommentaireRepository extends Repository
{
    public function trouver(int $id): ?Commentaire
    {
        $s = $this->pdo->prepare('SELECT c.*, u.prenom, u.nom FROM commentaires c
                                  LEFT JOIN utilisateurs u ON u.id = c.id_utilisateur WHERE c.id = :id');
        $s->execute([':id' => $id]);
        $l = $s->fetch();
        return $l ? Commentaire::depuisLigne($l) : null;
    }

    /** Commentaires d'une tâche sous forme d'arbre : racines + réponses imbriquées (ordre chronologique). @return Commentaire[] */
    public function arbreDeLaTache(int $idTache): array
    {
        $s = $this->pdo->prepare('SELECT c.*, u.prenom, u.nom FROM commentaires c
                                  LEFT JOIN utilisateurs u ON u.id = c.id_utilisateur
                                  WHERE c.id_tache = :t ORDER BY c.date ASC, c.id ASC');
        $s->execute([':t' => $idTache]);
        $parId = [];
        foreach ($s->fetchAll() as $l) {
            $c = Commentaire::depuisLigne($l);
            $parId[$c->id] = $c;
        }
        $racines = [];
        foreach ($parId as $c) {
            if ($c->idParent !== null && isset($parId[$c->idParent])) {
                $parId[$c->idParent]->reponses[] = $c;
            } else {
                $racines[] = $c;
            }
        }
        return $racines;
    }

    public function total(int $idTache): int
    {
        $s = $this->pdo->prepare('SELECT COUNT(*) FROM commentaires WHERE id_tache = :t AND supprime = 0');
        $s->execute([':t' => $idTache]);
        return (int) $s->fetchColumn();
    }

    public function creer(int $idTache, int $idAuteur, string $message, ?int $idParent): int
    {
        $this->pdo->prepare('INSERT INTO commentaires (id_utilisateur, id_tache, id_parent, message) VALUES (:u, :t, :p, :m)')
            ->execute([':u' => $idAuteur, ':t' => $idTache, ':p' => $idParent, ':m' => $message]);
        return (int) $this->pdo->lastInsertId();
    }

    public function aDesReponses(int $id): bool
    {
        $s = $this->pdo->prepare('SELECT 1 FROM commentaires WHERE id_parent = :id LIMIT 1');
        $s->execute([':id' => $id]);
        return (bool) $s->fetchColumn();
    }

    /** Suppression douce : le contenu et l'auteur sont effacés, la ligne reste pour porter les réponses. */
    public function marquerSupprime(int $id): void
    {
        $this->pdo->prepare("UPDATE commentaires SET supprime = 1, message = '', id_utilisateur = NULL WHERE id = :id")
            ->execute([':id' => $id]);
    }

    /**
     * Supprime définitivement un commentaire sans réponse, puis remonte : un parent déjà « supprimé »
     * qui n'a plus aucune réponse n'a plus de raison d'être affiché et est purgé à son tour.
     */
    public function supprimerFeuille(int $id): void
    {
        while ($id !== null) {
            $c = $this->trouver($id);
            $this->pdo->prepare('DELETE FROM commentaires WHERE id = :id')->execute([':id' => $id]);
            $parent = $c?->idParent;
            $id = ($parent !== null && ($p = $this->trouver($parent)) && $p->supprime && !$this->aDesReponses($parent)) ? $parent : null;
        }
    }
}
