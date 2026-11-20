<?php
declare(strict_types=1);

namespace App\Service;

use App\Core\HttpException;
use App\Core\Session;
use App\Model\Utilisateur;
use App\Repository\UtilisateurRepository;

/** Authentification : utilisateur relu en base à chaque requête (compte supprimé/rétrogradé pris en compte). */
final class Auth
{
    private ?Utilisateur $courant = null;
    private bool $charge = false;

    public function __construct(private Session $session, private UtilisateurRepository $utilisateurs)
    {
    }

    public function utilisateur(): ?Utilisateur
    {
        if (!$this->charge) {
            $this->charge = true;
            $id = $this->session->utilisateurId();
            if ($id !== null) {
                $this->courant = $this->utilisateurs->trouver($id);
                if ($this->courant === null) {
                    $this->session->oublierUtilisateur();
                }
            }
        }
        return $this->courant;
    }

    public function exiger(): Utilisateur
    {
        return $this->utilisateur() ?? throw HttpException::nonConnecte();
    }

    public function tenterConnexion(string $email, string $motDePasse): ?Utilisateur
    {
        $l = $this->utilisateurs->trouverPourConnexion($email);
        if ($l && password_verify($motDePasse, $l['motDePasse'])) {
            $this->session->connecter((int) $l['id']);
            $this->charge = false;
            return $this->utilisateur();
        }
        return null;
    }

    public function deconnecter(): void
    {
        $this->session->detruire();
        $this->courant = null;
        $this->charge = true;
    }
}
