# Blocks System

The Baukasten-CMS implements a comprehensive block-based content system that allows editors to build flexible page layouts using predefined content blocks. This system is central to the headless CMS architecture and provides structured content for the Astro frontend.

## Overview

The blocks system consists of:

- **Block Blueprints**: YAML definitions in `site/blueprints/blocks/`
- **Block Processing**: PHP logic in the `baukasten-blocks` plugin
- **JSON Conversion**: Structured data output for frontend consumption
- **Multi-language Support**: Localized block content

## Available Block Types

### Text Blocks

- **`text`**: Rich text content with formatting options
- **`title`**: Headline blocks with configurable heading levels
- **`code`**: Code blocks with syntax highlighting support

### Media Blocks

- **`image`**: Single images with responsive settings and copyright information
- **`vector`**: SVG graphics and illustrations
- **`slider`**: Image carousels with navigation controls
- **`gallery`**: Photo galleries with lightbox functionality

### Layout Blocks

- **`columns`**: Multi-column layouts with flexible widths
- **`grid`**: Complex grid layouts with nested content
- **`divider`**: Visual separators and spacing elements
- **`line`**: Horizontal rules and decorative lines

### Interactive Blocks

- **`button`**: Call-to-action buttons with link objects
- **`buttonBar`**: Multiple buttons in a row
- **`menu`**: Navigation menus with structured links
- **`accordion`**: Collapsible content sections

### Content Blocks

- **`card`**: Content cards with images and text
- **`quote`**: Blockquotes and testimonials
- **`iconList`**: Lists with icon graphics

## Block Blueprint Structure

Blocks are defined as YAML blueprints in `site/blueprints/blocks/`. Here's an example of an image block:

```yaml
# site/blueprints/blocks/image.yml
name: Image
icon: image
preview: image
fields:
  image:
    label: Image
    type: files
    layout: cards
    template: image
    min: 1
    max: 1

  alt:
    label: Alt Text
    type: text
    help: Alternative text for accessibility

  caption:
    label: Caption
    type: text

  ratio:
    label: Aspect Ratio (Desktop)
    type: select
    options:
      16/9: 16:9 (Widescreen)
      4/3: 4:3 (Standard)
      1/1: 1:1 (Square)
      3/2: 3:2 (Photo)

  ratioMobile:
    label: Aspect Ratio (Mobile)
    type: select
    options:
      16/9: 16:9 (Widescreen)
      4/3: 4:3 (Standard)
      1/1: 1:1 (Square)

  abovefold:
    label: Above the Fold
    type: toggle
    help: Mark as priority for loading optimization
```

## Block Processing

The `baukasten-blocks` plugin handles the conversion of Kirby blocks to JSON format:

### Main Processing Function

```php
function processBlocks($blocks) {
    $result = [];
    foreach ($blocks as $block) {
        $blockData = getBlockArray($block);
        if ($blockData) {
            $result[] = $blockData;
        }
    }
    return $result;
}
```

### Individual Block Processing

Each block type has specific processing logic:

```php
function getBlockArray(\Kirby\Cms\Block $block) {
    $blockArray = [
        "id"      => $block->id(),
        "type"    => $block->type(),
        "content" => [],
    ];

    switch ($block->type()) {
        case 'image':
            $blockArray['content'] = $block->toArray()['content'];
            $image = null;
            if ($file1 = $block->image()->toFile()) {
                $ratioMobile = explode('/', $block->ratioMobile()->value());
                $ratio       = explode('/', $block->ratio()->value());
                $image       = getImageArray($file1, $ratio, $ratioMobile);

                // Add copyright-specific properties
                $image = array_merge($image, [
                    'copyrighttoggle' => $file1->copyrighttoggle()->toBool(false),
                    'copyrighttitle' => $file1->copyrightobject()->toObject()->copyrighttitle()->value(),
                    // ... additional copyright fields
                ]);
            }
            $blockArray['content']['abovefold'] = $block->abovefold()->toBool(false);
            $blockArray['content']['image'] = $image;
            break;

        // ... other block types
    }

    return $blockArray;
}
```

## Complex Layout Blocks

### Columns Block

The columns block allows for flexible multi-column layouts:

```php
case 'columns':
    $layout = $block->layout()->toLayouts()->first();
    if ($layout !== null) {
        $blockArray['content'] = [
            "columns" => processColumns($layout->columns())
        ];
    }
    break;
```

### Grid Block

Grid blocks support complex layouts with multiple rows and columns:

```php
case 'grid':
    $allGrids = [];
    $title    = $block->title()->value();
    foreach ($block->grid()->toLayouts() as $layout) {
        $allGrids[] = [
            "id"      => $layout->id(),
            "columns" => processColumns($layout->columns()),
        ];
    }
    $blockArray['content'] = [
        "title" => $title,
        "grid"  => $allGrids,
    ];
    break;
```

### Column Processing

```php
function processColumns($columnsCollection) {
    $columns = [];
    foreach ($columnsCollection as $column) {
        $columns[] = [
            "id"     => $column->id(),
            "width"  => $column->width(),
            "span"   => $column->span(),
            "nested" => true,
            "blocks" => processBlocks($column->blocks())
        ];
    }
    return $columns;
}
```

## Image Processing

Images receive enhanced metadata for responsive design and optimization:

```php
function getImageArray($file, $ratio = null, $ratioMobile = null) {
    return [
        "src"         => (string)$file->url(),
        "alt"         => (string)$file->alt()->or(''),
        "width"       => $file->width(),
        "height"      => $file->height(),
        "thumbhash"   => (string)$file->thumbhash()->value(),
        "ratio"       => $ratio,
        "ratioMobile" => $ratioMobile,
        "caption"     => (string)$file->caption()->value(),
        "copyright"   => (string)$file->copyright()->value(),
    ];
}
```

### SVG Processing

SVG files receive special handling:

```php
function getSvgArray($file) {
    return [
        "src"    => (string)$file->url(),
        "alt"    => (string)$file->alt()->or(''),
        "source" => file_get_contents($file->root()),
        "width"  => $file->width(),
        "height" => $file->height(),
    ];
}
```

## Link Objects

Many blocks support link objects for navigation:

```php
// Button block with link object
case 'button':
    $blockArray['content'] = $block->toArray()['content'];
    $linkobject = [];
    if ($block->linkobject()->isNotEmpty()) {
        $linkobject = getLinkArray($block->linkobject());
        $blockArray['content']['linkobject'] = $linkobject;
    }
    $blockArray['content']['buttonlocal'] = $block->buttonlocal()->toBool(false);
    break;
```

## JSON Output Example

A processed image block produces this JSON structure:

```json
{
	"id": "block-uuid",
	"type": "image",
	"content": {
		"image": {
			"src": "/media/pages/about/image.jpg",
			"alt": "Team photo",
			"width": 1200,
			"height": 800,
			"thumbhash": "base64-encoded-hash",
			"ratio": ["16", "9"],
			"ratioMobile": ["4", "3"],
			"caption": "Our amazing team",
			"copyrighttoggle": false,
			"copyrighttitle": ""
		},
		"caption": "Our team working together",
		"abovefold": true
	}
}
```

## Extending the Block System

### Adding New Block Types

1. **Create Blueprint**: Add a new YAML file in `site/blueprints/blocks/`
2. **Add Processing Logic**: Extend the `getBlockArray()` function in the blocks plugin
3. **Frontend Component**: Create corresponding Astro component in the frontend

### Custom Fields

Blocks can include custom fields defined in `site/blueprints/fields/`:

```yaml
customField:
  type: myCustomField
  label: Custom Field
  # ... field configuration
```

## Performance Considerations

- **Lazy Loading**: Images marked as `abovefold: false` can be lazy-loaded
- **Thumbhash**: Provides instant image placeholders
- **Responsive Images**: Multiple aspect ratios for different devices
- **SVG Inlining**: SVG source included for performance optimization

## Multi-Language Support

Blocks automatically support multi-language content:

- Field values are localized based on current language
- Image alt text and captions can be translated
- Link objects respect language-specific URLs

## Content Validation

Blocks include validation rules:

- Required fields prevent publishing incomplete content
- File type restrictions ensure proper media handling
- Field constraints maintain content consistency

This comprehensive block system provides the foundation for flexible, maintainable content management while ensuring optimal performance and user experience in the frontend.
