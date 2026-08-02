#!/bin/bash
set -euo pipefail

if [ "$#" -eq 0 ]; then
  set -- apache2-foreground
fi

if [ "$1" = "apache2-foreground" ]; then
  # Berechtigungen für das gemountete Datenverzeichnis setzen
  if ! chown -R www-data:www-data /var/www/data; then
    echo "Failed to set ownership on /var/www/data" >&2
    exit 1
  fi
fi

exec "$@"
