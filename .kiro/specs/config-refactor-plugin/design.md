# Design Document

## Overview

This design outlines the refactoring of the monolithic `site/config/config.php` file into a clean configuration file supported by a dedicated Kirby plugin called `baukasten-api`. The plugin will encapsulate all helper functions, API endpoints, and routing logic while maintaining complete backward compatibility.

## Architecture

### Plugin Structure

```
site/plugins/baukasten-api/
├── index.php                 # Plugin entry point and registration
├── src/
│   ├── Api/
│   │   ├── Routes.php        # Route definitions and handlers
│   │   ├── IndexApi.php      # Index JSON endpoint logic
│   │   └── GlobalApi.php     # Global JSON endpoint logic
│   ├── Helpers/
│   │   ├── NavigationHelper.php    # Navigation-related functions
│   │   ├── LanguageHelper.php      # Language/translation functions
│   │   ├── UrlHelper.php           # URL generation and routing
│   │   └── SiteDataHelper.php      # Site data extraction functions
│   └── Services/
│       └── FlatUrlResolver.php     # Flat URL resolution logic
└── tests/
    ├── ApiTest.php           # API endpoint tests
    ├── HelperTest.php        # Helper function tests
    └── RoutingTest.php       # URL routing tests
```

### Refactored Config Structure

The new `site/config/config.php` will be minimal and focused:

```php
<?php
return [
    'debug' => false,
    'auth' => ['methods' => ['password', 'password-reset']],
    'panel.install' => true,
    // ... other core config
    'routes' => \BaukastenApi\Api\Routes::getRoutes(),
];
```

## Components and Interfaces

### 1. Plugin Entry Point (`index.php`)

- Registers the plugin with Kirby
- Autoloads all helper functions into global namespace
- Initializes route handlers

### 2. Route Handler (`Api/Routes.php`)

- Static method `getRoutes()` returns all route definitions
- Delegates to specific API classes for endpoint logic
- Maintains current route patterns and behavior

### 3. API Endpoint Classes

- `IndexApi::handle()` - Processes index.json requests
- `GlobalApi::handle()` - Processes global.json requests
- Each class encapsulates related functionality

### 4. Helper Function Classes

- `NavigationHelper` - Page navigation, siblings, breadcrumbs
- `LanguageHelper` - Multi-language support functions
- `UrlHelper` - URL generation and flat URL logic
- `SiteDataHelper` - Site configuration data extraction

### 5. Flat URL Resolver Service

- Encapsulates complex flat URL resolution logic
- Handles section toggle behavior
- Manages URL conflict resolution

## Data Models

### Helper Function Organization

```php
// Navigation functions
NavigationHelper::getPageNavigation($page)
NavigationHelper::getNavigationSiblings($page, $effectiveParent)
NavigationHelper::getEffectiveParent($page)

// URL functions
UrlHelper::generatePageUri($page, $respectSectionToggle = true)
UrlHelper::getSectionToggleState()
UrlHelper::handleFlatUrlResolution($path)

// Language functions
LanguageHelper::getTranslations($kirby)
LanguageHelper::getAllLanguages($kirby)
LanguageHelper::getDefaultLanguage($kirby)

// Site data functions
SiteDataHelper::getFavicon($site)
SiteDataHelper::getNavigation($site, $field)
SiteDataHelper::getLogoFile($site)
SiteDataHelper::getFonts($site)
// ... etc
```

### API Response Structure

Both API endpoints will maintain their current JSON structure:

- `index.json` - Array of page objects with navigation data
- `global.json` - Site-wide configuration and design tokens
- Individual page JSON endpoints - Page-specific content

## Error Handling

### Plugin Loading

- Graceful fallback if plugin fails to load
- Clear error messages for missing dependencies
- Validation of required Kirby version

### Route Resolution

- Maintain current 404 handling behavior
- Preserve redirect logic for flat URLs
- Error logging for debugging route issues

### API Responses

- Consistent error response format
- Proper HTTP status codes
- Fallback values for missing data

## Testing Strategy

### Unit Tests

- Test each helper function in isolation
- Mock Kirby objects and dependencies
- Verify output matches current implementation

### Integration Tests

- Test complete API endpoint responses
- Compare JSON output before/after refactoring
- Validate URL routing scenarios

### Regression Tests

- Capture current API responses as baseline
- Automated comparison after refactoring
- Test edge cases and error conditions

### Test Data Setup

- Create minimal test content structure
- Mock site configuration data
- Test with multiple languages enabled

## Migration Strategy

### Phase 1: Plugin Creation

1. Create plugin structure and basic files
2. Move helper functions to appropriate classes
3. Implement route handlers

### Phase 2: Config Refactoring

1. Update config.php to use plugin routes
2. Remove helper functions from config
3. Test all endpoints work correctly

### Phase 3: Validation

1. Run comprehensive test suite
2. Compare API responses before/after
3. Validate URL routing behavior

### Rollback Plan

- Keep backup of original config.php
- Plugin can be disabled to revert changes
- Clear documentation of changes made

## Performance Considerations

### Plugin Loading

- Minimal overhead during Kirby initialization
- Lazy loading of helper classes when needed
- Efficient autoloading strategy

### Route Processing

- No performance impact on route resolution
- Maintain current caching behavior
- Optimize helper function calls

### Memory Usage

- Similar memory footprint to current implementation
- Proper class organization reduces duplication
- Efficient object instantiation
