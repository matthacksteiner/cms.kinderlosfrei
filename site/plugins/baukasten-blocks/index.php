<?php

use Kirby\Cms\App as Kirby;

Kirby::plugin('baukasten-blocks/baukasten-blocks', [
    'options' => [],
    'components' => [],
    'fields' => [],
    'snippets' => [],
    'templates' => [],
    'blueprints' => [],
    'translations' => [],
]);

function getBlockArray(\Kirby\Cms\Block $block)
{
    $blockArray = [
        "id" => $block->id(),
        "type" => $block->type(),
        "content" => [],
    ];

    switch ($block->type()) {

        case 'columns':
            $layout = $block->layout()->toLayouts()->first();

            if ($layout !== null) {
                foreach ($layout->columns() as $column) {
                    $columnArray = [
                        "id" => $column->id(),
                        "width" => $column->width(),
                        "span" => $column->span(),
                        "nested" => true,
                        "blocks" => []
                    ];

                    $blocks = $column->blocks();

                    foreach ($blocks as $block) {
                        $blockData = getBlockArray($block);

                        if (!$blockData) {
                            continue;
                        }

                        $columnArray['blocks'][] = $blockData;
                    }

                    $columns[] = $columnArray;
                }
                $blockArray['content'] = [
                    "columns" => $columns,
                ];
            }
            break;

        case 'grid':
            $allGrids = [];
            $title = $block->title()->value();

            foreach ($block->grid()->toLayouts() as $layout) {
                $columns = [];

                foreach ($layout->columns() as $column) {
                    $columnArray = [
                        "id" => $column->id(),
                        "width" => $column->width(),
                        "span" => $column->span(),
                        "nested" => true,
                        "blocks" => []
                    ];

                    $blocks = $column->blocks();

                    foreach ($blocks as $block) {
                        $blockData = getBlockArray($block);

                        if (!$blockData) {
                            continue;
                        }

                        $columnArray['blocks'][] = $blockData;
                    }

                    $columns[] = $columnArray;
                }

                $allGrids[] = [
                    "id" => $layout->id(),
                    "columns" => $columns,
                ];
            }

            $blockArray['content'] = [
                "title" => $title,
                "grid" => $allGrids,
            ];

            break;

        case 'image':
            $blockArray['content'] = $block->toArray()['content'];
            $image = null;

            if ($file1 = $block->image()->toFile()) {
                $ratioMobile = explode('/', $block->ratioMobile()->value());
                $ratio = explode('/', $block->ratio()->value());
                $image = getImageArray($file1, $ratio, $ratioMobile);

                // Add copyright-specific properties
                $image = array_merge($image, [
                    'copyrighttoggle' => $file1->copyrighttoggle()->toBool(false),
                    'copyrighttitle' => $file1->copyrightobject()->toObject()->copyrighttitle()->value(),
                    'copyrighttextfont' => $file1->copyrightobject()->toObject()->textfont()->value(),
                    'copyrighttextsize' => $file1->copyrightobject()->toObject()->textsize()->value(),
                    'copyrighttextcolor' => $file1->copyrightobject()->toObject()->textColor()->value(),
                    'copyrighbackgroundcolor' => $file1->copyrightobject()->toObject()->copyrightBackground()->value(),
                    'copyrightposition' => $file1->copyrightobject()->toObject()->copyrightposition()->value(),
                ]);
            }

            $blockArray['content']['image'] = $image;
            break;

        case "vector":
            $blockArray['content'] = $block->toArray()['content'];
            $image = null;
            if ($file1 = $block->image()->toFile()) {
                $image = getImageArray($file1);
            }
            $blockArray['content']['image'] = $image;
            break;

        case 'slider':
            $blockArray['content'] = $block->toArray()['content'];
            $images = [];

            $ratioMobile = explode('/', $block->ratioMobile()->value());
            $ratio = explode('/', $block->ratio()->value());

            foreach ($block->images()->toFiles() as $file) {
                $image = getImageArray($file, $ratio, $ratioMobile);
                $image['toggle'] = $file->toggle()->toBool(false);
                $images[] = $image;
            }

            $blockArray['content']['images'] = $images;
            $blockArray['content']['toggle'] = $block->toggle()->toBool(false);
            break;

        case "menu":
            $blockArray['content'] = $block->toArray()['content'];
            foreach ($block->nav()->toStructure() as $key => $item) {
                $linkobject = [];
                if ($item->linkobject()->isNotEmpty()) {
                    $linkobject = getLinkArray($item->linkobject());
                }
                $blockArray['content']['nav'][$key]["linkobject"] = $linkobject;
            }

            break;

        case 'button':
            $blockArray['content'] = $block->toArray()['content'];

            $linkobject = [];
            if ($block->linkobject()->isNotEmpty()) {
                $linkobject = getLinkArray($block->linkobject());
                $blockArray['content']['linkobject'] = $linkobject;
            }

            $blockArray['content']['buttonlocal'] = $block->buttonlocal()->toBool(false);

            break;

        case 'buttonBar':
            $blockArray['content'] = $block->toArray()['content'];

            // Process each button in the structure
            foreach ($block->buttons()->toStructure() as $key => $button) {
                $linkobject = [];
                if ($button->linkObject()->isNotEmpty()) {
                    $linkobject = getLinkArray($button->linkObject());
                }
                $blockArray['content']['buttons'][$key]['linkobject'] = $linkobject;
            }

            $blockArray['content']['buttonlocal'] = $block->buttonlocal()->toBool(false);

            break;

        case 'text':
            $blockArray['content'] = $block->toArray()['content'];
            $blockArray['content']['text'] = (string)$block->text();
            break;

        case "iconlist":
            $blockArray['content'] = $block->toArray()['content'];

            foreach ($block->list()->toStructure() as $key => $item) {
                $icon = null;
                if ($file = $item->icon()->toFile()) {
                    $icon = [
                        'url' => $file->url(),
                        'alt' => (string)$file->alt(),
                        'source' => file_get_contents($file->root()),
                    ];
                }

                $blockArray['content']['list'][$key]["icon"] = $icon;
            }

            break;

        case 'code':
            $blockArray['content'] = $block->toArray()['content'];
            $blockArray['content']['code'] = (string)$block->code();
            break;

        case 'video':
            $blockArray['content'] = $block->toArray()['content'];
            $video = null;
            $thumb = null;
            if ($file1 = $block->file()->toFile()) {
                $video = [
                    'url' => $file1->url(),
                    'alt' => (string)$file1->alt(),
                    'identifier' => $file1->identifier()->value(),
                    'classes' => $file1->classes()->value(),
                ];
            }
            if ($file2 = $block->thumbnail()->toFile()) {
                $thumb = [
                    'url' => $file2->url(),
                    'alt' => (string)$file2->alt(),
                ];
            }
            $blockArray['content']['thumbnail'] = $thumb;
            $blockArray['content']['file'] = $video;
            break;

        case 'card':
            $blockArray['content'] = $block->toArray()['content'];

            // Extract fields from card.yml
            $blockArray['content']['title'] = (string)$block->title();
            $blockArray['content']['text'] = (string)$block->text();
            $blockArray['content']['hoverToggle'] = $block->hoverToggle()->toBool(false);
            $blockArray['content']['hovertext'] = (string)$block->hovertext();

            if ($file = $block->image()->toFile()) {
                $blockArray['content']['image'] = [
                    'url' => $file->url(),
                    'alt' => (string)$file->alt(),
                ];
            }
            break;

        default:
            $blockArray['content'] = $block->toArray()['content'];
            break;
    }

    // Extract metadata attributes
    if (isset($blockArray['content']['metadata']['attributes'])) {
        $metadataAttributes = $blockArray['content']['metadata']['attributes'];
        $attributes = [];
        foreach ($metadataAttributes as $attr) {
            $key = $attr['attribute'];
            $value = $attr['value'] === 'true' ? true : $attr['value'];
            $attributes[$key] = $value;
        }
        $blockArray['content']['metadata']['attributes'] = $attributes;
    }

    return $blockArray;
}

function getImageArray($file, $ratio = null, $ratioMobile = null)
{
    $image = [
        'url' => $file->url(),
        'width' => $file->width(),
        'height' => $file->height(),
        'alt' => (string)$file->alt(),
        'name' => (string)$file->name(),
        'identifier' => $file->identifier()->value(),
        'classes' => $file->classes()->value(),
        'captiontoggle' => $file->captiontoggle()->toBool(false),
        'captiontitle' => $file->captionobject()->toObject()->captiontitle()->value(),
        'captiontextfont' => $file->captionobject()->toObject()->textfont()->value(),
        'captiontextsize' => $file->captionobject()->toObject()->textsize()->value(),
        'captiontextcolor' => $file->captionobject()->toObject()->textColor()->value(),
        'captiontextalign' => $file->captionobject()->toObject()->textalign()->value(),
        'captionoverlay' => $file->captionobject()->toObject()->captionControls()->options()->value(),
        'captionalign' => $file->captionobject()->toObject()->captionalign()->value(),
        'linktoggle' => $file->linktoggle()->toBool(false),
        'linkexternal' => getLinkArray($file->linkexternal()),
    ];

    // Add focus-related properties if ratio is provided and file is not SVG
    if ($ratio && $ratioMobile && strtolower($file->extension()) !== 'svg') {
        $calculateHeight = function ($width, $ratio) {
            return isset($ratio[1]) ? round(($width / $ratio[0]) * $ratio[1]) : $width;
        };

        $image = array_merge($image, [
            'thumbhash' => $file->thumbhashUri(),
            'urlFocus' => $file->crop($file->width(), $calculateHeight($file->width(), $ratio))->url(),
            'urlFocusMobile' => $file->crop($file->width(), $calculateHeight($file->width(), $ratioMobile))->url(),
            'focusX' => json_decode($file->focusPercentageX()),
            'focusY' => json_decode($file->focusPercentageY()),
        ]);
    }

    return $image;
}
