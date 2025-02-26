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
                    'name' => (string)$image->name(),
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
                    'copyrighttoggle' => $file1->copyrighttoggle()->toBool(false),
                    'copyrighttitle' => $file1->copyrightobject()->toObject()->copyrighttitle()->value(),
                    'copyrighttextfont' => $file1->copyrightobject()->toObject()->textfont()->value(),
                    'copyrighttextsize' => $file1->copyrightobject()->toObject()->textsize()->value(),
                    'copyrighttextcolor' => $file1->copyrightobject()->toObject()->textColor()->value(),
                    'copyrighbackgroundcolor' => $file1->copyrightobject()->toObject()->copyrightBackground()->value(),
                    'copyrightposition' => $file1->copyrightobject()->toObject()->copyrightposition()->value(),

                ];
            }

            $blockArray['content']['image'] = $image;
            break;

        case "vector":
            $blockArray['content'] = $block->toArray()['content'];
            $image = null;
            if ($file1 = $block->image()->toFile()) {
                $image = [
                    'url' => $file1->url(),
                    'alt' => (string)$file1->alt(),
                    'name' => (string)$file1->name(),
                    'identifier' => $file1->identifier()->value(),
                    'classes' => $file1->classes()->value(),
                    'width' => $file1->width(),
                    'height' => $file1->height(),
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

        case 'slider':
            $blockArray['content'] = $block->toArray()['content'];
            $images = [];

            foreach ($block->images()->toFiles() as $file) {
                $image = $file;

                $ratioMobile = explode('/', $block->ratioMobile()->value());
                $ratio = explode('/', $block->ratio()->value());

                $calculateHeight = function ($width, $ratio) {
                    return isset($ratio[1]) ? round(($width / $ratio[0]) * $ratio[1]) : $width;
                };

                $images[] = [
                    'url' => $image->url(),
                    'urlFocus' => $image->crop($image->width(), $calculateHeight($image->width(), $ratio))->url(),
                    'urlFocusMobile' => $image->crop($image->width(), $calculateHeight($image->width(), $ratioMobile))->url(),
                    'width' => $image->width(),
                    'height' => $image->height(),
                    'alt' => (string)$image->alt(),
                    'name' => (string)$image->name(),
                    'identifier' => $image->identifier()->value(),
                    'classes' => $image->classes()->value(),
                    'focusX' => json_decode($file->focusPercentageX()),
                    'focusY' => json_decode($file->focusPercentageY()),
                    'toggle' => $file->toggle()->toBool(false),
                    'captiontoggle' => $file->captiontoggle()->toBool(false),
                    'captiontitle' => $file->captionobject()->toObject()->captiontitle()->value(),
                    'captiontextfont' => $file->captionobject()->toObject()->textfont()->value(),
                    'captiontextsize' => $file->captionobject()->toObject()->textsize()->value(),
                    'captiontextcolor' => $file->captionobject()->toObject()->textColor()->value(),
                    'captiontextalign' => $file->captionobject()->toObject()->textalign()->value(),
                    'captionoverlay' => $file->captionobject()->toObject()->captionoverlay()->value(),
                    'captionalign' => $file->captionobject()->toObject()->captionalign()->value(),
                    'linktoggle' => $file->linktoggle()->toBool(false),
                    'linkexternal' => getLinkArray($file->linkexternal()),
                ];
            }

            $blockArray['content']['images'] = $images;
            $blockArray['content']['toggle'] = $block->toggle()->toBool(false);

            break;

        case 'gallery':
            $blockArray['content'] = $block->toArray()['content'];
            $images = [];

            foreach ($block->images()->toFiles() as $file) {
                $image = $file;

                $ratioMobile = explode('/', $block->ratioMobile()->value());
                $ratio = explode('/', $block->ratio()->value());

                $calculateHeight = function ($width, $ratio) {
                    return isset($ratio[1]) ? round(($width / $ratio[0]) * $ratio[1]) : $width;
                };

                $images[] = [
                    'url' => $image->url(),
                    'urlFocus' => $image->crop($image->width(), $calculateHeight($image->width(), $ratio))->url(),
                    'urlFocusMobile' => $image->crop($image->width(), $calculateHeight($image->width(), $ratioMobile))->url(),
                    'width' => $image->width(),
                    'height' => $image->height(),
                    'alt' => (string)$image->alt(),
                    'name' => (string)$image->name(),
                    'identifier' => $image->identifier()->value(),
                    'classes' => $image->classes()->value(),
                    'focusX' => json_decode($file->focusPercentageX()),
                    'focusY' => json_decode($file->focusPercentageY()),
                    'captiontoggle' => $file->captiontoggle()->toBool(false),
                    'captiontitle' => $file->captionobject()->toObject()->captiontitle()->value(),
                    'captiontextfont' => $file->captionobject()->toObject()->textfont()->value(),
                    'captiontextsize' => $file->captionobject()->toObject()->textsize()->value(),
                    'captiontextcolor' => $file->captionobject()->toObject()->textColor()->value(),
                    'captiontextalign' => $file->captionobject()->toObject()->textalign()->value(),
                    'captionoverlay' => $file->captionobject()->toObject()->captionoverlay()->value(),
                    'captionalign' => $file->captionobject()->toObject()->captionalign()->value(),
                    'linktoggle' => $file->linktoggle()->toBool(false),
                    'linkexternal' => getLinkArray($file->linkexternal()),
                ];
            }

            $blockArray['content']['images'] = $images;
            $blockArray['content']['layoutType'] = $block->layoutType()->value();
            $blockArray['content']['lightbox'] = $block->lightbox()->toBool(false);
            $blockArray['content']['viewMobile'] = $block->viewMobile()->value();
            $blockArray['content']['viewDesktop'] = $block->viewDesktop()->value();
            $blockArray['content']['viewPaddingMobile'] = $block->viewPaddingMobile()->value();
            $blockArray['content']['viewPaddingDesktop'] = $block->viewPaddingDesktop()->value();

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
