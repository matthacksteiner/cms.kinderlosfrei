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
  }

  return [
    "id" => $layout->id(),
    "anchor" => $layout->anchor()->value(),
    "backgroundContainer" => $layout->backgroundContainer()->value(),
    "backgroundColor" => $layout->backgroundColor()->value(),
    "backgroundContainerColor" => $layout->backgroundContainerColor()->value(),
    "backgroundAlign" => $layout->backgroundAlign()->value(),
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



function getBlockArray(\Kirby\Cms\Block $block)
{
  $blockArray = [
    "id" => $block->id(),
    "type" => $block->type(),
    "content" => [],
  ];

  switch ($block->type()) {
    case "anchor":
      $blockArray['content'] = $block->toArray()['content'];
      $slug = (string)$block->title()->slug();
      $blockArray['content']['slug'] = $slug;
      break;

    case 'image':
      $blockArray['content'] = $block->toArray()['content'];

      $image = null;
      if ($file1 = $block->image()->toFile()) {
        $image = $file1->focusCrop(1840);

        $image = [
          'url' => $image->url(),
          'width' => $image->width(),
          'height' => $image->height(),
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

    case 'button':
      $blockArray['content'] = $block->toArray()['content'];

      $link = [];
      if ($block->link()->isNotEmpty()) {
        $link = getLinkArray($block->link());
        $blockArray['content']['link'] = $link;
      }

      break;

    case 'slider':
      $blockArray['content'] = $block->toArray()['content'];
      $images = [];

      foreach ($block->images()->toFiles() as $file) {
        $image = $file->focusCrop(1840);
        $images[] = [
          'url' => $image->url(),
          'width' => $image->width(),
          'height' => $image->height(),
          'alt' => (string)$image->alt(),
        ];
      }
      $blockArray['content']['images'] = $images;
      $blockArray['content']['toggle'] = $block->toggle()->toBool(false);
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
          ];
        }

        $blockArray['content']['list'][$key]["icon"] = $icon;
      }

      break;


    case "video":
      $blockArray['content'] = $block->toArray()['content'];
      $video = null;
      if ($file1 = $block->file()->toFile()) {
        $video = [
          'url' => $file1->url(),
          'alt' => (string)$file1->alt(),
        ];
      }
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
