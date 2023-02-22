<?php

use Kirby\Toolkit\Config;

function getMeta($site, $page, $kirby)
{
	$meta = [];

	$twitter_image_thumb = [
		'width' => 1200,
		'height' => 675,
		'quality' => 80,
		'crop' => true
	];
	$og_image_thumb = [
		'width' => 1200,
		'height' => 630,
		'quality' => 80,
		'crop' => true
	];

	if (option('diesdasdigital.meta-knight.siteTitleAsHomePageTitle', false) && $page->isHomePage()) {
		$full_title = $site->meta_title()->or($site->title());
	} elseif (option('diesdasdigital.meta-knight.pageTitleAsHomePageTitle', false) && $page->isHomePage()) {
		$full_title = $page->meta_title()->or($page->title());
	} elseif (option('diesdasdigital.meta-knight.siteTitleAfterPageTitle', true)) {
		$full_title = $page->meta_title()->or($page->title()) . option('diesdasdigital.meta-knight.separator', ' - ') . $site->meta_title()->or($site->title());
	} else {
		$full_title = $site->meta_title()->or($site->title()) . option('diesdasdigital.meta-knight.separator', ' - ') . $page->meta_title()->or($page->title());
	}

	// Page Title
	$meta[] = [
		"tag" => "title",
		"html" => (string) $full_title,
	];

	$meta[] = [
		"tag" => "meta",
		"id" => "schema_name",
		"itemProp" => "name",
		"content" => (string) $full_title,
	];

	// Description
	$meta[] = [
		"tag" => "meta",
		"name" => "description",
		"content" => (string) $page->meta_description()->or($site->meta_description()),
	];

	// Image
	if ($meta_image = $page->meta_image()->toFile() ?? $site->meta_image()->toFile()) {
		$meta[] = [
			"tag" => "meta",
			"id" => "schema_image",
			"itemProp" => "image",
			"name" => "description",
			"content" => (string) $meta_image->url(),
		];
	}

	// Author
	$meta[] = [
		"tag" => "meta",
		"name" => "author",
		"content" => (string) $page->meta_author()->or($site->meta_author()),
	];

	// Date
	$meta[] = [
		"tag" => "meta",
		"name" => "date",
		"content" => $page->modified('Y-m-d'),
		"scheme" => "YYYY-MM-DD",
	];

	// Open Graph
	$meta[] = [
		"tag" => "meta",
		"property" => "og:title",
		"content" => (string) $page->og_title()->or($page->meta_title())->or($site->og_title())->or($site->meta_title())->or($page->title()),
	];

	$meta[] = [
		"tag" => "meta",
		"property" => "og:description",
		"content" => (string) $page->og_description()->or($page->meta_description())->or($site->meta_description()),
	];

	if ($og_image = $page->og_image()->toFile() ?? $site->og_image()->toFile()) {
		$meta[] = [
			"tag" => "meta",
			"property" => "og:image",
			"content" => (string) $og_image->thumb($og_image_thumb)->url(),
		];

		$meta[] = [
			"tag" => "meta",
			"property" => "og:width",
			"content" => (string) $og_image->thumb($og_image_thumb)->width(),
		];

		$meta[] = [
			"tag" => "meta",
			"property" => "og:height",
			"content" => (string) $og_image->thumb($og_image_thumb)->height(),
		];
	}

	$meta[] = [
		"tag" => "meta",
		"property" => "og:site_name",
		"content" => (string) $page->og_site_name()->or($site->og_site_name()),
	];

	$meta[] = [
		"tag" => "meta",
		"property" => "og:url",
		"content" => str_replace($kirby->environment()->host(), config::get('frontendUrl'), (string) $page->og_url()->or($page->url())),
	];

	$meta[] = [
		"tag" => "meta",
		"property" => "og:type",
		"content" => (string) $page->og_type()->or($site->og_type()),
	];

	if ($page->og_determiner()->or($site->og_determiner())->isNotEmpty()) {
		$meta[] = [
			"tag" => "meta",
			"property" => "og:determiner",
			"content" => (string) $page->og_determiner()->or($site->og_determiner())->or("auto"),
		];
	}

	if ($page->og_audio()->isNotEmpty()) {
		$meta[] = [
			"tag" => "meta",
			"property" => "og:audio",
			"content" => (string) $page->og_audio(),
		];
	}

	if ($page->og_video()->isNotEmpty()) {
		$meta[] = [
			"tag" => "meta",
			"property" => "og:video",
			"content" => (string) $page->og_video(),
		];
	}

	if ($kirby->language() !== null) {
		$meta[] = [
			"tag" => "meta",
			"property" => "og:locale",
			"content" => (string) $kirby->language()->locale(LC_ALL),
		];

		foreach ($kirby->languages() as $language) {
			if ($language !== $kirby->language()) {
				$meta[] = [
					"tag" => "meta",
					"property" => "og:locale:alternate",
					"content" => (string) $language->locale(LC_ALL),
				];
			}
		}
	}

	$og_authors = $page->og_type_article_author()->or($site->og_type_article_author());

	foreach ($og_authors->toStructure() as $og_author) {
		$meta[] = [
			"tag" => "meta",
			"property" => "article:author",
			"content" => (string) $og_author->url()->html(),
		];
	}

	// Twitter Card

	$meta[] = [
		"tag" => "meta",
		"name" => "twitter:card",
		"content" => (string) $page->twitter_card_type()->or($site->twitter_card_type())->value(),
	];

	$meta[] = [
		"tag" => "meta",
		"name" => "twitter:title",
		"content" => (string) $page->twitter_title()->or($page->meta_title())->or($site->twitter_title())->or($site->meta_title())->or($page->title()),
	];

	$meta[] = [
		"tag" => "meta",
		"name" => "twitter:description",
		"content" => (string) $page->twitter_description()->or($page->meta_description())->or($site->meta_description()),
	];

	if ($twitter_image = $page->twitter_image()->toFile() ?? $site->twitter_image()->toFile()) {
		$meta[] = [
			"tag" => "meta",
			"name" => "twitter:image",
			"content" => (string) $twitter_image->thumb($twitter_image_thumb)->url(),
		];
	}

	$meta[] = [
		"tag" => "meta",
		"name" => "twitter:site",
		"content" => (string) $page->twitter_site()->or($site->twitter_site()),
	];

	$meta[] = [
		"tag" => "meta",
		"name" => "twitter:creator",
		"content" => (string) $page->twitter_creator()->or($site->twitter_creator()),
	];

	return $meta;
}

return function ($site, $page, $kirby) {
	$nav = [];
	foreach ($site->pages()->listed() as $navPage) {
		$nav[] = [
			"uri" => $navPage->uri(),
			"title" => (string) $navPage->title(),
			"active" => $navPage->isOpen(),
			"intendedtemplate" => (string) $navPage->intendedTemplate(),
		];
	}

	$nav = count($nav) > 0 ? $nav : null;



	return [
		'json' => [
			'global' => [
				'siteTitle' => (string) $site->title(),
				"meta" => getMeta($site, $page, $kirby),
				"instagramUrl" => (string) $site->instagramUrl(),
				"facebookUrl" => (string) $site->facebookUrl(),
				"nav" => $nav,
				"legalName" => (string) $site->legalName(),
				"ownerName" => (string) $site->ownerName(),
				"street" => (string) $site->street(),
				"zip" => (string) $site->zip(),
				"city" => (string) $site->city(),
				"country" => (string) $site->country(),
				"phone" => (string) $site->phone(),
				"email" => (string) $site->email(),
			],
			'intendedTemplate' => $page->intendedTemplate()->name(),
			'title' => (string) $page->title(),
		]
	];
};
