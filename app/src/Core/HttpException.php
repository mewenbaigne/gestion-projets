<?php
declare(strict_types=1);

namespace App\Core;

use RuntimeException;

/** Erreur « métier » affichée à l'utilisateur (403 accès refusé, 404 introuvable, 401 non connecté). */
class HttpException extends RuntimeException
{
    public static function nonConnecte(): self
    {
        return new self('Veuillez vous connecter.', 401);
    }

    public static function interdit(string $message = "Vous n'avez pas le droit d'effectuer cette action."): self
    {
        return new self($message, 403);
    }

    public static function introuvable(string $message = 'Ressource introuvable.'): self
    {
        return new self($message, 404);
    }
}
