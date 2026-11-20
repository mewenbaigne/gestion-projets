<?php
declare(strict_types=1);

namespace App\Core;

/** Routeur : associe (méthode, motif /taches/{id}) à [Contrôleur::class, 'action']. */
final class Router
{
    /** @var array<int, array{string, string, array}> */
    private array $routes = [];

    public function get(string $motif, array $action): void
    {
        $this->routes[] = ['GET', $motif, $action];
    }

    public function post(string $motif, array $action): void
    {
        $this->routes[] = ['POST', $motif, $action];
    }

    /** @return array{array, int[]}|null [action, paramètres] ou null si aucune route */
    public function trouver(string $methode, string $chemin): ?array
    {
        foreach ($this->routes as [$m, $motif, $action]) {
            $regex = '#^' . preg_replace('#\{[a-z]+\}#', '(\d+)', $motif) . '$#';
            if ($m === $methode && preg_match($regex, $chemin, $match)) {
                return [$action, array_map('intval', array_slice($match, 1))];
            }
        }
        return null;
    }
}
