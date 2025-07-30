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
				return indexJson();
			}
		],
		[
			'pattern'  => 'global.json',
			'language' => '*',
			'method'   => 'GET',
			'action'   => function () {
				return globalJson();
			}
		],
		[
			'pattern'  => '/',
			'method'   => 'GET',
			'action'   => function () {
				return go('/panel');
			}
		],
		[
			'pattern'  => '(:any).json',
			'language' => '*',
			'method'   => 'GET',
			'action'   => function ($path) {
				$kirby = kirby();
				$site = site();

				// Get the full request URI to handle language routing correctly
				$fullPath = $kirby->request()->path();
				if (substr($fullPath, -5) === '.json') {
					$pageUri = substr($fullPath, 0, -5);
				} else {
					$pageUri = $path;
				}

				// Remove language prefix if present
				$languages = $kirby->languages();
				if ($languages) {
					foreach ($languages as $lang) {
						$langPrefix = $lang->code() . '/';
						if (substr($pageUri, 0, strlen($langPrefix)) === $langPrefix) {
							$pageUri = substr($pageUri, strlen($langPrefix));
							break;
						}
					}
				}

				// Skip requests for empty URIs (home page and excluded sections)
				if ($pageUri === '') {
					return null; // 404 for empty URI requests
				}

				// Try to find the page by flat URI
				$indexData = indexJsonData();
				foreach ($indexData as $pageData) {
					if ($pageData['uri'] === $pageUri) {
						// Found matching page by flat URI, get the actual page
						$actualPage = $site->find($pageData['id']);
						if ($actualPage) {
							return $actualPage->render([], 'json');
						}
					}
				}

				// Fallback: try direct page lookup
				$page = $site->find($pageUri);
				if ($page) {
					return $page->render([], 'json');
				}

				return null; // 404
			}
		],
		[
			'pattern'  => '(:any)',
			'language' => '*',
			'method'   => 'GET',
			'action'   => function ($path) {
				return handleFlatUrlResolution($path);
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
 * Get the current state of the designSectionToggle setting.
 *
 * @return bool True if sections should be included in URLs, false otherwise
 */
function getSectionToggleState(): bool
{
	return site()->designSectionToggle()->toBool(true);
}

/**
 * Generate a page URI based on the section toggle setting.
 *
 * @param \Kirby\Cms\Page $page The page to generate URI for
 * @param bool $respectSectionToggle Whether to respect the toggle setting
 * @return string The generated URI
 */
function generatePageUri($page, bool $respectSectionToggle = true): string
{
	// Handle home page specially - always return 'home' instead of empty string
	if ($page->isHomePage()) {
		return 'home';
	}

	// If not respecting toggle or toggle is enabled, use standard URI
	if (!$respectSectionToggle || getSectionToggleState()) {
		return $page->uri();
	}

	// Generate flat URI by skipping section parents
	$segments = [];
	$current = $page;

	while ($current && !$current->isHomePage()) {
		// Only add non-section pages to the URI segments
		if ($current->intendedTemplate()->name() !== 'section') {
			array_unshift($segments, $current->slug());
		}
		$current = $current->parent();
	}

	return implode('/', $segments);
}

/**
 * Get the effective parent of a page when sections might be excluded.
 *
 * @param \Kirby\Cms\Page $page The page to get the effective parent for
 * @return \Kirby\Cms\Page|null The effective parent page
 */
function getEffectiveParent($page)
{
	if (!$page->parent()) {
		return null;
	}

	// If toggle is enabled, return the actual parent
	if (getSectionToggleState()) {
		return $page->parent();
	}

	// If toggle is disabled, skip section parents
	$current = $page->parent();
	while ($current && !$current->isHomePage()) {
		if ($current->intendedTemplate()->name() !== 'section') {
			return $current;
		}
		$current = $current->parent();
	}

	// If we've reached the homepage or no parent, return the homepage
	return $current;
}

/**
 * Handle flat URL resolution when section toggle is disabled.
 * This route handler attempts to find pages by slug when direct URI match fails.
 *
 * @param string $path The requested path
 * @return mixed Page response or redirect
 */
function handleFlatUrlResolution($path)
{
	$kirby = kirby();
	$site = site();

	// First, try to find the page using standard Kirby resolution
	$page = $site->find($path);
	if ($page && $page->isReadable()) {
		return $page;
	}

	// If section toggle is enabled, let Kirby handle normally
	if (getSectionToggleState()) {
		return null; // Let Kirby's default 404 handling take over
	}

	// Section toggle is disabled - try to resolve flat URLs
	$segments = explode('/', trim($path, '/'));
	$lastSegment = end($segments);

	// Find all pages that could match this flat URL structure
	$candidatePages = findPagesByFlatUrl($path, $segments);

	if (empty($candidatePages)) {
		// No candidates found - try fallback to hierarchical URL
		return tryHierarchicalFallback($path, $segments);
	}

	// If we have exactly one candidate, use it
	if (count($candidatePages) === 1) {
		$page = $candidatePages[0];
		if ($page->isReadable()) {
			return $page;
		}
	}

	// Multiple candidates - use priority resolution
	$resolvedPage = resolvePriorityConflict($candidatePages, $path);
	if ($resolvedPage && $resolvedPage->isReadable()) {
		return $resolvedPage;
	}

	// Still no resolution - try hierarchical fallback
	return tryHierarchicalFallback($path, $segments);
}

/**
 * Find pages that could match a flat URL structure.
 */
function findPagesByFlatUrl($path, $segments)
{
	$site = site();
	$candidates = [];
	$lastSegment = end($segments);

	// Search through all pages to find matches
	foreach ($site->index() as $page) {
		// Skip section pages as they shouldn't be accessible in flat mode
		if ($page->intendedTemplate()->name() === 'section') {
			continue;
		}

		// Check if this page's flat URL matches the requested path
		$flatUri = generatePageUri($page, true);
		if ($flatUri === $path) {
			$candidates[] = $page;
			continue;
		}

		// Also check if the page slug matches the last segment
		if ($page->slug() === $lastSegment) {
			// Verify this page would generate the requested flat URL
			$expectedFlatUri = generatePageUri($page, true);
			if ($expectedFlatUri === $path) {
				$candidates[] = $page;
			}
		}
	}

	return $candidates;
}

/**
 * Resolve conflicts when multiple pages could match the same flat URL.
 */
function resolvePriorityConflict($candidates, $path)
{
	if (empty($candidates)) {
		return null;
	}

	// Sort candidates by priority
	usort($candidates, function ($a, $b) {
		// Priority 1: Fewer parent levels (closer to root)
		$aDepth = count(explode('/', $a->uri()));
		$bDepth = count(explode('/', $b->uri()));
		if ($aDepth !== $bDepth) {
			return $aDepth - $bDepth;
		}

		// Priority 2: Listed pages over unlisted
		if ($a->isListed() !== $b->isListed()) {
			return $b->isListed() ? 1 : -1;
		}

		// Priority 3: Sort order (num field)
		$aNum = $a->num();
		$bNum = $b->num();
		if ($aNum !== $bNum) {
			return $aNum - $bNum;
		}

		// Priority 4: Alphabetical by slug as final tiebreaker
		return strcmp($a->slug(), $b->slug());
	});

	return $candidates[0];
}

/**
 * Try to find a page using hierarchical URL structure as fallback.
 */
function tryHierarchicalFallback($path, $segments)
{
	$site = site();

	// Try to find the page using hierarchical structure
	$hierarchicalPage = $site->find($path);
	if ($hierarchicalPage && $hierarchicalPage->isReadable()) {
		// If we found a hierarchical page and section toggle is disabled,
		// we might want to redirect to the flat URL equivalent
		$flatUri = generatePageUri($hierarchicalPage, true);
		if ($flatUri !== $path && !getSectionToggleState()) {
			// Redirect to the flat URL
			return go($flatUri, 301);
		}
		return $hierarchicalPage;
	}

	// No fallback found - return null to trigger 404
	return null;
}

/**
 * Check if a section page should be accessible based on the toggle setting.
 */
function isSectionPageAccessible($page)
{
	// If the page is not a section, it's always accessible
	if ($page->intendedTemplate()->name() !== 'section') {
		return true;
	}

	// Section pages are only accessible when the toggle is enabled
	return getSectionToggleState();
}

/**
 * Get navigation siblings for a page, handling section toggle logic
 */
function getNavigationSiblings($page, $effectiveParent)
{
	$sectionToggleEnabled = getSectionToggleState();

	if ($sectionToggleEnabled) {
		// Standard behavior: get siblings from actual parent
		$parent = $page->parent();
		if (!$parent) {
			return new \Kirby\Cms\Pages();
		}

		return $parent->children()->listed()->filter(function ($sibling) {
			return !($sibling->intendedTemplate()->name() == 'item' && $sibling->coverOnly()->toBool(false));
		});
	} else {
		// Flat URL mode: find siblings that share the same original parent (section)
		$originalParent = $page->parent();
		if (!$originalParent) {
			return new \Kirby\Cms\Pages();
		}

		// Get all children of the original parent (section)
		return $originalParent->children()->listed()->filter(function ($sibling) {
			return !($sibling->intendedTemplate()->name() == 'item' && $sibling->coverOnly()->toBool(false));
		});
	}
}

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
 * Get next and previous page navigation for pages within the same parent/section
 */
function getPageNavigation($page)
{
	// Get the effective parent based on section toggle setting
	$effectiveParent = getEffectiveParent($page);

	// Get siblings - this works even if effective parent is null
	$siblings = getNavigationSiblings($page, $effectiveParent);

	$nextPage = null;
	$prevPage = null;

	// Use Kirby's built-in navigation methods with custom collection
	if ($next = $page->nextListed($siblings)) {
		$nextPage = [
			'title' => (string) $next->title(),
			'uri' => generatePageUri($next),
			'id' => $next->id(),
		];
	}

	if ($prev = $page->prevListed($siblings)) {
		$prevPage = [
			'title' => (string) $prev->title(),
			'uri' => generatePageUri($prev),
			'id' => $prev->id(),
		];
	}

	return [
		'nextPage' => $nextPage,
		'prevPage' => $prevPage,
	];
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

		// Skip pages with empty URIs when they shouldn't be accessible
		$pageUri = generatePageUri($page);
		if ($pageUri === '') {
			// Skip sections when toggle is disabled, but keep home page
			if ($page->intendedTemplate()->name() === 'section' && !getSectionToggleState()) {
				continue;
			}
		}

		$translations = [];
		foreach ($kirby->languages() as $language) {
			// Generate URI for specific language without changing global context
			if (getSectionToggleState()) {
				// When toggle is enabled, use hierarchical URIs
				$translations[$language->code()] = $page->uri($language->code());
			} else {
				// When toggle is disabled, generate flat URI for this language
				if ($page->isHomePage()) {
					$translations[$language->code()] = 'home';
				} else {
					// Generate flat URI by skipping section parents
					$segments = [];
					$current = $page;

					while ($current && !$current->isHomePage()) {
						// Only add non-section pages to the URI segments
						if ($current->intendedTemplate()->name() !== 'section') {
							array_unshift($segments, $current->slug($language->code()));
						}
						$current = $current->parent();
					}

					$translations[$language->code()] = implode('/', $segments);
				}
			}
		}

		// Get effective parent for parent reference
		$effectiveParent = null;
		if ($page->intendedTemplate()->name() == 'item') {
			$parent = getEffectiveParent($page);
			$effectiveParent = $parent ? generatePageUri($parent) : null;
		}

		$index[] = [
			"id"               => $page->id(),
			"uri"              => generatePageUri($page),
			"status"           => $page->status(),
			"intendedTemplate" => $page->intendedTemplate()->name(),
			"parent"           => $effectiveParent,
			"coverOnly"        => $page->intendedTemplate()->name() == 'item'
				? $page->coverOnly()->toBool(false)
				: null,
			"translations"     => $translations,
			"navigation"       => getPageNavigation($page),
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
