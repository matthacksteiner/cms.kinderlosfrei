# cms.baukasten

## 1. Installation Kirby

- Neues Repo von Termplate erstellen https://github.com/matthacksteiner/cms.baukasten
- In HTML Verzeichnis auf dem Server wechseln `cd /var/www/virtual/fifth/html`
- Klonen von Repository in Verzeichnis
- `composer install` via ssh auf dem Server ausführen

### Falls eigener Server

1. Gandi.net: DNS-Einstellungen anpassen (subdomain 10800 IN CNAME matthiashacksteiner.net.)
2. Domain erstellen `uberspace web domain add domain.de` und `uberspace web domain add www.domain.de`
3. Symlink erstellen `cd /var/www/virtual/fifth` + `ln -s html/domain.de/public domain.de` + `ln -s html/domain.de/public www.domain.de`

## 2. Installation Astro

- Neues Repo von Template erstellen https://github.com/matthacksteiner/baukasten
- Auf Netlify zwei neue Seiten erstellen: `npm run build:preview` und einmal mit `npm run build:production` als Build Command
- Unter show advanced die Environment Variables setzen: `KIRBY_URL` und als Value die URL des Kirby-Backends z.B. `https://super.matthiashacksteiner.net`
- Eventuell Build nochmals starten.

## 3. Hooks anlegen

- Auf Netlify der Production Site unter Site Settings -> Build & Deploy -> Build Hooks einen neuen Hook erstellen.
- Unter settings/deploys#deploy-notifications zwei neue Outgoing webhooks erstellen:
  1. 'Deploy Succeeded' mit `https://cms.domain.de/webhook/netlify_deploy/success` als URL
  2. 'Deploy failed' mit `https://cms.domain.de/webhook/netlify_deploy/error` als URL
- Auf SSH Server unter site/config/config.php die Netlify die URL zum Hook eintragen
