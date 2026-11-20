<?php
declare(strict_types=1);

namespace App\Repository;

final class HistoriqueRepository extends Repository
{
    public function tache(int $idTache, int $idUtilisateur, string $description): void
    {
        $this->pdo->prepare('INSERT INTO historique_taches (id_tache, id_utilisateur, descriptionModification)
                             VALUES (:t, :u, :d)')->execute([':t' => $idTache, ':u' => $idUtilisateur, ':d' => $description]);
    }

    public function projet(int $idProjet, int $idUtilisateur, string $description): void
    {
        $this->pdo->prepare('INSERT INTO historique_projets (id_projet, id_utilisateur, descriptionModification)
                             VALUES (:p, :u, :d)')->execute([':p' => $idProjet, ':u' => $idUtilisateur, ':d' => $description]);
    }

    public function commentaire(int $idCommentaire, int $idUtilisateur, string $description): void
    {
        $this->pdo->prepare('INSERT INTO historique_commentaires (id_commentaire, id_utilisateur, descriptionModification)
                             VALUES (:c, :u, :d)')->execute([':c' => $idCommentaire, ':u' => $idUtilisateur, ':d' => $description]);
    }

    public function duneTache(int $idTache): array
    {
        $s = $this->pdo->prepare('SELECT h.date, h.descriptionModification, u.prenom, u.nom
                                  FROM historique_taches h LEFT JOIN utilisateurs u ON u.id = h.id_utilisateur
                                  WHERE h.id_tache = :t ORDER BY h.date DESC, h.id DESC');
        $s->execute([':t' => $idTache]);
        return $s->fetchAll();
    }
}
