<?php

use Kirby\Toolkit\Str;
use Kirby\Content\Field;

Kirby::plugin("baukasten/field-methods", [
	"fieldMethods" => [
		"getLinkArray" => function ($field, $title = true) {
			return getLinkArray($field, $title);
		},
		"getNavArray" => function ($field) {
			return getNavArray($field);
		},
	],
]);


// function getNavArray($link)
// {
// 	$linkValue = preg_replace('/^(#|tel:)/', '', $link->link()->value());
// 	$linkType = getLinkType($link->link());
// 	$uri = determineUri($linkType, $link->link());

// 	return [
// 		'href' => in_array($linkType, ['url', 'tel', 'email']) ? $linkValue : null,
// 		'title' => $link->linkText()->value() ?: $linkValue,
// 		'popup' => $link->target()->toBool(),
// 		'hash' => $linkType === 'anchor' ? $linkValue : null,
// 		'type' => $linkType,
// 		'uri' => $uri,
// 		'classes' => $link->classnames()->value(),
// 	];
// }

function getLinkArray($field, $title = true): ?array
{
	if ($field->isEmpty()) {
		return null;
	}

	$link = $field->toObject();
	if (!$link) {
		return null;
	}

	$linkValue = preg_replace('/^(tel:)/', '', $link->link()->value());
	$linkType = getLinkType($link->link());

	$title = $title ? ($link->title() ?: null) : null;
	$uri = determineUri($linkType, $link->link());

	$anchorToggle = $link->anchorToggle()->toBool();
	$anchor = preg_replace('/^(#)/', '', $link->anchor());

	return [
		'href' => in_array($linkType, ['url', 'tel', 'email']) ? $linkValue : null,
		'title' => $link->linkText()->value() ?: $linkValue,
		'popup' => $link->target()->toBool(),
		'hash' => $anchorToggle ? $anchor : null,
		'hash' => $anchor,
		'type' => $linkType,
		'uri' => $uri,
		'classes' => $link->classnames()->value(),
	];
}

function getLinkType(Field $field): string
{
	$val = $field->value();
	if (empty($val)) return 'custom';

	if (Str::match($val, '/^(http|https):\/\//')) {
		return 'url';
	}

	if (Str::startsWith($val, 'page://') || Str::startsWith($val, '/@/page/')) {
		return 'page';
	}

	if (Str::startsWith($val, 'file://') || Str::startsWith($val, '/@/file/')) {
		return 'file';
	}

	if (Str::startsWith($val, 'tel:')) {
		return 'tel';
	}

	if (Str::startsWith($val, 'mailto:')) {
		return 'email';
	}

	if (Str::startsWith($val, '#')) {
		return 'anchor';
	}

	return 'custom';
}

function determineUri($linkType, $linkField)
{
	$uri = null;

	switch ($linkType) {
		case 'page':
			$uri = $linkField->toPage()?->uri();
			break;
		case 'file':
			$uri = $linkField->toUrl();
			break;
	}

	if ($uri === 'home') {
		$uri = '/';
	}

	return $uri;
}
