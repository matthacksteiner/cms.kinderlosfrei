<?php

use Kirby\Toolkit\Str;

Kirby::plugin("baukasten/field-methods", [
	"fieldMethods" => [
		"getLinkArray" => function ($field, $title = true) {
			return getLinkArray($field, $title);
		},
		"getCategories" => function ($field, $categoryName = 'categories') {
			return getCategories($field, $categoryName);
		},
		"getAllCategories" => function ($field, $categoryName = 'categoriesNews') {
			return getAllCategories($field, $categoryName);
		},
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
		"uri" => $linkObject->page() ? (string) $linkObject->page()->uri() : null,
		"popup" => (bool) $linkObject->popup(),
		"hash" => $linkObject->hash(),
	];
}

function getCategories($field, $categoryName): ?array
{

	if (site()->{$categoryName}()->toStructure()->isEmpty()) {
		return [];
	}

	$categories = [];
	foreach (site()->{$categoryName}()->toStructure() as $category) {
		$name = $category->name();

		$categories[] = [
			"slug" => Str::slug($name),
			"name" => (string) $name,
		];
	}

	$categorySlugs = array_column($categories, "slug");
	$categoriesMapped = [];
	foreach ($field->split(',') as $item) {
		$categoryKey = array_search((string) $item, $categorySlugs);

		if (!$categories[$categoryKey]) {
			continue;
		}

		$categoriesMapped[] = $categories[$categoryKey];
	}

	return count($categoriesMapped) > 0 ? $categoriesMapped : null;
}

function getAllCategories($field, $categoryName): ?array
{
	if (site()->{$categoryName}()->toStructure()->isEmpty()) {
		return [];
	}

	$categories = [];
	foreach (site()->{$categoryName}()->toStructure() as $category) {
		$name = $category->name();

		$categories[] = [
			"slug" => Str::slug($name),
			"name" => (string) $name,
		];
	}

	return $categories;
}
