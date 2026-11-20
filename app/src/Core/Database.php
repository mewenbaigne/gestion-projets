<?php
declare(strict_types=1);

namespace App\Core;

use PDO;
use PDOException;

/** Connexion PDO unique, partagée par tous les dépôts. */
final class Database
{
    private static ?PDO $pdo = null;

    public static function connecter(array $c): PDO
    {
        if (self::$pdo === null) {
            $dsn = "mysql:host={$c['host']};port={$c['port']};dbname={$c['name']};charset={$c['charset']}";
            try {
                self::$pdo = new PDO($dsn, $c['user'], $c['pass'], [
                    PDO::ATTR_ERRMODE            => PDO::ERRMODE_EXCEPTION,
                    PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
                    PDO::ATTR_EMULATE_PREPARES   => false,
                ]);
            } catch (PDOException $e) {
                error_log($e->getMessage());
                http_response_code(500);
                exit('Erreur de connexion à la base de données.');
            }
        }
        return self::$pdo;
    }
}
