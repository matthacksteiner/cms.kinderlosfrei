# cms.baukasten

## Installation

- Neues Repo von Termplate erstellen https://github.com/matthacksteiner/cms.baukasten
- In HTML Verzeichnis auf dem Server wechseln `cd /var/www/virtual/fifth/html`
- Klonen von Repository in Verzeichnis
- `composer install` via ssh auf dem Server ausführen

### Falls eigener Server

1. Gandi.net: DNS-Einstellungen anpassen (subdomain 10800 IN CNAME matthiashacksteiner.net.)
2. Domain erstellen `uberspace web domain add domain.de` und `uberspace web domain add www.domain.de`
3. Symlink erstellen `cd /var/www/virtual/fifth` + `ln -s html/domain.de/public domain.de` + `ln -s html/domain.de/public www.domain.de`
