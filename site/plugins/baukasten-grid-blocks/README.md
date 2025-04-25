# Baukasten Grid Blocks

## Overview

With this plugin you can use layouts right within any blocks field in Baukasten CMS.

This is a drop-in replacement for the abandoned microman/kirby-grid-blocks plugin.

## Installation

The plugin is installed automatically through Composer:

```
composer require baukasten/grid-blocks
```

## Block field usage in the frontend

```php
<?php foreach ($page->myGrid()->toBlocks() as $grid): ?>
  <h2><?= $grid->title() ?></h2>

  <!--
  Customize the Grid-Block and use any custom fields:
  <?= $grid->yourCustomFields() ?>
  -->

  <?= $grid ?>
<?php endforeach ?>
```

## Customization

### Grid block

Simply copy the main `grid.yml` from `site/plugins/baukasten-grid-blocks/blueprints/blocks` to your project's `site/blueprints/blocks` folder. The latter one will be used by Kirby instead of the one provided by the plugin.

You can customize it to your needs:

```yaml
textContent:
  label: Content with Text
  type: blocks
  fieldsets:
    grid:
      extends: blocks/grid
      fields:
        grid:
          layouts:
            - "1/1"
          fieldsets:
            - heading
            - text
        title:
          label: Title
        margin:
          type: range
          after: px
          default: "5"
          min: 0
          max: 200
```

### Snippet

Copy the block snippet `grid.php` from `site/plugins/baukasten-grid-blocks/snippets/blocks` to your project's `site/snippets/blocks` folder. The latter one will be used by Kirby instead of the one provided by the plugin.

## License

MIT
