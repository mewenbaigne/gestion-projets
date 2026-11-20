<?php
declare(strict_types=1);

// Contrôleur frontal : toutes les requêtes passent ici
$config = require __DIR__ . '/bootstrap.php';
(new App\Application($config))->executer();
