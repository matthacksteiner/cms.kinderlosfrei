<?php

use Kirby\Toolkit\Str;

Kirby::plugin("baukasten/field-methods", [
	"fieldMethods" => [
		"getLinkArray" => function ($field, $title = true) {
			return getLinkArray($field, $title);
		}
	],
]);

function getLinkArray($field): ?array
{
	$linkObject = $field->toLinkObject();
	if (!$linkObject) {
		return null;
	}

	$title = $linkObject->text() ? (string) $linkObject->text() : null;
	if ($title === null && $linkObject->page()) {
		$title = (string) $linkObject->page()->title();
	}

	return [
		"href" => $linkObject->page() ? null : $linkObject->href(),
		"title" => $title,
		"type" => $linkObject->type(),
		"uri" => $linkObject->page() ? (string) $linkObject->page()->uri() . ($linkObject->file() ? (string) $linkObject->page()->uri() : '') : null,
		"popup" => (bool) $linkObject->popup(),
		"hash" => $linkObject->hash(),
	];
}
