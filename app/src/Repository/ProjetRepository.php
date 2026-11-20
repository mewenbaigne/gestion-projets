<?php
declare(strict_types=1);

namespace App\Repository;

use App\Model\Utilisateur;

final class ProjetRepository extends Repository
{
    /** Rôle de l'utilisateur dans le projet (null = pas membre). */
    public function roleDe(int $idUtilisateur, int $idProjet): ?string
    {
        $s = $this->pdo->prepare('SELECT r.nom FROM utilisateur_projet up
                                  INNER JOIN roles r ON r.id = up.id_role
                                  WHERE up.id_utilisateur = :u AND up.id_projet = :p');
        $s->execute([':u' => $idUtilisateur, ':p' => $idProjet]);
        $nom = $s->fetchColumn();
        return $nom === false ? null : $nom;
    }

    /** Projets visibles (tous pour un admin, sinon ceux dont on est membre). @return array<int, array{id:int, nom:string}> */
    public function visibles(Utilisateur $u): array
    {
        if ($u->admin) {
            return $this->pdo->query('SELECT id, nom FROM projets ORDER BY nom')->fetchAll();
        }
        $s = $this->pdo->prepare('SELECT p.id, p.nom FROM projets p INNER JOIN utilisateur_projet up ON up.id_projet = p.id
                                  WHERE up.id_utilisateur = :u ORDER BY p.nom');
        $s->execute([':u' => $u->id]);
        return $s->fetchAll();
    }

    /** Projets que l'utilisateur peut gérer (tous pour un admin, sinon ceux dont il est chef). */
    public function gerables(Utilisateur $u): array
    {
        if ($u->admin) {
            return $this->visibles($u);
        }
        $s = $this->pdo->prepare("SELECT p.id, p.nom FROM projets p
                                  INNER JOIN utilisateur_projet up ON up.id_projet = p.id
                                  INNER JOIN roles r ON r.id = up.id_role
                                  WHERE up.id_utilisateur = :u AND r.nom = 'Chef de projet' ORDER BY p.nom");
        $s->execute([':u' => $u->id]);
        return $s->fetchAll();
    }

    /** @return int[] */
    public function idsMembres(int $idProjet): array
    {
        $s = $this->pdo->prepare('SELECT id_utilisateur FROM utilisateur_projet WHERE id_projet = :p');
        $s->execute([':p' => $idProjet]);
        return array_map('intval', array_column($s->fetchAll(), 'id_utilisateur'));
    }

    /** @param int[] $idsProjets @return array<int, array[]> membres groupés par projet */
    public function membresParProjet(array $idsProjets): array
    {
        if (!$idsProjets) {
            return [];
        }
        $in = implode(',', array_fill(0, count($idsProjets), '?'));
        $s = $this->pdo->prepare("SELECT up.id_projet, u.id, u.prenom, u.nom FROM utilisateur_projet up
                                  INNER JOIN utilisateurs u ON u.id = up.id_utilisateur
                                  WHERE up.id_projet IN ($in) ORDER BY u.nom");
        $s->execute($idsProjets);
        $res = [];
        foreach ($s->fetchAll() as $m) {
            $res[(int) $m['id_projet']][] = $m;
        }
        return $res;
    }
}
