# Technology Stack

## Core Framework

- **Kirby CMS 4.x**: PHP-based flat-file CMS providing the foundation
- **PHP 8.2+**: Required minimum version for modern language features

## Key Dependencies

- **Composer**: PHP dependency management
- **getkirby/cms**: Core CMS functionality
- **bnomei/kirby3-dotenv**: Environment variable management
- **johannschopplich/kirby-deploy-trigger**: Automated deployment hooks
- **tobimori/kirby-thumbhash**: Modern image processing and placeholders
- **fabianmichael/kirby-meta**: SEO and metadata management
- **microman/kirby-column-blocks**: Enhanced block layouts

## Development Environment

- **Laravel Herd**: Local development server (configured in `herd.yml`)
- **Composer**: Package management and autoloading
- **Git**: Version control with GitHub integration

## Build & Deployment Commands

### Initial Setup

```bash
# Install PHP dependencies
composer install

# Initialize project (removes template files)
./init-project.sh

# Configure environment
cp .env.example .env
# Edit .env with your specific settings
```

### Development

```bash
# Start local development server (if using Herd)
# Server automatically runs on configured domain

# Access Kirby Panel
# Navigate to your-domain.test/panel
```

### Deployment

```bash
# Automated deployment via GitHub Actions
# Configured in .github/workflows/ (if present)

# Manual deployment
# Upload files to web server pointing to /public directory
```

## Environment Configuration

- **DEPLOY_URL**: Netlify build hook for triggering frontend rebuilds
- **PHP Extensions Required**: gd/ImageMagick, ctype, curl, dom, filter, hash, iconv, json, libxml, mbstring, openssl, SimpleXML

## API Endpoints

- `/index.json`: Complete site structure and page index
- `/global.json`: Site-wide configuration and design tokens
- `/{page-uri}.json`: Individual page content in JSON format

## Caching Strategy

- API response caching with intelligent invalidation
- Automatic cache clearing on content updates
- WebP image conversion and optimization
