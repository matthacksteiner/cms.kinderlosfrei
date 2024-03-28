<?php

/** @var Page $page */
/** @var Array $json */

use Kirby\Cms\Page;

function getChildren(\Kirby\Cms\Page $page)
{
  $children = [];
  foreach ($page->children() as $child) {
    $children[] = [
      "id" => $child->id(),
      "uri" => $child->uri(),
      "intendedTemplate" => $child->intendedTemplate()->name(),
    ];
  }

  return [
    "id" => $page->id(),
    "uri" => $page->uri(),
    "intendedTemplate" => $page->intendedTemplate()->name(),
  ];
}

if ($page->children()->isNotEmpty()) {
  $json['children'] = [];

  foreach ($page->children() as $child) {
    $childrenData = getChildren($child);
  }

  $json['children'] = $childrenData;
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
