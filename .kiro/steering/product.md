# Product Overview

Baukasten CMS is a headless content management system built on Kirby CMS that works in conjunction with an Astro-based frontend. It provides a modern, block-based content architecture with multi-language support and automated deployment capabilities.

## Key Features

- **Headless Architecture**: Provides JSON API endpoints for frontend consumption
- **Block-Based Content**: Flexible content structure using reusable content blocks
- **Multi-Language Support**: Built-in internationalization with language-specific content
- **Automated Deployments**: Triggers frontend builds when content changes via Netlify hooks
- **Performance Optimized**: API caching with intelligent invalidation
- **Modern Image Handling**: WebP conversion and ThumbHash generation for optimized loading

## Architecture

The system consists of two main components:

- **Backend (this repository)**: Kirby CMS providing headless API endpoints
- **Frontend (separate repository)**: Astro-based application consuming JSON from CMS

Content is managed through the Kirby Panel and exposed via JSON endpoints that are consumed by the frontend application. The CMS automatically triggers frontend rebuilds when content changes.

## Target Use Cases

- Modern websites requiring flexible content management
- Multi-language sites with complex content structures
- Projects needing separation between content management and presentation
- Sites requiring automated deployment workflows
