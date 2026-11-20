<?php
declare(strict_types=1);

namespace App\Model;

final class Utilisateur
{
    public function __construct(
        public readonly int $id,
        public readonly string $nom,
        public readonly string $prenom,
        public readonly string $email,
        public readonly bool $admin,
    ) {
    }

    public static function depuisLigne(array $l): self
    {
        return new self((int) $l['id'], $l['nom'], $l['prenom'], $l['email'], (bool) $l['isAdmin']);
    }

    public function nomComplet(): string
    {
        return $this->prenom . ' ' . $this->nom;
    }
}
