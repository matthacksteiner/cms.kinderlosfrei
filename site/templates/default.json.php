<?php

/** @var Page $page */
/** @var Array $json */

use Kirby\Cms\Page;

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

    case 'blockImage':
      $blockArray['content'] = $block->toArray()['content'];
      $images = [];

      foreach ($block->images()->toFiles() as $file) {
        $image = $file->focusCrop(1920, 1920);
        $images[] = [
          'url' => $image->url(),
          'width' => $image->width(),
          'height' => $image->height(),
          'alt' => (string)$image->alt(),
        ];
      }
      $blockArray['content']['images'] = $images;
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

  $json['content'] = $page->getJsonData($content);
}

echo json_encode($json);
