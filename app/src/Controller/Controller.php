<?php
declare(strict_types=1);

namespace App\Controller;

use App\Application;
use App\Core\HttpException;
use App\Model\Tache;
use App\Model\Utilisateur;

abstract class Controller
{
    public function __construct(protected Application $app)
    {
    }

    protected function utilisateur(): Utilisateur
    {
        return $this->app->auth->exiger();
    }

    /** Vérifie le jeton CSRF des requêtes POST. */
    protected function verifierCsrf(): void
    {
        if (!$this->app->session->csrfValide($this->app->request->post('csrf'))) {
            http_response_code(403);
            exit('Requête invalide (jeton CSRF).');
        }
    }

    protected function flash(string $type, string $message): void
    {
        $this->app->session->flash($type, $message);
    }

    protected function rediriger(string $chemin): never
    {
        $this->app->rediriger($chemin);
    }

    /** Charge une tâche accessible à l'utilisateur, sinon lève une erreur. */
    protected function tacheAccessible(int $id, Utilisateur $u): Tache
    {
        $t = $this->app->taches->trouver($id) ?? throw HttpException::introuvable('Tâche introuvable.');
        if (!$this->app->permissions->estMembre($u, $t->idProjet)) {
            throw HttpException::interdit("Vous n'avez pas accès à cette tâche.");
        }
        return $t;
    }
}
