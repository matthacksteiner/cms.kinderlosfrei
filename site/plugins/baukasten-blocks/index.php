<?php

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
                $image = $file1;

                $ratioMobile = explode('/', $block->ratioMobile()->value());
                $ratio = explode('/', $block->ratio()->value());

                $calculateHeight = function ($width, $ratio) {
                    return isset($ratio[1]) ? round(($width / $ratio[0]) * $ratio[1]) : $width;
                };

                $image = [
                    'url' => $image->url(),
                    'urlFocus' => $image->crop($image->width(), $calculateHeight($image->width(), $ratio))->url(),
                    'urlFocusMobile' => $image->crop($image->width(), $calculateHeight($image->width(), $ratioMobile))->url(),
                    'width' => $image->width(),
                    'height' => $image->height(),
                    'alt' => (string)$image->alt(),
                    'identifier' => $image->identifier()->value(),
                    'classes' => $image->classes()->value(),
                    'focusX' => json_decode($file1->focusPercentageX()),
                    'focusY' => json_decode($file1->focusPercentageY()),
                    'captiontoggle' => $file1->captiontoggle()->toBool(false),
                    'captiontitle' => $file1->captionobject()->toObject()->captiontitle()->value(),
                    'captiontextfont' => $file1->captionobject()->toObject()->textfont()->value(),
                    'captiontextsize' => $file1->captionobject()->toObject()->textsize()->value(),
                    'captiontextcolor' => $file1->captionobject()->toObject()->textColor()->value(),
                    'captiontextalign' => $file1->captionobject()->toObject()->textalign()->value(),
                    'captionoverlay' => $file1->captionobject()->toObject()->captionControls()->options()->value(),
                    'captionalign' => $file1->captionobject()->toObject()->captionalign()->value(),
                    'linktoggle' => $file1->linktoggle()->toBool(false),
                    'linkexternal' => getLinkArray($file1->linkexternal()),
                ];
            }

            $blockArray['content']['image'] = $image;
            break;

            // Additional cases follow similarly...

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
