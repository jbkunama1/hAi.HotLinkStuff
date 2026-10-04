#!/bin/bash
set -eu

mkdir -p /var/www/data
chown -R www-data:www-data /var/www/data

# Keep a backup when a bind-mounted database is truncated or not SQLite.
for database in /var/www/data/heisser-scheiss.db /var/www/data/prompts.db; do
  if [ -f "$database" ] && [ -s "$database" ]; then
    if ! php -r '
      $pdo = new PDO("sqlite:" . $argv[1]);
      $pdo->query("PRAGMA schema_version")->fetchColumn();
    ' "$database" >/dev/null 2>&1; then
      backup="${database}.invalid.$(date +%Y%m%d%H%M%S)"
      mv "$database" "$backup"
      echo "WARN: Invalid SQLite database moved to $backup" >&2
      touch "$database"
      chown www-data:www-data "$database"
    fi
  fi
done

# Apache stays in the foreground for Docker.
exec apache2-foreground
