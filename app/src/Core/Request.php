<?php
declare(strict_types=1);

namespace App\Core;

/** Accès typé aux données de la requête HTTP. */
final class Request
{
    public function __construct(private string $baseUrl = '')
    {
    }

    public function methode(): string
    {
        return strtoupper($_SERVER['REQUEST_METHOD'] ?? 'GET');
    }

    public function chemin(): string
    {
        $chemin = parse_url($_SERVER['REQUEST_URI'] ?? '/', PHP_URL_PATH) ?: '/';
        if ($this->baseUrl !== '' && str_starts_with($chemin, $this->baseUrl)) {
            $chemin = substr($chemin, strlen($this->baseUrl));
        }
        return '/' . trim($chemin, '/');
    }

    /** Toutes les entrées texte passent par Texte::nettoyer() ; seules les méthodes *Brut() renvoient la valeur telle quelle. */
    public function get(string $cle, string $defaut = ''): string
    {
        return is_string($_GET[$cle] ?? null) ? Texte::nettoyer($_GET[$cle]) : $defaut;
    }

    /** Texte sur une seule ligne. */
    public function post(string $cle, string $defaut = ''): string
    {
        return is_string($_POST[$cle] ?? null) ? Texte::nettoyer($_POST[$cle]) : $defaut;
    }

    /** Texte multiligne (description, commentaire). */
    public function postMultiligne(string $cle): string
    {
        return is_string($_POST[$cle] ?? null) ? Texte::nettoyer($_POST[$cle], true) : '';
    }

    /** Valeur brute, non modifiée (mots de passe uniquement). */
    public function postBrut(string $cle): string
    {
        return is_string($_POST[$cle] ?? null) ? $_POST[$cle] : '';
    }

    /** @return string[] */
    public function postTableau(string $cle): array
    {
        $v = $_POST[$cle] ?? [];
        return is_array($v) ? array_values(array_filter($v, 'is_string')) : [];
    }

    /** Toutes les clés et valeurs de $_GET/$_POST doivent être de l'UTF-8 valide (sinon 400). */
    public function utf8Valide(): bool
    {
        $ok = static function (mixed $v) use (&$ok): bool {
            if (is_array($v)) {
                foreach ($v as $k => $x) {
                    if (!$ok((string) $k) || !$ok($x)) {
                        return false;
                    }
                }
                return true;
            }
            return !is_string($v) || mb_check_encoding($v, 'UTF-8');
        };
        return $ok($_GET) && $ok($_POST);
    }

    public function getInt(string $cle): ?int
    {
        $v = filter_var($_GET[$cle] ?? null, FILTER_VALIDATE_INT, ['options' => ['min_range' => 1]]);
        return $v === false ? null : $v;
    }

    public function postInt(string $cle): ?int
    {
        $v = filter_var($_POST[$cle] ?? null, FILTER_VALIDATE_INT, ['options' => ['min_range' => 1]]);
        return $v === false ? null : $v;
    }
}
