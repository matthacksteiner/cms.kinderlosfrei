<?php

use Kirby\Filesystem\Dir;
use Kirby\Filesystem\F;
use Kirby\Http\Response;

/*
|--------------------------------------------------------------------------
| Kirby Configuration Array
|--------------------------------------------------------------------------
*/

return [
	// 'debug' => true,
	'auth' => [
		'methods' => ['password', 'password-reset']
	],
	'panel.install'   => true,
	'date.handler'    => 'strftime',
	'locale'          => 'de_AT.utf-8',
	'languages'       => true,
	'prefixDefaultLocale' => false,
	'error'           => 'z-error',
	'panel' => [
		'css'     => 'assets/css/baukasten-panel.css',
		'favicon' => 'assets/img/baukasten-favicon.ico',
	],
	'thumbs' => [
		'quality' => 99,
		'format'  => 'webp',
	],
	'cache' => [
		// Simple API cache for build performance
		'api' => true
	],
	'hooks' => [
		// Simple cache invalidation - only clear when content actually changes
		'page.update:after' => function ($newPage, $oldPage) {
			kirby()->cache('api')->flush();
		},
		'site.update:after' => function ($newSite, $oldSite) {
			kirby()->cache('api')->flush();
		}
	],
	'ready' => function () {
		return [
			'johannschopplich.deploy-trigger' => [
				'deployUrl' => env('DEPLOY_URL', 'https://api.netlify.com/build_hooks/65142ee2a2de9b24080dcc95'),
			],
		];
	},
	'routes' => [
		[
			'pattern'  => 'index.json',
			'language' => '*',
			'method'   => 'GET',
			'action'   => function () {
				return indexJsonCached();
			}
		],
		[
			'pattern'  => 'global.json',
			'language' => '*',
			'method'   => 'GET',
			'action'   => function () {
				return globalJsonCached();
			}
		],
		[
			'pattern'  => '/',
			'method'   => 'GET',
			'action'   => function () {
				return go('/panel');
			}
		]
	],
];


/*
|--------------------------------------------------------------------------
| Helper Functions
|--------------------------------------------------------------------------
*/

/**
 * Returns an array of languages excluding the default language.
 */
function getTranslations($kirby)
{
	$default = $kirby->defaultLanguage();
	$translations = [];
	foreach ($kirby->languages() as $language) {
		if ($language->code() !== $default->code()) {
			$translations[] = [
				"code"   => $language->code(),
				"name"   => $language->name(),
				"url"    => $language->url(),
				"locale" => $language->locale(LC_ALL),
				"active" => $language->code() === $kirby->language()->code(),
			];
		}
	}
	return $translations;
}

/**
 * Returns an array of all languages.
 */
function getAllLanguages($kirby)
{
	$all = [];
	foreach ($kirby->languages() as $language) {
		$all[] = [
			"code"   => $language->code(),
			"name"   => $language->name(),
			"url"    => $language->url(),
			"locale" => $language->locale(LC_ALL),
			"active" => $language->code() === $kirby->language()->code(),
		];
	}
	return $all;
}

/**
 * Returns the default language information.
 */
function getDefaultLanguage($kirby)
{
	$default = $kirby->defaultLanguage();
	return [
		"code"   => $default->code(),
		"name"   => $default->name(),
		"url"    => option('prefixDefaultLocale')
			? $default->url()
			: str_replace('/' . $default->code(), '', $default->url()),
		"locale" => $default->locale(LC_ALL),
		"active" => $default->code() === $kirby->language()->code(),
	];
}

/**
 * Extract favicon data from the site.
 */
function getFavicon($site)
{
	$fav = $site->faviconFiles()->toObject();
	if (!$fav) {
		return null;
	}
	return [
		"svgSrc"     => $fav->faviconFileSvg()->toFile() ? (string)$fav->faviconFileSvg()->toFile()->url() : null,
		"icoSrc"     => $fav->faviconFileIco()->toFile() ? (string)$fav->faviconFileIco()->toFile()->url() : null,
		"png192Src"  => $fav->faviconFilePng1()->toFile() ? (string)$fav->faviconFilePng1()->toFile()->url() : null,
		"png512Src"  => $fav->faviconFilePng2()->toFile() ? (string)$fav->faviconFilePng2()->toFile()->url() : null,
		"pngAppleSrc" => $fav->faviconFilePng3()->toFile() ? (string)$fav->faviconFilePng3()->toFile()->url() : null,
	];
}

/**
 * Returns a navigation array from the given field.
 */
function getNavigation($site, $field)
{
	$nav = [];
	foreach ($site->$field()->toStructure() as $item) {
		$nav[] = getLinkArray($item->linkobject());
	}
	return count($nav) > 0 ? $nav : null;
}

/**
 * Returns logo file data.
 */
function getLogoFile($site)
{
	$logoObj = $site->headerLogo()->toObject();
	if ($logoObj->logoFile()->isNotEmpty()) {
		$file = $logoObj->logoFile()->toFile();
		return [
			"src"    => (string)$file->url(),
			"alt"    => (string)$file->alt()->or($site->title() . " Logo"),
			"source" => file_get_contents($file->root()),
			"width"  => $file->width(),
			"height" => $file->height(),
			"source" => file_get_contents($file->root()),
		];
	}
	return [];
}

/**
 * Returns the logo CTA as a link array.
 */
function getLogoCta($site)
{
	$logoObj = $site->headerLogo()->toObject();
	if ($logoObj->logoCta()->isNotEmpty()) {
		return getLinkArray($logoObj->logoCta());
	}
	return [];
}

/**
 * Processes font file structures.
 */
function getFonts($site)
{
	$fonts = [];
	foreach ($site->fontFile()->toStructure() as $fontItem) {
		$file2 = $fontItem->file2()->toFile();
		if ($file2) {
			$fonts[] = [
				"name"        => (string)$fontItem->name(),
				"url2"        => (string)$file2->url(),
			];
		}
	}
	return count($fonts) > 0 ? $fonts : null;
}

/**
 * Processes font size structures.
 */
function getFontSizes($site)
{
	$sizes = [];
	foreach ($site->fontSize()->toStructure() as $item) {
		$sizes[] = [
			"name"                => (string)$item->name(),
			"sizeMobile"          => (string)$item->sizeMobile(),
			"lineHeightMobile"    => (string)$item->lineHeightMobile(),
			"letterSpacingMobile" => (string)$item->letterSpacingMobile(),
			"sizeDesktop"         => (string)$item->sizeDesktop(),
			"lineHeightDesktop"   => (string)$item->lineHeightDesktop(),
			"letterSpacingDesktop" => (string)$item->letterSpacingDesktop(),
			"sizeDesktopXl"       => (string)$item->sizeDesktopXl(),
			"lineHeightDesktopXl" => (string)$item->lineHeightDesktopXl(),
			"letterSpacingDesktopXl" => (string)$item->letterSpacingDesktopXl(),
			"transform"           => (string)$item->transform() ?: 'none',
			"decoration"          => (string)$item->decoration() ?: 'none',
		];
	}
	return $sizes;
}

/**
 * Extract headlines from the site.
 */
function getHeadlines($site)
{
	$headlines = [];
	for ($i = 1; $i <= 6; $i++) {
		$tag = "h$i";
		$fontKey = "{$tag}font";
		$sizeKey = "{$tag}size";
		$headlines[$tag] = [
			"font" => (string)$site->headlines()->toObject()->$fontKey(),
			"size" => (string)$site->headlines()->toObject()->$sizeKey(),
		];
	}
	return $headlines;
}

/**
 * Returns analytics codes if toggled on.
 */
function getAnalytics($site)
{
	$searchConsoleCode = $site->searchConsoleToggle()->toBool(false)
		? (string)$site->searchConsoleCode()
		: null;
	$googleAnalyticsCode = $site->googleAnalyticsToggle()->toBool(false)
		? (string)$site->googleAnalyticsCode()
		: null;
	$analyticsLink = $site->analyticsLink()->isNotEmpty()
		? getLinkArray($site->analyticsLink())
		: null;
	return [
		'searchConsoleCode'  => $searchConsoleCode,
		'googleAnalyticsCode' => $googleAnalyticsCode,
		'analyticsLink'       => $analyticsLink,
	];
}

/**
 * Handles the cached index.json route action.
 */
function indexJsonCached()
{
	$kirby = kirby();
	$apiCache = $kirby->cache('api');
	$language = $kirby->language() ? $kirby->language()->code() : 'default';
	$cacheKey = 'index.' . $language;

	// Try to get cached data
	$cached = $apiCache->get($cacheKey);
	if ($cached !== null) {
		return Response::json($cached);
	}

	// Generate fresh data and cache for 7 days
	$data = indexJsonData();
	$apiCache->set($cacheKey, $data, 10080);

	return Response::json($data);
}

/**
 * Handles the cached global.json route action.
 */
function globalJsonCached()
{
	$kirby = kirby();
	$apiCache = $kirby->cache('api');
	$language = $kirby->language() ? $kirby->language()->code() : 'default';
	$cacheKey = 'global.' . $language;

	// Try to get cached data
	$cached = $apiCache->get($cacheKey);
	if ($cached !== null) {
		return Response::json($cached);
	}

	// Generate fresh data and cache for 30 days
	$data = globalJsonData();
	$apiCache->set($cacheKey, $data, 43200);

	return Response::json($data);
}

/**
 * Generates the index data (extracted from original indexJson function).
 */
function indexJsonData()
{
	$kirby = kirby();
	$index = [];
	foreach (site()->index() as $page) {
		// Skip pages that have coverOnly set to true
		if ($page->intendedTemplate()->name() == 'item' && $page->coverOnly()->toBool(false)) {
			continue;
		}

		$translations = [];
		foreach ($kirby->languages() as $language) {
			$translations[$language->code()] = $page->uri($language->code());
		}
		$index[] = [
			"id"               => $page->id(),
			"uri"              => $page->uri(),
			"intendedTemplate" => $page->intendedTemplate()->name(),
			"parent"           => $page->intendedTemplate()->name() == 'item'
				? $page->parent()->uri()
				: null,
			"coverOnly"        => $page->intendedTemplate()->name() == 'item'
				? $page->coverOnly()->toBool(false)
				: null,
			"translations"     => $translations
		];
	}
	return $index;
}

/**
 * Generates the global data (extracted from original globalJson function).
 */
function globalJsonData()
{
	$site   = site();
	$kirby  = kirby();
	$analytics = getAnalytics($site);

	return [
		"kirbyUrl"              => (string)$kirby->url('index'),
		"siteUrl"               => (string)$site->url(),
		"siteTitle"             => (string)$site->title(),
		"defaultLang"           => getDefaultLanguage($kirby),
		"translations"          => getTranslations($kirby),
		"prefixDefaultLocale"   => option('prefixDefaultLocale'),
		"allLang"               => getAllLanguages($kirby),
		"favicon"               => getFavicon($site),
		"frontendUrl"           => (string)$site->frontendUrl(),
		"navHeader"             => getNavigation($site, 'navHeader'),
		"navHamburger"          => getNavigation($site, 'navHambuger'),
		"colorPrimary"          => (string)$site->colorPrimary(),
		"colorSecondary"        => (string)$site->colorSecondary(),
		"colorTertiary"         => (string)$site->colorTertiary(),
		"colorBlack"            => (string)$site->colorBlack(),
		"colorWhite"            => (string)$site->colorWhite(),
		"colorTransparent"      => (string)$site->colorTransparent(),
		"colorBackground"       => (string)$site->colorBackground(),
		"font"                  => getFonts($site),
		"fontSize"              => getFontSizes($site),
		"headlines"             => getHeadlines($site),
		"headerActive"          => $site->headerActive()->toBool(),
		"headerFont"            => (string)$site->headerMenu()->toObject()->headerFont(),
		"headerFontSize"        => (string)$site->headerMenu()->toObject()->headerFontSize(),
		"headerColor"           => (string)$site->headerMenu()->toObject()->headerColor(),
		"headerColorActive"     => (string)$site->headerMenu()->toObject()->headerColorActive(),
		"headerBackground"      => (string)$site->headerMenu()->toObject()->headerBackground(),
		"headerBackgroundActive" => (string)$site->headerMenu()->toObject()->headerBackgroundActive(),
		"hamburgerFont"         => (string)$site->headerHamburger()->toObject()->hamburgerFont(),
		"hamburgerFontSize"     => (string)$site->headerHamburger()->toObject()->hamburgerFontSize(),
		"hamburgerFontColor"    => (string)$site->headerHamburger()->toObject()->hamburgerFontColor(),
		"hamburgerMenuColor"    => (string)$site->headerHamburger()->toObject()->hamburgerMenuColor(),
		"hamburgerMenuColorActive" => (string)$site->headerHamburger()->toObject()->hamburgerMenuColorActive(),
		"hamburgerOverlay"      => (string)$site->headerHamburger()->toObject()->hamburgerOverlay(),
		"logoFile"              => getLogoFile($site),
		"logoAlign"             => (string)$site->headerLogo()->toObject()->logoAlign(),
		"logoCta"               => getLogoCta($site),
		"logoDesktop"           => (string)$site->headerLogo()->toObject()->logoDesktop(),
		"logoMobile"            => (string)$site->headerLogo()->toObject()->logoMobile(),
		"logoDesktopActive"     => (string)$site->headerLogo()->toObject()->logoDesktopActive(),
		"logoMobileActive"      => (string)$site->headerLogo()->toObject()->logoMobileActive(),
		"gridGapMobile"         => (string)$site->gridGapMobile(),
		"gridMarginMobile"      => (string)$site->gridMarginMobile(),
		"gridGapDesktop"        => (string)$site->gridGapDesktop(),
		"gridMarginDesktop"     => (string)$site->gridMarginDesktop(),
		"gridBlockMobile"       => (string)$site->gridBlockMobile(),
		"gridBlockDesktop"      => (string)$site->gridBlockDesktop(),
		"buttonFont"            => (string)$site->buttonSettings()->toObject()->buttonFont(),
		"buttonFontSize"        => (string)$site->buttonSettings()->toObject()->buttonFontSize(),
		"buttonBorderRadius"    => (string)$site->buttonSettings()->toObject()->buttonBorderRadius(),
		"buttonBorderWidth"     => (string)$site->buttonSettings()->toObject()->buttonBorderWidth(),
		"buttonPadding"         => (string)$site->buttonSettings()->toObject()->buttonPadding(),
		"buttonBackgroundColor" => (string)$site->buttonColors()->toObject()->buttonBackgroundColor(),
		"buttonBackgroundColorActive" => (string)$site->buttonColors()->toObject()->buttonBackgroundColorActive(),
		"buttonTextColor"       => (string)$site->buttonColors()->toObject()->buttonTextColor(),
		"buttonTextColorActive" => (string)$site->buttonColors()->toObject()->buttonTextColorActive(),
		"buttonBorderColor"     => (string)$site->buttonColors()->toObject()->buttonBorderColor(),
		"buttonBorderColorActive" => (string)$site->buttonColors()->toObject()->buttonBorderColorActive(),
		"paginationFont"        => (string)$site->paginationSettings()->toObject()->paginationFont(),
		"paginationFontSize"    => (string)$site->paginationSettings()->toObject()->paginationFontSize(),
		"paginationBorderRadius" => (string)$site->paginationSettings()->toObject()->paginationBorderRadius(),
		"paginationBorderWidth" => (string)$site->paginationSettings()->toObject()->paginationBorderWidth(),
		"paginationPadding"     => (string)$site->paginationSettings()->toObject()->paginationPadding() ?: '10',
		"paginationMargin"      => (string)$site->paginationSettings()->toObject()->paginationMargin() ?: '10',
		"paginationElements"    => (string)$site->paginationSettings()->toObject()->paginationElements(),
		"paginationTop"         => (string)$site->paginationSettings()->toObject()->paginationTop() ?: '16',
		"paginationBottom"      => (string)$site->paginationSettings()->toObject()->paginationBottom() ?: '16',
		"paginationBackgroundColor" => (string)$site->paginationColors()->toObject()->paginationBackgroundColor(),
		"paginationBackgroundColorHover" => (string)$site->paginationColors()->toObject()->paginationBackgroundColorHover(),
		"paginationBackgroundColorActive" => (string)$site->paginationColors()->toObject()->paginationBackgroundColorActive(),
		"paginationTextColor"   => (string)$site->paginationColors()->toObject()->paginationTextColor(),
		"paginationTextColorHover" => (string)$site->paginationColors()->toObject()->paginationTextColorHover(),
		"paginationTextColorActive" => (string)$site->paginationColors()->toObject()->paginationTextColorActive(),
		"paginationBorderColor" => (string)$site->paginationColors()->toObject()->paginationBorderColor(),
		"paginationBorderColorHover" => (string)$site->paginationColors()->toObject()->paginationBorderColorHover(),
		"paginationBorderColorActive" => (string)$site->paginationColors()->toObject()->paginationBorderColorActive(),
		"searchConsoleToggle"   => $site->searchConsoleToggle()->toBool(false),
		"searchConsoleCode"     => $analytics['searchConsoleCode'],
		"googleAnalyticsToggle" => $site->googleAnalyticsToggle()->toBool(false),
		"googleAnalyticsCode"   => $analytics['googleAnalyticsCode'],
		"analyticsLink"         => $analytics['analyticsLink'],

		"claimText" => (string) $site->headerClaim()->toObject()->claimText(),
		"claimFont" => (string) $site->headerClaim()->toObject()->claimFont(),
		"claimFontSize" => (string) $site->headerClaim()->toObject()->claimFontSize(),
	];
}

/**
 * Handles the index.json route action.
 */
function indexJson()
{
	return Response::json(indexJsonData());
}

/**
 * Handles the global.json route action.
 */
function globalJson()
{
	return Response::json(globalJsonData());
}
