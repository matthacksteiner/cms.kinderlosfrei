<?php

use Kirby\Cms\App as Kirby;

Kirby::plugin('baukasten-blocks/baukasten-blocks', [
    'options'       => [],
    'components'    => [],
    'fields'        => [],
    'snippets'      => [],
    'templates'     => [],
    'blueprints'    => [],
    'translations'  => [],
]);

/**
 * Process a collection of blocks and return an array
 */
function processBlocks($blocks)
{
    $result = [];
    foreach ($blocks as $block) {
        $blockData = getBlockArray($block);
        if ($blockData) {
            $result[] = $blockData;
        }
    }
    return $result;
}

/**
 * Process columns from a given collection and return their arrays.
 */
function processColumns($columnsCollection)
{
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

/**
 * Process metadata attributes and convert 'true' strings to booleans.
 */
function processMetadataAttributes(array $metadataAttributes)
{
    $attributes = [];
    foreach ($metadataAttributes as $attr) {
        $key = $attr['attribute'];
        $value = $attr['value'] === 'true' ? true : $attr['value'];
        $attributes[$key] = $value;
    }
    return $attributes;
}

/**
 * Helper function to get ratio arrays from block fields
 */
function getRatioArrays($block, $ratioField = 'ratio', $ratioMobileField = 'ratioMobile')
{
    return [
        'ratio' => explode('/', $block->{$ratioField}()->value()),
        'ratioMobile' => explode('/', $block->{$ratioMobileField}()->value())
    ];
}

/**
 * Helper function to process multiple images with ratios
 */
function processImagesWithRatio($files, $ratio, $ratioMobile, $additionalFields = [])
{
    $images = [];
    foreach ($files as $file) {
        if (strtolower($file->extension()) === 'svg') {
            $image = getSvgArray($file);
        } else {
            $image = getImageArray($file, $ratio, $ratioMobile);
        }

        // Add any additional fields for this specific file
        foreach ($additionalFields as $field => $defaultValue) {
            if (method_exists($file, $field)) {
                $image[$field] = $file->{$field}()->toBool($defaultValue);
            }
        }

        $images[] = $image;
    }
    return $images;
}

/**
 * Helper function to add copyright properties to an image array
 */
function addCopyrightProperties($image, $file)
{
    return array_merge($image, [
        'copyrighttoggle'       => $file->copyrighttoggle()->toBool(false),
        'copyrighttitle'        => $file->copyrightobject()->toObject()->copyrighttitle()->value(),
        'copyrighttextfont'     => $file->copyrightobject()->toObject()->textfont()->value(),
        'copyrighttextsize'     => $file->copyrightobject()->toObject()->textsize()->value(),
        'copyrighttextcolor'    => $file->copyrightobject()->toObject()->textColor()->value(),
        'copyrighbackgroundcolor' => $file->copyrightobject()->toObject()->copyrightBackground()->value(),
        'copyrightposition'     => $file->copyrightobject()->toObject()->copyrightposition()->value(),
    ]);
}

/**
 * Helper function to process structure items with link objects
 */
function processStructureWithLinks($structure, $linkField = 'linkobject')
{
    $result = [];
    foreach ($structure as $key => $item) {
        $result[$key] = $item->toArray();
        $result[$key][$linkField] = getLinkArray($item->{$linkField}());
    }
    return $result;
}

function getBlockArray(\Kirby\Cms\Block $block)
{
    $blockArray = [
        "id"      => $block->id(),
        "type"    => $block->type(),
        "content" => [],
    ];

    // Cases that don't use base content first
    $noBaseContentCases = ['columns', 'grid'];

    // Initialize base content for most cases
    if (!in_array($block->type(), $noBaseContentCases)) {
        $blockArray['content'] = $block->toArray()['content'];
    }

    switch ($block->type()) {
        case 'columns':
            $layout = $block->layout()->toLayouts()->first();
            if ($layout !== null) {
                $blockArray['content'] = [
                    "columns" => processColumns($layout->columns())
                ];
            }
            break;

        case 'grid':
            $allGrids = [];
            foreach ($block->grid()->toLayouts() as $layout) {
                $allGrids[] = [
                    "id"      => $layout->id(),
                    "columns" => processColumns($layout->columns()),
                ];
            }
            $blockArray['content'] = [
                "title" => $block->title()->value(),
                "grid"  => $allGrids,
            ];
            break;

        case 'image':
            $image = null;
            if ($file1 = $block->image()->toFile()) {
                $ratios = getRatioArrays($block);
                $image = getImageArray($file1, $ratios['ratio'], $ratios['ratioMobile']);
                $image = addCopyrightProperties($image, $file1);
            }
            $blockArray['content']['abovefold'] = $block->abovefold()->toBool(false);
            $blockArray['content']['image'] = $image;
            break;

        case "vector":
            $image = null;
            if ($file1 = $block->image()->toFile()) {
                $image = getSvgArray($file1);
            }
            $blockArray['content']['image'] = $image;
            break;

        case 'slider':
            $ratios = getRatioArrays($block);
            $images = processImagesWithRatio(
                $block->images()->toFiles(),
                $ratios['ratio'],
                $ratios['ratioMobile'],
                ['toggle' => false]
            );

            $blockArray['content']['images'] = $images;
            $blockArray['content']['toggle'] = $block->toggle()->toBool(false);
            $blockArray['content']['abovefold'] = $block->abovefold()->toBool(false);
            break;

        case 'gallery':
            $ratios = getRatioArrays($block);
            $images = processImagesWithRatio(
                $block->images()->toFiles(),
                $ratios['ratio'],
                $ratios['ratioMobile']
            );

            $blockArray['content']['images'] = $images;
            $blockArray['content']['layoutType'] = $block->layoutType()->value();
            $blockArray['content']['lightbox'] = $block->lightbox()->toBool(false);
            $blockArray['content']['viewMobile'] = $block->viewMobile()->value();
            $blockArray['content']['viewDesktop'] = $block->viewDesktop()->value();
            $blockArray['content']['viewPaddingMobile'] = $block->viewPaddingMobile()->value();
            $blockArray['content']['viewPaddingDesktop'] = $block->viewPaddingDesktop()->value();
            $blockArray['content']['abovefold'] = $block->abovefold()->toBool(false);
            break;

        case "menu":
            foreach ($block->nav()->toStructure() as $key => $item) {
                $blockArray['content']['nav'][$key] = $item->toArray();
                $blockArray['content']['nav'][$key]["linkobject"] = getLinkArray($item->linkobject());
            }
            break;

        case 'button':
            $blockArray['content']['linkobject'] = getLinkArray($block->linkobject());
            $blockArray['content']['buttonlocal'] = $block->buttonlocal()->toBool(false);
            break;

        case 'buttonBar':
            foreach ($block->buttons()->toStructure() as $key => $button) {
                $blockArray['content']['buttons'][$key] = $button->toArray();
                $blockArray['content']['buttons'][$key]['linkobject'] = getLinkArray($button->linkObject());
            }
            $blockArray['content']['buttonlocal'] = $block->buttonlocal()->toBool(false);
            break;

        case 'text':
            $blockArray['content']['text'] = (string)$block->text();
            break;

        case "iconlist":
            foreach ($block->list()->toStructure() as $key => $item) {
                $icon = null;
                if ($file = $item->icon()->toFile()) {
                    $icon = [
                        'url'    => $file->url(),
                        'alt'    => (string)$file->alt(),
                        'source' => file_get_contents($file->root()),
                    ];
                }
                $blockArray['content']['list'][$key] = $item->toArray();
                $blockArray['content']['list'][$key]["icon"] = $icon;
            }
            break;

        case 'code':
            $blockArray['content']['code'] = (string)$block->code();
            break;

        case 'video':
            $video = null;
            $thumb = null;
            if ($file1 = $block->file()->toFile()) {
                $video = [
                    'url'        => $file1->url(),
                    'alt'        => (string)$file1->alt(),
                    'identifier' => $file1->identifier()->value(),
                    'classes'    => $file1->classes()->value(),
                ];
            }
            if ($file2 = $block->thumbnail()->toFile()) {
                $thumb = [
                    'url' => $file2->url(),
                    'alt' => (string)$file2->alt(),
                ];
            }
            $blockArray['content']['abovefold'] = $block->abovefold()->toBool(false);
            $blockArray['content']['thumbnail'] = $thumb;
            $blockArray['content']['file'] = $video;
            break;

        case 'card':
            $blockArray['content']['hovertoggle'] = $block->hovertoggle()->toBool(false);
            $blockArray['content']['linktoggle'] = $block->linktoggle()->toBool(false);
            $blockArray['content']['linkobject'] = getLinkArray($block->linkobject());

            $image = null;
            if ($file1 = $block->image()->toFile()) {
                $image = getSvgArray($file1);
            }
            $blockArray['content']['image'] = $image;
            break;

        case 'navigation':
            $blockArray['content']['previousToggle'] = $block->previousToggle()->toBool(true);
            $blockArray['content']['nextToggle'] = $block->nextToggle()->toBool(true);
            $blockArray['content']['previousLabel'] = $block->previousLabel()->value();
            $blockArray['content']['nextLabel'] = $block->nextLabel()->value();
            $blockArray['content']['buttonlocal'] = $block->buttonlocal()->toBool(false);
            break;

        case 'featured':
            $items = [];

            // Get ratio values for proper image processing
            $ratioMobile = explode('/', $block->displayratio()->toObject()->ratiomobile()->value() ?: '1/1');
            $ratio = explode('/', $block->displayratio()->toObject()->ratio()->value() ?: '1/1');

            // Process selected elements
            if ($selectedElements = $block->elements()->split(',')) {
                foreach ($selectedElements as $elementId) {
                    if ($page = page($elementId)) {
                        $thumbnail = null;

                        // Get thumbnail image if available using the proper getImageArray function
                        if ($thumbFile = $page->thumbnail()->toFile()) {
                            $thumbnail = getImageArray($thumbFile, $ratio, $ratioMobile);
                        }

                        $items[] = [
                            'id'          => $page->id(),
                            'title'       => (string)$page->title(),
                            'description' => (string)$page->description(),
                            'uri'         => generatePageUri($page),
                            'url'         => $page->url(),
                            'status'      => $page->status(),
                            'position'    => $page->num(),
                            'thumbnail'   => $thumbnail,
                            'coverOnly'   => $page->coverOnly()->toBool(false),
                        ];
                    }
                }
            }

            // Get site object for design fallbacks
            $site = site();

            // Helper function to get value with fallback to design settings
            $getValueWithFallback = function ($value, $fallbackSiteField) use ($site) {
                if ($value === 'default') {
                    return $site->{$fallbackSiteField}()->value() ?: '16';
                }
                return ($value !== null && $value !== '') ? $value : '16';
            };

            // Get the actual values from block fields
            $gap = $block->displayGrid()->toObject()->gap()->value();
            $gapMobile = $block->displayGrid()->toObject()->gapMobile()->value();
            $gapHorizontal = $block->displayGrid()->toObject()->gapHorizontal()->value();
            $gapHorizontalMobile = $block->displayGrid()->toObject()->gapHorizontalMobile()->value();

            $blockArray['content']['items'] = $items;
            $blockArray['content']['fontTitleToggle'] = $block->fontTitleToggle()->toBool(true);
            $blockArray['content']['fontTextToggle'] = $block->fontTextToggle()->toBool(true);
            $blockArray['content']['captionAlign'] = $block->captionAlign()->value() ?: 'bottom';
            $blockArray['content']['captionControls'] = $block->captionControls()->split(',');
            $blockArray['content']['captionOverlayRange'] = $block->captionOverlayRange()->toInt() ?: 50;
            $blockArray['content']['captionColor'] = $block->captionColor()->value() ?: '#000000';
            $blockArray['content']['grid'] = [
                'gap' => $getValueWithFallback($gap, 'gridBlockDesktop'),
                'gapMobile' => $getValueWithFallback($gapMobile, 'gridBlockMobile'),
                'gapHorizontal' => $getValueWithFallback($gapHorizontal, 'gridGapDesktop'),
                'gapHorizontalMobile' => $getValueWithFallback($gapHorizontalMobile, 'gridGapMobile'),
            ];
            break;

        default:
            // Base content already assigned for default case
            break;
    }

    // Process metadata attributes if available
    if (isset($blockArray['content']['metadata']['attributes'])) {
        $blockArray['content']['metadata']['attributes'] = processMetadataAttributes($blockArray['content']['metadata']['attributes']);
    }

    return $blockArray;
}

function getImageArray($file, $ratio = null, $ratioMobile = null)
{
    $image = [
        'url'               => $file->url(),
        'width'             => $file->width(),
        'height'            => $file->height(),
        'alt'               => (string)$file->alt(),
        'name'              => (string)$file->name(),
        'identifier'        => $file->identifier()->value(),
        'classes'           => $file->classes()->value(),
        'captiontoggle'     => $file->captiontoggle()->toBool(false),
        'captiontitle'      => $file->captionobject()->toObject()->captiontitle()->value(),
        'captiontextfont'   => $file->captionobject()->toObject()->textfont()->value(),
        'captiontextsize'   => $file->captionobject()->toObject()->textsize()->value(),
        'captiontextcolor'  => $file->captionobject()->toObject()->textColor()->value(),
        'captiontextalign'  => $file->captionobject()->toObject()->textalign()->value(),
        'captionoverlay'    => $file->captionobject()->toObject()->captionControls()->options()->value(),
        'captionalign'      => $file->captionobject()->toObject()->captionalign()->value(),
        'captionOverlayRange' => $file->captionobject()->toObject()->captionOverlayRange()->toInt() ?: 50,
        'captionColor'      => $file->captionobject()->toObject()->captionColor()->value() ?: '#000000',
        'linktoggle'        => $file->linktoggle()->toBool(false),
        'linkexternal'      => getLinkArray($file->linkexternal()),
    ];

    // Add focus-related properties if ratio is provided and file is not SVG
    if ($ratio && $ratioMobile && strtolower($file->extension()) !== 'svg') {
        $calculateHeight = function ($width, $ratio) {
            return isset($ratio[1]) ? round(($width / $ratio[0]) * $ratio[1]) : $width;
        };

        $image = array_merge($image, [
            'thumbhash'       => $file->thumbhashUri(),
            'urlFocus'        => $file->crop($file->width(), $calculateHeight($file->width(), $ratio))->url(),
            'urlFocusMobile'  => $file->crop($file->width(), $calculateHeight($file->width(), $ratioMobile))->url(),
            'focusX'          => json_decode($file->focusPercentageX()),
            'focusY'          => json_decode($file->focusPercentageY()),
        ]);
    }

    return $image;
}

function getSvgArray($file)
{
    return [
        'url'        => $file->url(),
        'width'      => $file->width(),
        'height'     => $file->height(),
        'alt'        => (string)$file->alt(),
        'name'       => (string)$file->name(),
        'identifier' => $file->identifier()->value(),
        'classes'    => $file->classes()->value(),
        'linktoggle' => $file->linktoggle()->toBool(false),
        'linkexternal' => getLinkArray($file->linkexternal()),
        'source'     => file_get_contents($file->root()),
    ];
}
