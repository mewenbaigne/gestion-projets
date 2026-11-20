#!/bin/bash
# Arrête phpMyAdmin puis MySQL (arrêt propre)
cd "$(dirname "$0")" || exit 1

pkill -f "php -S 127.0.0.1:8897" 2>/dev/null
mysqladmin --no-defaults -h127.0.0.1 -P33077 -uroot shutdown 2>/dev/null

echo "Serveurs arrêtés."
