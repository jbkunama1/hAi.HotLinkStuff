# 🔥🧠 hAI.HotLinkStuff

> Die kombinierte Web-App aus **HotStuff** und **PromptSave** – für Links, Bilder, Textbausteine und Prompts in einer gemeinsamen Oberfläche mit PHP + SQLite.[file:8][file:9][file:11]

![Repo Status](https://img.shields.io/badge/status-active-16a34a?style=for-the-badge)
![Stack](https://img.shields.io/badge/stack-PHP%20%7C%20SQLite%20%7C%20JS-0f766e?style=for-the-badge)
![Deployment](https://img.shields.io/badge/deploy-Portainer%20Stack-2563eb?style=for-the-badge)
![License](https://img.shields.io/badge/license-MIT-f59e0b?style=for-the-badge)

---

## ✨ Überblick

**hAI.HotLinkStuff** vereint zwei bestehende Apps in einem gemeinsamen Projekt:

- 🔥 **HotStuff** für **Text, Bilder und Links**.[file:8][file:9]
- 🧠 **PromptSave** für **Prompts mit CSV-Import/-Export**.[file:11]

Die Anwendung nutzt eine gemeinsame Shell, aber weiterhin **zwei getrennte SQLite-Datenbanken**, damit bestehende Daten einfach übernommen und weiterverwendet werden können.[file:9][file:11]

---

## 🧩 Module

| Modul | Zweck | Datenbank | Kernfunktionen |
|------|------|------|------|
| 🔥 HotStuff | Sammlung für Links, Bilder, Text und Ideen | `heisser-scheiss.db` [file:9] | Anlegen, bearbeiten, löschen, filtern, JSON-Export [file:8][file:9] |
| 🧠 PromptSave | Verwaltung von Prompts | `prompts.db` [file:11] | Anlegen, bearbeiten, löschen, kopieren, CSV-Import/-Export [file:11] |

---

## 🚀 Features

### Gemeinsame App

- 🧭 Eine gemeinsame Startoberfläche mit Tabs für beide Module.
- 🔐 Passwortgeschützte API.
- 🐳 Deployment über Docker und Portainer-Stack.
- 🗄️ Zwei getrennte SQLite-Dateien für einfache Migration und Backups.[file:9][file:11]

### HotStuff

- 📝 Texte und Anleitungen speichern.[file:8]
- 🖼️ Bilder per Upload ablegen.[file:8]
- 🔗 Links verwalten.[file:8]
- 🔍 Suche und Kategorienfilter.[file:8]
- 💾 JSON-Export.[file:9]

### PromptSave

- ✍️ Prompts strukturiert speichern.[file:11]
- 🧠 Kategorien: `gemini`, `chatgpt`, `general`.[file:11]
- 📥 CSV-Import.[file:11]
- 📤 CSV-Export.[file:11]
- 📋 Prompt direkt in die Zwischenablage kopieren.

---

## 🧰 Verwendeter Stack

```text
Frontend: HTML, CSS, Vanilla JavaScript
Backend: PHP
Datenbanken: SQLite (2 getrennte DBs)
Deployment: Docker + Portainer
```

---

## 📁 Projektstruktur

```text
hAI.HotLinkStuff/
├── app/
│   ├── index.html
│   ├── api.php
│   ├── modules/
│   │   ├── hotstuff.html
│   │   └── promptsave.html
│   └── assets/
│       ├── app.css
│       └── app.js
├── data/
│   ├── heisser-scheiss.db
│   └── prompts.db
├── docker/
│   ├── Dockerfile
│   └── stack.yml
├── README.md
└── .gitignore
```

---

## 🗄️ Datenbanken

### 🔥 `heisser-scheiss.db`

Tabelle `items` mit u. a. folgenden Feldern:[file:9]

- `id`
- `title`
- `category`
- `content`
- `link`
- `image`
- `created_at`
- `updated_at`

### 🧠 `prompts.db`

Tabelle `prompts` mit u. a. folgenden Feldern:[file:11]

- `id`
- `uuid`
- `title`
- `content`
- `category`
- `created_at`
- `updated_at`
- `created_timestamp`
- `updated_timestamp`

---

## ⚙️ Installation

### 1. Repository klonen

```bash
git clone https://github.com/jbkunama1/hAi.HotLinkStuff.git
cd hAi.HotLinkStuff
```

### 2. Datenbanken bereitstellen

Lege deine produktiven DB-Dateien in `data/` ab:

```text
data/heisser-scheiss.db
data/prompts.db
```

### 3. Stack deployen

Per Portainer oder lokal mit Docker Compose/Stack.

---

## 🐳 Portainer / Docker

### Dockerfile

- basiert auf `php:8.2-apache`
- installiert `pdo_sqlite`
- aktiviert Apache Rewrite
- mountet die SQLite-Dateien über ein Datenverzeichnis

### Stack

`docker/stack.yml` bindet das Datenverzeichnis als Volume ein und setzt das API-Passwort per ENV-Variable.

Beispiel:

```yaml
environment:
  APP_PASSWORD: ${APP_PASSWORD:-hotstuff}
volumes:
  - ../data:/var/www/data
```

---

## 🔐 Sicherheit

Aktuell ist die API passwortgeschützt und erwartet das Passwort über Requests an `api.php`.[file:9][file:11]

Empfohlen für Produktion:

- Passwort über `APP_PASSWORD` als ENV setzen.
- Repo nie mit echten `.db`-Dateien committen.
- Optional Reverse Proxy / HTTPS / Basic Auth zusätzlich davorschalten.

---

## 🌐 GitHub Pages

Für das Repo kann zusätzlich eine **GitHub-Pages-Startseite** genutzt werden, z. B. als Projekt-Landingpage oder Repo-Preview.

Die eigentliche App läuft serverseitig mit PHP + SQLite und daher **nicht direkt auf GitHub Pages**, sondern im Docker-/Portainer-Setup.[file:9][file:11]

---

## 💡 Roadmap

- [x] HotStuff und PromptSave technisch zusammenführen.
- [x] Gemeinsame Shell mit Tabs.
- [x] Zentrale API für beide Module.
- [x] Zwei getrennte SQLite-Datenbanken beibehalten.
- [ ] Einheitlicher globaler Login ohne doppelten Modul-Login.
- [ ] Optional: einheitliches Corporate Design.
- [ ] Optional: Tags, Favoriten, Volltextsuche, Backup-UI.

---

## 🛠️ Einsatzideen

- Unterrichtsmaterialien und Links sammeln.
- Prompts für Unterricht, Medienbildung und Informatik verwalten.
- Technik- und Maker-Projekte dokumentieren.
- Eigene Tool-Sammlung für Schule, Unterricht und Automatisierung pflegen.

---

## 🏷️ Tags

`php` `sqlite` `vanilla-js` `docker` `portainer` `teacher-tools` `prompt-management` `knowledge-base` `materialsammlung` `linksammlung`

---

## 📄 Lizenz

MIT License

---

## 🙌 Autor

**TheRealTeacher / Daniel Lienhard**

Praxisnahe Tools für Unterricht, Technikprojekte und digitale Organisation.
