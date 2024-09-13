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
   1. `UBERSPACE_HOST` mit dem Hostnamen des Servers (z.B. lacerta.uberspace.de)
   2. `UBERSPACE_USER` mit dem Benutzernamen des Servers (z.B. fifth)
   3. `DEPLOY_KEY_PRIVATE` mit dem privaten SSH Key (Inhalt der Datei neuerKeyFuerGithub - ohne .pub inklusive BEGIN OPENSSH PRIVATE KEY / END OPENSSH PRIVATE KEY - falls uberspace in 1Pass Private Key exportieren)
   4. `UBERSPACE_PATH` mit dem Pfad zum Verzeichnis auf dem Server (z.B. cms.baukasten.matthiashacksteiner.net)
3. Auf dem Server den öffentlichen Key in die Datei `~/.ssh/authorized_keys` eintragen - Wert aus neuerKeyFuerGithub.pub

source: https://matthias-andrasch.eu/2021/tutorial-webseite-mittels-github-actions-deployment-zu-uberspace-uebertragen-rsync/

## 2. Installation Astro

- Neues Repo von Template erstellen https://github.com/matthacksteiner/baukasten
- Auf Netlify eine neue Seite erstellen: `npm run build` als Build Command
- Unter show advanced zwei Environment Variables setzen:
  `KIRBY_URL` : `https://baukasten.matthiashacksteiner.net`
  `NETLIFY_URL` : `https://baukasten.netlify.app`
- Eventuell Build nochmals starten.

## 3. Hooks anlegen

- Auf Netlify der Production Site unter Site Settings -> Build & Deploy -> Build Hooks einen neuen Hook erstellen.
- Den Hook in der Datei `site/config/config.php` eintragen.
- Unter settings/deploys#deploy-notifications zwei neue Outgoing webhooks erstellen:
  1. 'Deploy Succeeded' mit `https://cms.domain.de/webhook/netlify_deploy/success` als URL
  2. 'Deploy failed' mit `https://cms.domain.de/webhook/netlify_deploy/error` als URL
- Auf SSH Server unter site/config/config.php die Netlify die URL zum Hook eintragen

## Requirements for Headless Kirby CMS

### Accounts

- ftp or sftp
- hosting login
- ssh (if available)

### Subdomain

working cms subdomain including correct DNS settings and SSL certificat - for example: cms.domain.com

### Server

- Apache 2 or nginx
- URL rewriting

### PHP

- PHP 8.1, 8.2 or 8.3 (PHP 8.2 is recommended) - PHP 7.4 is not supported
- Either the gd extension or ImageMagick
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
