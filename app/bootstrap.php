<?php
declare(strict_types=1);

// Autoloader PSR-4 minimal : App\Foo\Bar => src/Foo/Bar.php
spl_autoload_register(static function (string $classe): void {
    $prefixe = 'App\\';
    if (strncmp($classe, $prefixe, strlen($prefixe)) !== 0) {
        return;
    }
    $fichier = __DIR__ . '/src/' . str_replace('\\', '/', substr($classe, strlen($prefixe))) . '.php';
    if (is_file($fichier)) {
        require $fichier;
    }
});

return require __DIR__ . '/config/config.php';
