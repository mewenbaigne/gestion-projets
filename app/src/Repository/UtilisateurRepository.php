<?php
declare(strict_types=1);

namespace App\Repository;

use App\Model\Utilisateur;

final class UtilisateurRepository extends Repository
{
    public function trouver(int $id): ?Utilisateur
    {
        $s = $this->pdo->prepare('SELECT id, nom, prenom, email, isAdmin FROM utilisateurs WHERE id = :id');
        $s->execute([':id' => $id]);
        $l = $s->fetch();
        return $l ? Utilisateur::depuisLigne($l) : null;
    }

    /** @return array{id:int, prenom:string, motDePasse:string}|null */
    public function trouverPourConnexion(string $email): ?array
    {
        $s = $this->pdo->prepare('SELECT id, prenom, motDePasse FROM utilisateurs WHERE email = :email');
        $s->execute([':email' => $email]);
        return $s->fetch() ?: null;
    }
}
