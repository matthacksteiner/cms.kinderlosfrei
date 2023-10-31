<?php

use Kirby\Toolkit\Config;

function getMeta($site, $page, $kirby)
{
	$meta = $page->meta();

	$json = [
		"title" => (string)$meta->title()->html(),
		"description" => $meta->description()->isNotEmpty() ? (string)$meta->description()->html() : null,
		"robots" => $meta->robots(),
		"canonical" => $meta->canonicalUrl(),
		"social" => [],
	];

	foreach ($meta->social() as $tag) {
		$json["social"][$tag["property"]] = $tag["content"];
	}

	return $json;
}

return function ($site, $page, $kirby) {
	return [
		'json' => [
			"meta" => getMeta($site, $page, $kirby),
			'intendedTemplate' => $page->intendedTemplate()->name(),
			'title' => (string)$page->title(),
			'language' => (string)kirby()->languageCode(),
		]
	];
};
