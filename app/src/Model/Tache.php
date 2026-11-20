<?php
declare(strict_types=1);

namespace App\Model;

final class Tache
{
    public const ETATS = [
        'a_faire'  => 'À faire',
        'en_cours' => 'En cours',
        'termine'  => 'Terminée',
        'annule'   => 'Annulée',
    ];

    public function __construct(
        public readonly int $id,
        public readonly int $idProjet,
        public readonly string $projetNom,
        public readonly string $nom,
        public readonly ?string $description,
        public readonly ?string $dateDebut,
        public readonly ?string $dateFin,
        public readonly string $etat,
    ) {
    }

    public static function depuisLigne(array $l): self
    {
        return new self((int) $l['id'], (int) $l['id_projet'], $l['projet_nom'] ?? '', $l['nom'],
            $l['description'], $l['dateDebut'], $l['dateFin'], $l['etat']);
    }

    public function libelleEtat(): string
    {
        return self::ETATS[$this->etat];
    }
}
