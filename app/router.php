<?php
// Script de routage du serveur intégré : php -S 127.0.0.1:8896 -t app app/router.php
$chemin = parse_url($_SERVER['REQUEST_URI'], PHP_URL_PATH);
if ($chemin !== '/' && is_file(__DIR__ . $chemin) && !str_ends_with($chemin, '.php')) {
    return false; // fichier statique (CSS...)
}
require __DIR__ . '/index.php';
