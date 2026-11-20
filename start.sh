#!/bin/bash
# Démarre MySQL (127.0.0.1:33077) et phpMyAdmin (http://127.0.0.1:8897)
cd "$(dirname "$0")" || exit 1

if ! lsof -iTCP:33077 -sTCP:LISTEN -nP >/dev/null 2>&1; then
  nohup mysqld --no-defaults --basedir=/opt/homebrew/opt/mysql --datadir="$PWD/mysql-data" \
    --port=33077 --bind-address=127.0.0.1 --mysqlx=OFF \
    --socket="$PWD/mysql.sock" --pid-file="$PWD/mysql.pid" --log-error="$PWD/mysql-error.log" \
    >/dev/null 2>&1 &
  disown
  until mysqladmin --no-defaults -h127.0.0.1 -P33077 -uroot ping >/dev/null 2>&1; do sleep 1; done
fi

if ! lsof -iTCP:8897 -sTCP:LISTEN -nP >/dev/null 2>&1; then
  nohup php -S 127.0.0.1:8897 -t "$PWD/phpmyadmin" >"$PWD/phpmyadmin-server.log" 2>&1 &
  disown
fi

echo "MySQL       : 127.0.0.1:33077 (root, sans mot de passe)"
echo "phpMyAdmin  : http://127.0.0.1:8897"
