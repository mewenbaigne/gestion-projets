# Gestion de projets (PHP orienté objet)

Application de gestion de projets et de tâches : projets, rôles (admin, chef de projet, membre), tâches avec
attribution, filtres et tri, commentaires avec réponses imbriquées, historique des modifications.

## Architecture

```
app/
  index.php        contrôleur frontal
  bootstrap.php    autoloader PSR-4 + config
  config/          paramètres (base de données)
  src/
    Core/          Database, Session, Request, Router, View, Texte (assainissement Unicode)
    Model/         Utilisateur, Tache, Commentaire, FiltreTaches
    Repository/    accès aux données (PDO, requêtes préparées)
    Service/       Auth, Permissions
    Controller/    Auth, Tache, Commentaire
  templates/       vues PHP
sql/               schema.sql, seed.sql, migrations
```

## Sécurité

Requêtes préparées, échappement systématique, jeton CSRF, CSP stricte (aucun script/style en ligne),
validation UTF-8, suppression des caractères invisibles/de contrôle, refus des mots mélangeant latin et
cyrillique, sessions durcies.

## Lancer

Prérequis : PHP 8.1+ (extensions `pdo_mysql`, `mbstring`, `intl`) et MySQL.

```bash
mysql -uroot < sql/schema.sql
mysql -uroot < sql/seed.sql
# adapter app/config/config.php (hôte, port, identifiants)
php -S 127.0.0.1:8896 -t app app/router.php
```

Comptes de démo (mot de passe `password123`) : `admin@example.com`, `chef@example.com`,
`dev@example.com`, `designer@example.com`.

Sur Apache, le `.htaccess` de `app/` redirige tout vers `index.php`.
Les scripts `start.sh` / `start-app.sh` lancent une instance MySQL locale isolée (Homebrew, port 33077).
