# GHCR + Portainer Setup

## 1. GitHub vorbereiten

- Repo nach `main` pushen.
- Unter GitHub Actions prüfen, ob der Workflow `Build and Push Docker Image to GHCR` erfolgreich durchläuft.
- Danach sollte das Image verfügbar sein als:
  - `ghcr.io/jbkunama1/hai-hotlinkstuff:latest`

## 2. Falls GHCR-Image privat ist

Auf dem Docker-/Portainer-Host ggf. bei GHCR anmelden:

```bash
docker login ghcr.io -u GITHUB_USERNAME
```

Nutze dafür ein GitHub Token mit Paket-/Package-Rechten.[web:37][web:51]

## 3. Portainer Stack

In Portainer einen neuen Stack anlegen und den Inhalt von `docker/stack.yml` verwenden.

### Environment Variable in Portainer setzen

```env
APP_PASSWORD=deinSicheresPasswort
```

## 4. Datenbanken

Die DB-Dateien müssen bereits im Server-Verzeichnis `/data` liegen:

```text
/data/heisser-scheiss.db
/data/prompts.db
```

Der Stack mountet dieses Verzeichnis nach `/var/www/data` in den Container.

## 5. Testen

App danach aufrufen, z. B. über:

- `http://SERVER-IP:8095/app/`

Wenn ein Reverse Proxy davor sitzt, dann über die gewünschte Domain.
