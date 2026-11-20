<?php
declare(strict_types=1);

namespace App\Core;

/** Session, messages flash et jeton CSRF. */
final class Session
{
    public function demarrer(): void
    {
        if (session_status() === PHP_SESSION_NONE) {
            ini_set('session.use_strict_mode', '1');
            ini_set('session.use_only_cookies', '1');
            session_set_cookie_params([
                'httponly' => true,
                'samesite' => 'Strict',
                'secure'   => !empty($_SERVER['HTTPS']) && $_SERVER['HTTPS'] !== 'off',
            ]);
            session_start();
        }
    }

    public function utilisateurId(): ?int
    {
        return isset($_SESSION['user_id']) ? (int) $_SESSION['user_id'] : null;
    }

    public function connecter(int $id): void
    {
        session_regenerate_id(true);
        $_SESSION['user_id'] = $id;
    }

    public function oublierUtilisateur(): void
    {
        unset($_SESSION['user_id']);
    }

    public function detruire(): void
    {
        $_SESSION = [];
        if (ini_get('session.use_cookies')) {
            $p = session_get_cookie_params();
            setcookie(session_name(), '', ['expires' => time() - 42000, 'path' => $p['path'], 'httponly' => true, 'samesite' => 'Strict']);
        }
        session_destroy();
        session_start();
    }

    public function flash(string $type, string $message): void
    {
        $_SESSION['flash'] = ['type' => $type, 'message' => $message];
    }

    /** Lit puis efface le message flash. */
    public function consommerFlash(): ?array
    {
        $flash = $_SESSION['flash'] ?? null;
        unset($_SESSION['flash']);
        return $flash;
    }

    public function csrfToken(): string
    {
        if (empty($_SESSION['csrf'])) {
            $_SESSION['csrf'] = bin2hex(random_bytes(32));
        }
        return $_SESSION['csrf'];
    }

    public function csrfValide(string $jeton): bool
    {
        $attendu = (string) ($_SESSION['csrf'] ?? '');
        return $attendu !== '' && hash_equals($attendu, $jeton);
    }
}
