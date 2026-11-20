<?php
declare(strict_types=1);

namespace App\Model;

use App\Core\Request;

/** Critères de filtrage/tri de la liste des tâches, validés par liste blanche. */
final class FiltreTaches
{
    public const ECHEANCES = [
        'en_retard' => 'En retard',
        '7j'        => 'Dans les 7 jours',
        'sans'      => 'Sans échéance',
    ];
    public const TRIS = [
        'echeance' => 'Échéance',
        'titre'    => 'Titre',
        'etat'     => 'État',
        'recent'   => 'Plus récentes',
    ];
    public const RECHERCHE_MAX = 100;

    public function __construct(
        public readonly string $recherche = '',
        public readonly int $projet = 0,
        public readonly string $etat = '',
        public readonly string $echeance = '',
        public readonly bool $mesTaches = false,
        public readonly string $tri = 'echeance',
    ) {
    }

    public static function depuisRequete(Request $r): self
    {
        $etat = $r->get('etat');
        $echeance = $r->get('echeance');
        $tri = $r->get('tri');
        return new self(
            mb_substr($r->get('q'), 0, self::RECHERCHE_MAX),
            $r->getInt('projet') ?? 0,
            isset(Tache::ETATS[$etat]) ? $etat : '',
            isset(self::ECHEANCES[$echeance]) ? $echeance : '',
            $r->get('mes') !== '',
            isset(self::TRIS[$tri]) ? $tri : 'echeance',
        );
    }

    public function actif(): bool
    {
        return $this->recherche !== '' || $this->projet || $this->etat !== '' || $this->echeance !== ''
            || $this->mesTaches || $this->tri !== 'echeance';
    }
}
