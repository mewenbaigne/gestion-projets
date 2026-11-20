<?php
declare(strict_types=1);

namespace App\Model;

/** Commentaire d'une tâche ; peut être une réponse (idParent) et porter ses propres réponses. */
final class Commentaire
{
    /** @var Commentaire[] */
    public array $reponses = [];

    public function __construct(
        public readonly int $id,
        public readonly int $idTache,
        public readonly ?int $idAuteur,
        public readonly ?int $idParent,
        public readonly string $date,
        public readonly string $message,
        public readonly ?string $auteurNom,
        public readonly bool $supprime = false,
    ) {
    }

    public static function depuisLigne(array $l): self
    {
        return new self((int) $l['id'], (int) $l['id_tache'],
            $l['id_utilisateur'] !== null ? (int) $l['id_utilisateur'] : null,
            $l['id_parent'] !== null ? (int) $l['id_parent'] : null,
            $l['date'], $l['message'],
            $l['prenom'] !== null ? $l['prenom'] . ' ' . $l['nom'] : null,
            (bool) $l['supprime']);
    }

    /** Nombre de réponses visibles (hors commentaires supprimés) dans toute la descendance. */
    public function compterReponses(): int
    {
        $n = 0;
        foreach ($this->reponses as $r) {
            $n += ($r->supprime ? 0 : 1) + $r->compterReponses();
        }
        return $n;
    }
}
