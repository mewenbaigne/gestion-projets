<?php
declare(strict_types=1);

namespace App\Controller;

use App\Core\Texte;

final class AuthController extends Controller
{
    public function accueil(): void
    {
        $this->rediriger($this->app->auth->utilisateur() ? '/taches' : '/login');
    }

    public function formulaire(): void
    {
        if ($this->app->auth->utilisateur()) {
            $this->rediriger('/taches');
        }
        $this->app->vue->afficher('auth/login', 'Connexion', ['erreur' => null, 'email' => '']);
    }

    public function connexion(): void
    {
        $this->verifierCsrf();
        $email = trim($this->app->request->post('email'));
        $mdp = $this->app->request->postBrut('password');
        // Email : ASCII uniquement (écarte les homoglyphes cyrilliques) ; mot de passe borné (bcrypt tronque à 72 octets)
        $user = Texte::ascii($email) && strlen($email) <= 255 && $mdp !== '' && strlen($mdp) <= 1000
            ? $this->app->auth->tenterConnexion($email, $mdp) : null;
        if ($user) {
            $this->flash('ok', 'Bienvenue ' . $user->prenom . ' !');
            $this->rediriger('/taches');
        }
        // Message identique que l'email existe ou non
        $this->app->vue->afficher('auth/login', 'Connexion', ['erreur' => 'Email ou mot de passe incorrect.', 'email' => $email]);
    }

    public function deconnexion(): void
    {
        $this->verifierCsrf();
        $this->app->auth->deconnecter();
        $this->flash('ok', 'Vous êtes déconnecté.');
        $this->rediriger('/login');
    }
}
