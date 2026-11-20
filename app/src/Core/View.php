<?php
declare(strict_types=1);

namespace App\Core;

/** Rendu des gabarits PHP (templates/) dans la mise en page commune. */
final class View
{
    public function __construct(
        private string $dossier,
        private Session $session,
        private string $baseUrl,
        private ?object $utilisateur = null,
    ) {
    }

    public function avecUtilisateur(?object $utilisateur): self
    {
        $this->utilisateur = $utilisateur;
        return $this;
    }

    public static function e(mixed $valeur): string
    {
        return htmlspecialchars((string) $valeur, ENT_QUOTES | ENT_SUBSTITUTE, 'UTF-8');
    }

    public function url(string $chemin = ''): string
    {
        return $this->baseUrl . $chemin;
    }

    public function csrf(): string
    {
        return '<input type="hidden" name="csrf" value="' . self::e($this->session->csrfToken()) . '">';
    }

    /** Rend un gabarit seul (fragment). */
    public function fragment(string $gabarit, array $donnees = []): string
    {
        extract($donnees, EXTR_SKIP);
        $v = $this;
        ob_start();
        require $this->dossier . '/' . $gabarit . '.php';
        return (string) ob_get_clean();
    }

    /** Rend un gabarit dans la mise en page et l'envoie. */
    public function afficher(string $gabarit, string $titre, array $donnees = []): void
    {
        echo $this->fragment('layout', [
            'titre'   => $titre,
            'contenu' => $this->fragment($gabarit, $donnees),
            'flash'   => $this->session->consommerFlash(),
            'user'    => $this->utilisateur,
        ]);
    }
}
