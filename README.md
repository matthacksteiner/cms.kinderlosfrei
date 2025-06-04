# Baukasten CMS

This is the Kirby CMS backend part of the Baukasten project. It works in conjunction with the [Baukasten Frontend](https://github.com/matthacksteiner/baukasten).

## Project Structure

```
├── Frontend (separate repository)
│   └── Astro-based frontend application
└── Backend (this repository)
    └── Kirby CMS installation
```

## Updating the Template

### Automated Update

Run the script `update-template-version.sh` to fetch and merge the latest changes from the template repository. Use Git Bash or WSL on Windows

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

# Setup Instructions

## 1. Kirby CMS Installation

1. Create a new repository from the [CMS Baukasten template](https://github.com/matthacksteiner/cms.baukasten).
2. Run `composer install`.
3. Set the document root to `/public`.
4. Visit the site with `/panel` at the end of the URL.

## 2. Environment Configuration

This project uses the [kirby3-dotenv plugin](https://github.com/bnomei/kirby3-dotenv) to manage environment variables securely.

### Setup Environment Variables

1. **Copy the example file:**

   ```bash
   cp .env.example .env
   ```

2. **Configure your environment variables in `.env`:**

   ```env
   # Kirby CMS Environment Variables

   # Netlify Deploy Hook URL
   # Get this from your Netlify site settings > Build & deploy > Build hooks
   DEPLOY_URL=https://api.netlify.com/build_hooks/YOUR_BUILD_HOOK_ID
   ```

3. **Update your deploy URL:**
   - Go to your Netlify site settings
   - Navigate to **Build & deploy > Build hooks**
   - Copy your build hook URL and replace `YOUR_BUILD_HOOK_ID` in the `.env` file

### Available Environment Variables

`DEPLOY_URL`: Netlify build hook URL for automated deployments

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
   - `DEPLOY_URL`: Your Netlify build hook URL (e.g., `https://api.netlify.com/build_hooks/YOUR_BUILD_HOOK_ID`).
3. Add the public key to the server's `~/.ssh/authorized_keys` file (use the content of `neuerKeyFuerGithub.pub`).

**Note:** The `.env` file is automatically created on the server during deployment using the GitHub Secrets, ensuring sensitive information stays secure while being available where needed.

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

- PHP 8.2+ (PHP 7.4 is not supported)
- Required PHP extensions: gd or ImageMagick, ctype, curl, dom, filter, hash, iconv, json, libxml, mbstring, openssl, SimpleXML

---

This guide provides a clear and structured setup process for combining a Kirby CMS backend with an Astro frontend. Follow the steps carefully to ensure a smooth installation and deployment.
