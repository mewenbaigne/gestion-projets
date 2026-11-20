<?php
declare(strict_types=1);

namespace App;

use App\Controller\AuthController;
use App\Controller\CommentaireController;
use App\Controller\TacheController;
use App\Core\Database;
use App\Core\HttpException;
use App\Core\Request;
use App\Core\Router;
use App\Core\Session;
use App\Core\View;
use App\Repository\CommentaireRepository;
use App\Repository\HistoriqueRepository;
use App\Repository\ProjetRepository;
use App\Repository\TacheRepository;
use App\Repository\UtilisateurRepository;
use App\Service\Auth;
use App\Service\Permissions;
use PDO;

/** Assemble les dépendances (conteneur simple) et exécute la requête. */
final class Application
{
    public readonly Request $request;
    public readonly Session $session;
    public readonly PDO $pdo;
    public readonly UtilisateurRepository $utilisateurs;
    public readonly ProjetRepository $projets;
    public readonly TacheRepository $taches;
    public readonly CommentaireRepository $commentaires;
    public readonly HistoriqueRepository $historique;
    public readonly Auth $auth;
    public readonly Permissions $permissions;
    public readonly View $vue;

    public function __construct(array $config)
    {
        $this->request      = new Request($config['base_url']);
        $this->session      = new Session();
        $this->pdo          = Database::connecter($config['db']);
        $this->utilisateurs = new UtilisateurRepository($this->pdo);
        $this->projets      = new ProjetRepository($this->pdo);
        $this->taches       = new TacheRepository($this->pdo);
        $this->commentaires = new CommentaireRepository($this->pdo);
        $this->historique   = new HistoriqueRepository($this->pdo);
        $this->auth         = new Auth($this->session, $this->utilisateurs);
        $this->permissions  = new Permissions($this->projets, $this->taches);
        $this->vue          = new View(__DIR__ . '/../templates', $this->session, $config['base_url']);
    }

    private function routes(): Router
    {
        $r = new Router();
        $r->get('/',  [AuthController::class, 'accueil']);
        $r->get('/login',  [AuthController::class, 'formulaire']);
        $r->post('/login', [AuthController::class, 'connexion']);
        $r->post('/logout', [AuthController::class, 'deconnexion']);

        $r->get('/taches', [TacheController::class, 'liste']);
        $r->get('/taches/nouvelle', [TacheController::class, 'formulaire']);
        $r->post('/taches/nouvelle', [TacheController::class, 'enregistrer']);
        $r->get('/taches/{id}', [TacheController::class, 'detail']);
        $r->get('/taches/{id}/modifier', [TacheController::class, 'formulaire']);
        $r->post('/taches/{id}/modifier', [TacheController::class, 'enregistrer']);
        $r->post('/taches/{id}/supprimer', [TacheController::class, 'supprimer']);
        $r->post('/taches/{id}/etat', [TacheController::class, 'changerEtat']);

        $r->post('/taches/{id}/commentaires', [CommentaireController::class, 'ajouter']);
        $r->post('/commentaires/{id}/repondre', [CommentaireController::class, 'repondre']);
        $r->post('/commentaires/{id}/supprimer', [CommentaireController::class, 'supprimer']);
        return $r;
    }

    public function executer(): void
    {
        $this->entetesSecurite();
        if (!$this->request->utf8Valide()) {
            http_response_code(400);
            exit('Requête invalide (encodage UTF-8 attendu).');
        }
        $this->session->demarrer();
        $this->vue->avecUtilisateur($this->auth->utilisateur());

        $trouve = $this->routes()->trouver($this->request->methode(), $this->request->chemin());
        if ($trouve === null) {
            http_response_code(404);
            $this->vue->afficher('404', 'Page introuvable');
            return;
        }

        [[$classe, $action], $params] = $trouve;
        try {
            (new $classe($this))->$action(...$params);
        } catch (HttpException $e) {
            $this->session->flash('erreur', $e->getMessage());
            $this->rediriger($e->getCode() === 401 ? '/login' : '/taches');
        }
    }

    /** CSP stricte (aucun script/style en ligne), anti-clickjacking, anti-sniffing, pas de cache des pages privées. */
    private function entetesSecurite(): void
    {
        header("Content-Security-Policy: default-src 'none'; script-src 'self'; style-src 'self'; img-src 'self'; "
            . "form-action 'self'; base-uri 'none'; frame-ancestors 'none'");
        header('X-Content-Type-Options: nosniff');
        header('X-Frame-Options: DENY');
        header('Referrer-Policy: same-origin');
        header('Permissions-Policy: camera=(), microphone=(), geolocation=()');
        header('Cache-Control: no-store');
    }

    public function rediriger(string $chemin): never
    {
        header('Location: ' . $this->vue->url($chemin));
        exit;
    }
}
