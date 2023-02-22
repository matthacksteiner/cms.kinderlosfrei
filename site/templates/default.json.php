<?php

/** @var Page $page */
/** @var Array $json */

use Kirby\Cms\Page;

if ($page->blocks()->isNotEmpty()) {
  $json['blocks'] = [];

  foreach ($page->blocks()->toBlocks() as $block) {
    if (!method_exists($block, 'getBlockArray')) {
      continue;
    }

    $blockArray = [
      'id' => $block->id(),
      'type' => $block->type(),
      'content' => [],
    ];

    $blockArray['content'] = $block->getBlockArray();

    $json['blocks'][] = $blockArray;
  }
}

if ($page->blocksFooter()->isNotEmpty()) {
  $json['blocksFooter'] = [];

  foreach ($page->blocksFooter()->toBlocks() as $block) {
    if (!method_exists($block, 'getBlockArray')) {
      continue;
    }

    $blockArray = [
      'id' => $block->id(),
      'type' => $block->type(),
      'content' => [],
    ];

    $blockArray['content'] = $block->getBlockArray();

    $json['blocksFooter'][] = $blockArray;
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
