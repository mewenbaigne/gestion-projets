<?php
// Configuration de l'application (MySQL de ce poste : port 33077, cf. ../start.sh)
return [
    'base_url' => '', // à renseigner si l'app est servie dans un sous-dossier (ex. '/gestion-projets/app')
    'db' => [
        'host'    => '127.0.0.1',
        'port'    => 33077,
        'name'    => 'gestion_projets',
        'user'    => 'root',
        'pass'    => '',
        'charset' => 'utf8mb4',
    ],
];
