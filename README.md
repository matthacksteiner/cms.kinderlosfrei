# Baukasten CMS

This is the Kirby CMS backend part of the Baukasten project. It works in conjunction with the [Baukasten Frontend](https://github.com/matthacksteiner/baukasten).

**Template Release:** v0.0.0

## Project Structure

```
├── Frontend (separate repository)
│   └── Astro-based frontend application
└── Backend (this repository)
    └── Kirby CMS installation
```

## Updating the Template

### Automated Update

Run the script `update-template-version.sh` to fetch and merge the latest changes from the template repository. Use Git Bash or WSL on Windows.

### Manual Update

1. Add the template repository as a remote:
   ```bash
   git remote add template https://github.com/matthacksteiner/cms.baukasten
   ```
2. Fetch all changes:
   ```bash
   git fetch --all
   ```
3. Merge the changes:
   ```bash
   git merge template/main --allow-unrelated-histories
   ```
4. Run the script `update-template-version.sh` to update the version in `README.md` or manually edit the file.

---

## Semantic Versioning

This project uses semantic versioning for automatic version management. The version number is incremented based on commit message patterns:

- `major:` in commit message: Increments major version (e.g., 1.0.0 -> 2.0.0)
- `feat:` in commit message: Increments minor version (e.g., 1.0.0 -> 1.1.0)
- `fix:` in commit message: Increments patch version (e.g., 1.0.0 -> 1.0.1)

> **Note:** If no pattern is found, the version is incremented by 0.0.1.

---

# Setup Instructions

## 1. Kirby CMS Installation

1. Create a new repository from the [CMS Baukasten template](https://github.com/matthacksteiner/cms.baukasten).
2. Navigate to the HTML directory on your server:
   ```bash
   cd /var/www/virtual/fifth/html
   ```
3. Clone the repository into the directory.
4. Remove the default content from the new repository:
   ```bash
   git rm -r --cached content
   ```
5. Run `composer install` via SSH on the server.
6. Set the document root to `/public`.

### For Custom Servers

1. **DNS Settings:** Adjust DNS settings on Gandi.net (e.g., `subdomain 10800 IN CNAME matthiashacksteiner.net`).
2. **Add Domain:** Create the domain on Uberspace:
   ```bash
   uberspace web domain add domain.de
   uberspace web domain add www.domain.de
   ```
3. **Create Symlinks:**
   ```bash
   cd /var/www/virtual/fifth
   ln -s html/domain.de/public domain.de
   ln -s html/domain.de/public www.domain.de
   ```

### GitHub Actions for Automated Deployment

1. Generate an SSH key on your local machine:
   ```bash
   ssh-keygen -f neuerKeyFuerGithub -t ed25519 -a 100
   ```
   Leave the password empty.
2. In the new repository, go to **Settings > Secrets and variables > Actions** and add the following secrets:
   - `UBERSPACE_HOST`: Hostname of the server (e.g., `lacerta.uberspace.de`).
   - `UBERSPACE_USER`: Username for the server (e.g., `fifth`).
   - `DEPLOY_KEY_PRIVATE`: Private SSH key (content of `neuerKeyFuerGithub` file, including `BEGIN OPENSSH PRIVATE KEY` and `END OPENSSH PRIVATE KEY`).
   - `UBERSPACE_PATH`: Path to the directory on the server (e.g., `cms.baukasten.matthiashacksteiner.net`).
3. Add the public key to the server's `~/.ssh/authorized_keys` file (use the content of `neuerKeyFuerGithub.pub`).

Source: [Matthias Andrasch's Tutorial](https://matthias-andrasch.eu/2021/tutorial-webseite-mittels-github-actions-deployment-zu-uberspace-uebertragen-rsync/)

---

## Requirements for Headless Kirby CMS

### Accounts

- FTP or SFTP access
- Hosting login credentials
- SSH access (if available)

### Subdomain

A working CMS subdomain with correct DNS settings and an SSL certificate (e.g., `cms.domain.com`).

### Server

- Apache 2 or Nginx
- URL rewriting enabled

### PHP

- PHP 8.1, 8.2, or 8.3 (PHP 8.2 recommended; PHP 7.4 is not supported)
- Required PHP extensions:
  - gd or ImageMagick
  - ctype
  - curl
  - dom
  - filter
  - hash
  - iconv
  - json
  - libxml
  - mbstring
  - openssl
  - SimpleXML

---

This guide provides a clear and structured setup process for combining a Kirby CMS backend with an Astro frontend. Follow the steps carefully to ensure a smooth installation and deployment..
