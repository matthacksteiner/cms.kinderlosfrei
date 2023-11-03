# cms.baukasten

Template: https://github.com/matthacksteiner/cms.baukasten

## Update von Template Origin

`git remote add template https://github.com/matthacksteiner/cms.baukasten`

`git fetch --all`

`git merge template/main --allow-unrelated-histories`

## 1. Installation Kirby

- Neues Repo von Termplate erstellen https://github.com/matthacksteiner/cms.baukasten
- In HTML Verzeichnis auf dem Server wechseln `cd /var/www/virtual/fifth/html`
- Klonen von Repository in Verzeichnis
- default content aus dem neuen Repo entfernen `git rm -r --cached content`
- `composer install` via ssh auf dem Server ausführen
- Document Root auf `/public` setzen

### Falls eigener Server

1. Gandi.net: DNS-Einstellungen anpassen (subdomain 10800 IN CNAME matthiashacksteiner.net.)
2. Domain erstellen `uberspace web domain add domain.de` und `uberspace web domain add www.domain.de`
3. Symlink erstellen `cd /var/www/virtual/fifth` + `ln -s html/domain.de/public domain.de` + `ln -s html/domain.de/public www.domain.de`

### Github Actions für automatisches Deployment

1. Auf lokalen Rechner den ssh key erzeugen `ssh-keygen -f neuerKeyFuerGithub -t ed25519 -a 100` - Passwort leer lassen.
2. Im neuen Repo unter Settings -> Secrets and variables für Actions 4 Secrets anlegen:
   1. `UBERSPACE_HOST` mit dem Hostnamen des Servers
   2. `UBERSPACE_USER` mit dem Benutzernamen des Servers
   3. `DEPLOY_KEY_PRIVATE` mit dem privaten SSH Key (Inhalt der Datei neuerKeyFuerGithub - ohne .pub)
   4. `UBERSPACE_PATH` mit dem Pfad zum Verzeichnis auf dem Server
3. Auf dem Server den öffentlichen Key in die Datei `~/.ssh/authorized_keys` eintragen - Wert aus neuerKeyFuerGithub.pub

source: https://matthias-andrasch.eu/2021/tutorial-webseite-mittels-github-actions-deployment-zu-uberspace-uebertragen-rsync/

## 2. Installation Astro

- Neues Repo von Template erstellen https://github.com/matthacksteiner/baukasten
- Auf Netlify zwei neue Seiten erstellen: `npm run build:preview` und einmal mit `npm run build:production` als Build Command
- Unter show advanced die Environment Variables setzen: `KIRBY_URL` und als Value die URL des Kirby-Backends z.B. `https://super.matthiashacksteiner.net`
- Eventuell Build nochmals starten.

## 3. Hooks anlegen

- Auf Netlify der Production Site unter Site Settings -> Build & Deploy -> Build Hooks einen neuen Hook erstellen.
- Den Hook in der Datei `site/config/config.php` eintragen.
- Unter settings/deploys#deploy-notifications zwei neue Outgoing webhooks erstellen:
  1. 'Deploy Succeeded' mit `https://cms.domain.de/webhook/netlify_deploy/success` als URL
  2. 'Deploy failed' mit `https://cms.domain.de/webhook/netlify_deploy/error` als URL
- Auf SSH Server unter site/config/config.php die Netlify die URL zum Hook eintragen

## Requirements for Headless Kirby CMS

### General

working cms subdomain including correct DNS settings - for example: cms.domain.com

### Server

- Apache 2 or nginx
- URL rewriting

### PHP

- PHP 8.0, 8.1 or 8.2 (PHP 8.1 is recommended)
- Either the gd extension or ImageMagick (see below)
- ctype extension
- curl extension
- dom extension
- filter extension
- hash extension
- iconv extension
- json extension
- libxml extension
- mbstring extension
- openssl extension
- SimpleXML extension
