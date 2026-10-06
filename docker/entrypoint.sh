#!/bin/bash
set -eu

log() {
  echo "[startup] $*"
}

log "Container startup begonnen"
log "Zeit: $(date -Is)"
log "User: $(id -u):$(id -g) ($(id -un))"
log "Arbeitsverzeichnis: $(pwd)"
log "APP_PASSWORD gesetzt: $([ -n "${APP_PASSWORD:-}" ] && echo ja || echo nein)"
log "PHP: $(php -v | head -n 1)"
log "Apache: $(apache2 -v | head -n 1)"

if php -m | grep -qx 'pdo_sqlite'; then
  log "PHP-Erweiterung pdo_sqlite: aktiv"
else
  log "FEHLER: PHP-Erweiterung pdo_sqlite fehlt"
  exit 1
fi

if php -m | grep -qx 'sqlite3'; then
  log "PHP-Erweiterung sqlite3: aktiv"
else
  log "WARNUNG: PHP-Erweiterung sqlite3 fehlt"
fi

mkdir -p /var/www/data
log "Datenverzeichnis: /var/www/data"
log "Datenverzeichnis vor Rechteanpassung:"
ls -lad /var/www/data
find /var/www/data -maxdepth 1 -type f -printf '[startup] Datei: %f (%s Bytes, %u:%g, %m)\n' | sort || true

chown -R www-data:www-data /var/www/data
log "Datenverzeichnis nach Rechteanpassung:"
ls -lad /var/www/data

if [ -w /var/www/data ]; then
  log "Datenverzeichnis beschreibbar: ja"
else
  log "FEHLER: Datenverzeichnis nicht beschreibbar"
  exit 1
fi

# Keep a backup when a bind-mounted database is truncated or not SQLite.
for database in /var/www/data/heisser-scheiss.db /var/www/data/prompts.db; do
  name="$(basename "$database")"
  if [ ! -e "$database" ]; then
    log "$name: nicht vorhanden, wird beim ersten API-Aufruf angelegt"
    continue
  fi

  log "$name: vorhanden ($(stat -c '%s Bytes, %U:%G, Modus %a' "$database"))"
  if [ -s "$database" ]; then
    if ! php -r '
      $pdo = new PDO("sqlite:" . $argv[1]);
      $pdo->query("PRAGMA schema_version")->fetchColumn();
    ' "$database" >/dev/null 2>&1; then
      backup="${database}.invalid.$(date +%Y%m%d%H%M%S)"
      mv "$database" "$backup"
      log "$name: WARNUNG - ungültige SQLite-Datei nach $backup verschoben"
      touch "$database"
      chown www-data:www-data "$database"
      log "$name: leere SQLite-Datei neu angelegt"
    else
      integrity="$(php -r '
        $pdo = new PDO("sqlite:" . $argv[1]);
        echo $pdo->query("PRAGMA integrity_check")->fetchColumn();
      ' "$database")"
      log "$name: SQLite-Integrität: $integrity"
      php -r '
        $pdo = new PDO("sqlite:" . $argv[1]);
        $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
        $tables = $pdo->query("SELECT name FROM sqlite_master WHERE type = \"table\" AND name NOT LIKE \"sqlite_%\" ORDER BY name")->fetchAll(PDO::FETCH_COLUMN);
        if (!$tables) {
            echo "[startup] " . basename($argv[1]) . ": Tabellen: keine\n";
            exit;
        }
        foreach ($tables as $table) {
            $quoted = "\"" . str_replace("\"", "\"\"", $table) . "\"";
            $count = $pdo->query("SELECT COUNT(*) FROM " . $quoted)->fetchColumn();
            echo "[startup] " . basename($argv[1]) . ": Tabelle " . $table . ", Zeilen " . $count . "\n";
        }
        $expected = basename($argv[1]) === "heisser-scheiss.db" ? "items" : "prompts";
        if (!in_array($expected, $tables, true)) {
            echo "[startup] " . basename($argv[1]) . ": WARNUNG - erwartete Tabelle " . $expected . " fehlt\n";
        }
      ' "$database"
    fi
  else
    log "$name: leer, Tabellen werden beim ersten API-Aufruf angelegt"
  fi
done

log "Webroot: /var/www/html"
ls -lad /var/www/html /var/www/html/app /var/www/html/app/api.php
log "Apache-Konfiguration geprüft, Übergabe an apache2-foreground"

# Apache stays in the foreground for Docker.
exec apache2-foreground
