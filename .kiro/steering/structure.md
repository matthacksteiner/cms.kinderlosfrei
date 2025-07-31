# Project Structure & Organization

## Directory Layout

```
├── content/                 # Content files and media (managed via Panel)
├── kirby/                   # Core Kirby CMS files (DO NOT MODIFY)
├── public/                  # Web-accessible files
│   ├── assets/             # Static assets (CSS, images, favicon)
│   ├── media/              # Auto-generated thumbnails and user uploads
│   └── index.php           # Entry point
├── site/                    # Main customization directory
│   ├── blueprints/         # Content structure definitions (YAML)
│   ├── config/             # Configuration files
│   ├── controllers/        # PHP controllers for templates
│   ├── languages/          # Multi-language definitions
│   ├── models/             # Custom page models
│   ├── plugins/            # Custom functionality extensions
│   └── templates/          # PHP templates for rendering
├── storage/                # Cache, sessions, accounts
└── vendor/                 # Composer dependencies
```

## Key Conventions

### File Naming

- **Blueprints**: Lowercase with hyphens (`page-type.yml`)
- **Templates**: Match blueprint names (`page-type.php`)
- **Content files**: `page.txt` (default), `page.en.txt` (English), `page.de.txt` (German)
- **Plugins**: Descriptive names with prefix (`baukasten-feature-name/`)

### Content Organization

- Each page = folder with text file + optional media
- Hierarchical structure determines URLs
- Multi-language content uses file suffixes
- Block-based content structure for flexibility

## Critical Directories

### `site/blueprints/`

- **`blocks/`**: Content block definitions (text, image, video, etc.)
- **`pages/`**: Page type definitions and field structures
- **`fields/`**: Custom field types and configurations
- **`files/`**: File upload and display configurations

### `site/plugins/`

- **`baukasten-blocks/`**: Core block processing and JSON conversion
- **`baukasten-layouts/`**: Layout system definitions
- **`baukasten-kirby-routes/`**: API endpoints for frontend integration
- **`baukasten-programmable-blueprints/`**: Dynamic blueprint generation

### `site/config/`

- **`config.php`**: Main configuration (routes, caching, plugins)
- Environment-specific settings
- API endpoint definitions

## Development Guidelines

### When Working with Content Structure

- Modify blueprints in `site/blueprints/` to change Panel interface
- Use block-based approach for flexible content
- Follow multi-language conventions with file suffixes

### When Adding Functionality

- Create plugins in `site/plugins/` for custom features
- Use controllers in `site/controllers/` for complex page logic
- Templates in `site/templates/` handle rendering (though this is headless)

### When Configuring

- Environment variables go in `.env` (not tracked in Git)
- Main config in `site/config/config.php`
- API routes defined in config for JSON endpoints

## Important Files

- **`composer.json`**: PHP dependencies and autoloading
- **`.env`**: Environment-specific variables (DEPLOY_URL, etc.)
- **`herd.yml`**: Local development server configuration
- **`public/index.php`**: Application entry point
- **`site/config/config.php`**: Core configuration and API routes
