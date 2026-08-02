#!/bin/bash
# Berechtigungen für das gemountete Datenverzeichnis setzen
chown -R www-data:www-data /var/www/data
# Apache im Vordergrund starten
exec apache2-foreground
