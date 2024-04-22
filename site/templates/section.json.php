<?php

/** @var Page $page */
/** @var Array $json */

use Kirby\Cms\Page;

function getItems(\Kirby\Cms\Page $page)
{
  $items = [];

  foreach ($page->children() as $item) {
    $image = $item->thumbnail()->toFile();

    $ratioMobile = explode('/', $page->displayratio()->toObject()->ratioMobile()->value() ?: '16/9');
    $ratio = explode('/', $page->displayratio()->toObject()->ratio()->value() ?: '16/9');

    $calculateHeight = function ($width, $ratio) {
      return isset($ratio[1]) ? round(($width / $ratio[0]) * $ratio[1]) : $width;
    };
    $items[] = [
      'title' => (string) $item->title(),
      "uri" => $item->uri(),
      "description" => (string) $item->description()->escape(),
      "parent" => strtolower((string) $page->title()),
      'thumbnail' => [
        'url' => $image->url(),
        'urlFocus' => $image->crop($image->width(), $calculateHeight($image->width(), $ratio))->url(),
        'urlFocusMobile' => $image->crop($image->width(), $calculateHeight($image->width(), $ratioMobile))->url(),
        'width' => $image->width(),
        'height' => $image->height(),
        'alt' => (string) $image->alt(),
        'orientation' => $image->orientation(),
      ],
    ];
  }

  return $items;
}

if ($page->children()->isNotEmpty()) {
  $json['items'] = getItems($page);
}

function getSettings(\Kirby\Cms\Page $page)
{
  return [
    'ratio' => $page->displayRatio()->toObject()->ratio()->value() ?: '16/9',
    'ratioMobile' => $page->displayRatio()->toObject()->ratioMobile()->value() ?: '16/9',
    'grid' => [
      'elements' => $page->displayElements()->toObject()->elements()->value() ?: '10',
      'gap' => $page->displayGrid()->toObject()->gap()->value() ?: '16',
      'gapMobile' => $page->displayGrid()->toObject()->gapMobile()->value() ?: '16',
      'span' => $page->displayGrid()->toObject()->span()->value() ?: '6',
      'spanMobile' => $page->displayGrid()->toObject()->spanMobile()->value() ?: '6',
    ],
    'main' => [
      'level' => $page->fontMain()->toObject()->mainlevel()->value() ?: 'h2',
      'font' => $page->fontMain()->toObject()->mainfont()->value(),
      'size' => $page->fontMain()->toObject()->mainsize()->value(),
      'color' => $page->fontMain()->toObject()->maincolor()->value(),
      'align' => $page->fontMain()->toObject()->mainalign()->value(),
      'spacing' => $page->fontMain()->toObject()->mainSpacing()->value() ?: '0',
      'spacingMobile' => $page->fontMain()->toObject()->mainSpacingMobile()->value() ?: '0',
    ],
    'title' => [
      'level' => $page->fontTitle()->toObject()->level()->value() ?: 'h2',
      'font' => $page->fontTitle()->toObject()->titleFont()->value(),
      'size' => $page->fontTitle()->toObject()->titleSize()->value(),
      'color' => $page->fontTitle()->toObject()->titleColor()->value(),
      'align' => $page->fontTitle()->toObject()->titleAlign()->value(),
    ],
    'text' => [
      'font' => $page->fontText()->toObject()->textFont()->value(),
      'size' => $page->fontText()->toObject()->textSize()->value(),
      'color' => $page->fontText()->toObject()->textColor()->value(),
      'align' => $page->fontText()->toObject()->textAlign()->value(),
    ]
  ];
}

$json['settings'] = getSettings($page);

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
