#!/bin/bash
# Lance MySQL + phpMyAdmin (start.sh) puis l'application sur http://127.0.0.1:8896
cd "$(dirname "$0")" || exit 1
./start.sh >/dev/null

if ! lsof -iTCP:8896 -sTCP:LISTEN -nP >/dev/null 2>&1; then
  nohup php -S 127.0.0.1:8896 -t "$PWD/app" "$PWD/app/router.php" >"$PWD/app-server.log" 2>&1 &
  disown
fi
echo "Application : http://127.0.0.1:8896"
echo "phpMyAdmin  : http://127.0.0.1:8897  (base : gestion_projets)"
