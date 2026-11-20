<?php
declare(strict_types=1);

namespace App\Repository;

use App\Core\Texte;
use App\Model\FiltreTaches;
use App\Model\Tache;
use App\Model\Utilisateur;

final class TacheRepository extends Repository
{
    public function trouver(int $id): ?Tache
    {
        $s = $this->pdo->prepare('SELECT t.*, p.nom AS projet_nom FROM taches t
                                  INNER JOIN projets p ON p.id = t.id_projet WHERE t.id = :id');
        $s->execute([':id' => $id]);
        $l = $s->fetch();
        return $l ? Tache::depuisLigne($l) : null;
    }

    /** Liste filtrée, limitée aux projets de l'utilisateur sauf admin. Tout est paramétré ou issu d'une liste blanche. */
    public function lister(Utilisateur $u, FiltreTaches $f): array
    {
        $sql = "SELECT t.id, t.nom, t.etat, t.dateFin, p.nom AS projet_nom,
                       (SELECT GROUP_CONCAT(CONCAT(u2.prenom, ' ', u2.nom) SEPARATOR ', ')
                          FROM tache_utilisateur tu INNER JOIN utilisateurs u2 ON u2.id = tu.id_utilisateur
                         WHERE tu.id_tache = t.id) AS attribues
                FROM taches t INNER JOIN projets p ON p.id = t.id_projet WHERE 1 = 1";
        $params = [];
        if (!$u->admin) {
            $sql .= ' AND t.id_projet IN (SELECT id_projet FROM utilisateur_projet WHERE id_utilisateur = :moi)';
            $params[':moi'] = $u->id;
        }
        if ($f->recherche !== '') {
            $motif = '%' . Texte::echapperLike($f->recherche) . '%';
            $sql .= " AND (t.nom LIKE :q1 ESCAPE '\\\\' OR t.description LIKE :q2 ESCAPE '\\\\')";
            $params[':q1'] = $params[':q2'] = $motif;
        }
        if ($f->projet) {
            $sql .= ' AND t.id_projet = :projet';
            $params[':projet'] = $f->projet;
        }
        if ($f->etat !== '') {
            $sql .= ' AND t.etat = :etat';
            $params[':etat'] = $f->etat;
        }
        if ($f->mesTaches) {
            $sql .= ' AND t.id IN (SELECT id_tache FROM tache_utilisateur WHERE id_utilisateur = :moi2)';
            $params[':moi2'] = $u->id;
        }
        $sql .= match ($f->echeance) {
            'en_retard' => " AND t.dateFin < CURDATE() AND t.etat IN ('a_faire', 'en_cours')",
            '7j'        => ' AND t.dateFin BETWEEN CURDATE() AND DATE_ADD(CURDATE(), INTERVAL 7 DAY)',
            'sans'      => ' AND t.dateFin IS NULL',
            default     => '',
        };
        $sql .= match ($f->tri) { // liste blanche : jamais de valeur utilisateur dans ORDER BY
            'titre'  => ' ORDER BY t.nom ASC, t.id DESC',
            'etat'   => " ORDER BY FIELD(t.etat, 'a_faire', 'en_cours', 'termine', 'annule'), t.dateFin IS NULL, t.dateFin ASC",
            'recent' => ' ORDER BY t.id DESC',
            default  => ' ORDER BY t.dateFin IS NULL, t.dateFin ASC, t.id DESC',
        };
        $s = $this->pdo->prepare($sql);
        $s->execute($params);
        return $s->fetchAll();
    }

    /** @return array<int, array{id:int, prenom:string, nom:string}> */
    public function attribues(int $idTache): array
    {
        $s = $this->pdo->prepare('SELECT u.id, u.prenom, u.nom FROM tache_utilisateur tu
                                  INNER JOIN utilisateurs u ON u.id = tu.id_utilisateur
                                  WHERE tu.id_tache = :t ORDER BY u.nom');
        $s->execute([':t' => $idTache]);
        return $s->fetchAll();
    }

    /** @return int[] */
    public function idsAttribues(int $idTache): array
    {
        return array_map(fn($a) => (int) $a['id'], $this->attribues($idTache));
    }

    public function estAttribue(int $idUtilisateur, int $idTache): bool
    {
        $s = $this->pdo->prepare('SELECT 1 FROM tache_utilisateur WHERE id_tache = :t AND id_utilisateur = :u');
        $s->execute([':t' => $idTache, ':u' => $idUtilisateur]);
        return (bool) $s->fetchColumn();
    }

    public function creer(int $idProjet, string $nom, ?string $description, ?string $debut, ?string $fin, string $etat): int
    {
        $this->pdo->prepare('INSERT INTO taches (id_projet, nom, description, dateDebut, dateFin, etat)
                             VALUES (:p, :nom, :d, :dd, :df, :etat)')
            ->execute([':p' => $idProjet, ':nom' => $nom, ':d' => $description, ':dd' => $debut, ':df' => $fin, ':etat' => $etat]);
        return (int) $this->pdo->lastInsertId();
    }

    public function modifier(int $id, string $nom, ?string $description, ?string $debut, ?string $fin, string $etat): void
    {
        $this->pdo->prepare('UPDATE taches SET nom = :nom, description = :d, dateDebut = :dd, dateFin = :df, etat = :etat WHERE id = :id')
            ->execute([':nom' => $nom, ':d' => $description, ':dd' => $debut, ':df' => $fin, ':etat' => $etat, ':id' => $id]);
    }

    public function changerEtat(int $id, string $etat): void
    {
        $this->pdo->prepare('UPDATE taches SET etat = :etat WHERE id = :id')->execute([':etat' => $etat, ':id' => $id]);
    }

    public function supprimer(int $id): void
    {
        $this->pdo->prepare('DELETE FROM taches WHERE id = :id')->execute([':id' => $id]);
    }

    /** @param int[] $idsUtilisateurs */
    public function synchroniserAttributions(int $idTache, array $idsUtilisateurs): bool
    {
        $actuels = $this->idsAttribues($idTache);
        $retires = array_diff($actuels, $idsUtilisateurs);
        $ajoutes = array_diff($idsUtilisateurs, $actuels);
        foreach ($retires as $uid) {
            $this->pdo->prepare('DELETE FROM tache_utilisateur WHERE id_tache = :t AND id_utilisateur = :u')
                ->execute([':t' => $idTache, ':u' => $uid]);
        }
        foreach ($ajoutes as $uid) {
            $this->pdo->prepare('INSERT INTO tache_utilisateur (id_tache, id_utilisateur) VALUES (:t, :u)')
                ->execute([':t' => $idTache, ':u' => $uid]);
        }
        return $retires || $ajoutes;
    }
}
