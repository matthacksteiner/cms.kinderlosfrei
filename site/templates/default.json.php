<?php

/** @var Page $page */
/** @var Array $json */

use Kirby\Cms\Page;

function getlayoutArray(\Kirby\Cms\Layout $layout)
{
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
    $backgroundArrow = $layout->backgroundArrow()->toBool(false);
  }

  return [
    "id" => $layout->id(),
    "anchor" => $layout->anchor()->value(),
    "classes" => $layout->classes()->value(),
    "backgroundContainer" => $layout->backgroundContainer()->value(),
    "backgroundHeight" => $layout->backgroundHeight()->value(),
    "backgroundColor" => $layout->backgroundColor()->value(),
    "backgroundContainerColor" => $layout->backgroundContainerColor()->value(),
    "backgroundPadding" => $layout->backgroundPadding()->value(),
    "backgroundAlignVertical" => $layout->backgroundAlignVertical()->value(),
    "backgroundAlignItemsVertical" => $layout->backgroundAlignItemsVertical()->value(),
    "backgroundAlignHorizontal" => $layout->backgroundAlignHorizontal()->value(),
    "backgroundArrow" => $backgroundArrow,
    "backgroundArrowColor" => $layout->backgroundArrowColor()->value(),
    "backgroundArrowSize" => $layout->backgroundArrowSize()->value(),
    "spacingMobileTop" => $layout->spacingMobileTop()->value(),
    "spacingMobileBottom" => $layout->spacingMobileBottom()->value(),
    "spacingDesktopTop" => $layout->spacingDesktopTop()->value(),
    "spacingDesktopBottom" => $layout->spacingDesktopBottom()->value(),

    "content" => [
      "columns" => $columns,
    ],
  ];
}

if ($page->layout()->isNotEmpty()) {
  $json["layouts"] = [];

  foreach ($page->layout()->toLayouts() as $layout) {
    $layoutData = getlayoutArray($layout);

    if (!$layoutData) {
      continue;
    }

    $json["layouts"][] = $layoutData;
  }
}

if ($site->layoutFooter()->isNotEmpty()) {
  $json["layoutFooter"] = [];

  foreach ($site->layoutFooter()->toLayouts() as $layout) {
    $layoutData = getlayoutArray($layout);

    if (!$layoutData) {
      continue;
    }

    $json["layoutFooter"][] = $layoutData;
  }
}


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
        return [
          "id" => $layout->id(),
          "type" => 'columns',
          "content" => [
            "columns" => $columns,
          ],

        ];
      }
      break;

    case 'grid':
      $allGrids = [];

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

      $output = [
        "id" => $block->id(),
        "type" => 'grid',
        "content" => [
          "grid" => $allGrids,
        ],
      ];

      return $output;

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

      $linkexternal = [];
      if ($block->linkexternal()->isNotEmpty()) {
        $linkexternal = getLinkArray($block->linkexternal());
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

      $linkexternal = [];
      if ($block->linkexternal()->isNotEmpty()) {
        $linkexternal = getLinkArray($block->linkexternal());
      }
      $blockArray['content']['images'] = $images;
      $blockArray['content']['toggle'] = $block->toggle()->toBool(false);

      break;

    case "menu":
      $blockArray['content'] = $block->toArray()['content'];
      foreach ($block->nav()->toStructure() as $key => $item) {
        $link = [];
        if ($item->link()->isNotEmpty()) {
          $link = getLinkArray($item->link());
        }
        $blockArray['content']['nav'][$key]["link"] = $link;
      }

      break;

    case 'button':
      $blockArray['content'] = $block->toArray()['content'];

      $link = [];
      if ($block->link()->isNotEmpty()) {
        $link = getLinkArray($block->link());
        $blockArray['content']['link'] = $link;
      }

      break;



    case 'text':
      $blockArray['content'] = $block->toArray()['content'];
      $blockArray['content']['text'] = (string)$block->text();
      break;

    case "vector":
      $blockArray['content'] = $block->toArray()['content'];
      $image = null;
      if ($file1 = $block->image()->toFile()) {
        $image = [
          'url' => $file1->url(),
          'alt' => (string)$file1->alt(),
          'identifier' => $file1->identifier()->value(),
          'classes' => $file1->classes()->value(),
          'width' => $file1->width(),
          'height' => $file1->height(),
        ];
      }

      $linkexternal = [];
      if ($block->linkexternal()->isNotEmpty()) {
        $linkexternal = getLinkArray($block->linkexternal());
      }

      $blockArray['content']['image'] = $image;
      $blockArray['content']['linkexternal'] = $linkexternal;
      $blockArray['content']['toggle'] = $block->toggle()->toBool(false);
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


    case "video":
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

  return $blockArray;
}

if ($page->baukastenbuilder()->isNotEmpty()) {
  $json["blocks"] = [];

  foreach ($page->baukastenbuilder()->toBlocks() as $block) {
    $blockData = getBlockArray($block);

    if (!$blockData) {
      continue;
    }

    $json["blocks"][] = $blockData;
  }
}

if (method_exists($page, 'getJsonData')) {
  $content = $page->content()->toArray();
  $unsetFields = [
    'title',
    'meta_title',
    'meta_description',
    'meta_canonical_url',
    'meta_author',
    'meta_image',
    'meta_phone_number',
    'og_title',
    'og_description',
    'og_image',
    'og_site_name',
    'og_url',
    'og_audio',
    'og_video',
    'og_determiner',
    'og_type',
    'og_type_article_published_time',
    'og_type_article_modified_time',
    'og_type_article_expiration_time',
    'og_type_article_author',
    'og_type_article_section',
    'og_type_article_tag',
    'twitter_title',
    'twitter_description',
    'twitter_image',
    'twitter_card_type',
    'twitter_site',
    'twitter_creator',
    'robots_noindex',
    'robots_nofollow',
    'robots_noarchive',
    'robots_noimageindex',
    'robots_nosnippet',
    'blocks'
  ];

  foreach ($unsetFields as $key) {
    unset($content[$key]);
  }
}

echo json_encode($json);
