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

	// ---------- settings ----------

	// favicon
	$siteFavicon = $site->faviconFiles()->toEntity();
	$favicon = null;
	if ($siteFavicon) {
		$favicon = [
			"svgSrc" => $siteFavicon->faviconFileSvg()->toFile() ? (string) $siteFavicon->faviconFileSvg()->toFile()->url() : null,
			"icoSrc" => $siteFavicon->faviconFileIco()->toFile() ? (string) $siteFavicon->faviconFileIco()->toFile()->url() : null,
			"png192Src" => $siteFavicon->faviconFilePng1()->toFile() ? (string) $siteFavicon->faviconFilePng1()->toFile()->url() : null,
			"png512Src" => $siteFavicon->faviconFilePng2()->toFile() ? (string) $siteFavicon->faviconFilePng2()->toFile()->url() : null,
			"pngAppleSrc" => $siteFavicon->faviconFilePng3()->toFile() ? (string) $siteFavicon->faviconFilePng3()->toFile()->url() : null,
		];
	}


	// header menu
	$header = [];
	foreach ($site->navHeader()->toStructure() as $headerItem) {
		$linkArrayHeader = $headerItem->link()->getLinkArray();
		$header[] = $linkArrayHeader;
	}
	$header = count($header) > 0 ? $header : null;

	// hambuger menu
	$hambuger = [];
	foreach ($site->navhambuger()->toStructure() as $hambugerItem) {
		$linkArrayHamburger = $hambugerItem->link()->getLinkArray();
		$hambuger[] = $linkArrayHamburger;
	}
	$hambuger = count($hambuger) > 0 ? $hambuger : null;



	// ---------- design ----------

	// header
	$logoFile = [];
	if ($site->headerLogo()->toEntity()->logoFile()->isNotEmpty()) {
		$logoFile = [
			"src" => (string) $site->headerLogo()->toEntity()->logoFile()->toFile()->url(),
			"alt" => (string) $site->headerLogo()->toEntity()->logoFile()->toFile()->alt()->or($site->title() . " Logo"),
		];
	}

	//font files
	$font = [];
	foreach ($site->fontFile()->toStructure() as $fontItem) {
		$font[] = [
			"name" => (string)$fontItem->name(),
			"url1" => (string)$fontItem->file1()->toFile()->url(),
			"url2" => (string)$fontItem->file2()->toFile()->url(),
		];
	}
	$font = count($font) > 0 ? $font : null;

	//font sizes
	$fontSize = [];
	foreach ($site->fontSize()->toStructure() as $fontSizeItem) {
		$fontSize[] = [
			"name" => (string)$fontSizeItem->name(),
			"sizeMobile" => (string)$fontSizeItem->sizeMobile(),
			"lineHeightMobile" => (string)$fontSizeItem->lineHeightMobile(),
			"sizeDesktop" => (string)$fontSizeItem->sizeDesktop(),
			"lineHeightDesktop" => (string)$fontSizeItem->lineHeightDesktop(),
		];
	}

	// headlines
	$headlines = [];

	for ($i = 1; $i <= 6; $i++) {
		$tag = "h$i";
		$fontKey = "{$tag}font";
		$sizeKey = "{$tag}size";
		$colorKey = "{$tag}color";
		$alignKey = "{$tag}align";

		$headline = [
			"font" => (string) $site->headlines()->toEntity()->$fontKey(),
			"size" => (string) $site->headlines()->toEntity()->$sizeKey(),
			"color" => (string) $site->headlines()->toEntity()->$colorKey(),
			"align" => (string) $site->headlines()->toEntity()->$alignKey(),
		];

		$headlines[$tag] = $headline;
	}


	return [
		'json' => [
			'global' => [
				'siteTitle' => (string) $site->title(),
				"meta" => getMeta($site, $page, $kirby),

				"favicon" => $favicon,

				"navHeader" => $header,
				"navHamburger" => $hambuger,
				"company" => (string) $site->addressCompany(),
				"street" => (string) $site->street(),
				"zip" => (string) $site->zip(),
				"city" => (string) $site->city(),
				"country" => (string) $site->country(),
				"phone" => (string) $site->phone(),
				"email" => (string) $site->email(),
				"register" => (string) $site->addressRegister(),
				"court" => (string) $site->addressCourt(),

				"colorPrimary" => (string) $site->colorPrimary(),
				"colorSecondary" => (string) $site->colorSecondary(),
				"colorTertiary" => (string) $site->colorTertiary(),
				"colorBlack" => (string) $site->colorBlack(),
				"colorWhite" => (string) $site->colorWhite(),
				"colorTransparent" => (string) $site->colorTransparent(),
				"colorBackground" => (string) $site->colorBackground(),

				"font" => $font,
				"fontSize" => $fontSize,
				"headlines" => $headlines,

				"headerFont" => (string) $site->headerMenu()->toEntity()->headerFont(),
				"headerFontSize" => (string) $site->headerMenu()->toEntity()->headerFontSize(),
				"headerColor" => (string) $site->headerMenu()->toEntity()->headerColor(),
				"headerColorActive" => (string) $site->headerMenu()->toEntity()->headerColorActive(),
				"headerBackground" => (string) $site->headerMenu()->toEntity()->headerBackground(),
				"headerBackgroundActive" => (string) $site->headerMenu()->toEntity()->headerBackgroundActive(),

				"hamburgerFont" => (string) $site->headerHamburger()->toEntity()->hamburgerFont(),
				"hamburgerFontSize" => (string) $site->headerHamburger()->toEntity()->hamburgerFontSize(),
				"hamburgerFontColor" => (string) $site->headerHamburger()->toEntity()->hamburgerFontColor(),
				"hamburgerMenuColor" => (string) $site->headerHamburger()->toEntity()->hamburgerMenuColor(),
				"hamburgerMenuColorActive" => (string) $site->headerHamburger()->toEntity()->hamburgerMenuColorActive(),
				"hamburgerOverlay" => (string) $site->headerHamburger()->toEntity()->hamburgerOverlay(),

				"logoFile" => $logoFile,
				"logoAlign" => (string) $site->headerLogo()->toEntity()->logoAlign(),
				"logoDesktop" => (string) $site->headerLogo()->toEntity()->logoDesktop(),
				"logoMobile" => (string) $site->headerLogo()->toEntity()->logoMobile(),
				"logoDesktopActive" => (string) $site->headerLogo()->toEntity()->logoDesktopActive(),
				"logoMobileActive" => (string) $site->headerLogo()->toEntity()->logoMobileActive(),

				"gridGapMobile" => (string) $site->gridGapMobile(),
				"gridMarginMobile" => (string) $site->gridMarginMobile(),
				"gridGapDesktop" => (string) $site->gridGapDesktop(),
				"gridMarginDesktop" => (string) $site->gridMarginDesktop(),
				"gridBlockMobile" => (string) $site->gridBlockMobile(),
				"gridBlockDesktop" => (string) $site->gridBlockDesktop(),

				"buttonFont" => (string) $site->buttonSettings()->toEntity()->buttonFont(),
				"buttonFontSize" => (string) $site->buttonSettings()->toEntity()->buttonFontSize(),
				"buttonBorderRadius" => (string) $site->buttonSettings()->toEntity()->buttonBorderRadius(),
				"buttonBorderWidth" => (string) $site->buttonSettings()->toEntity()->buttonBorderWidth(),
				"buttonPadding" => (string) $site->buttonSettings()->toEntity()->buttonPadding(),
				"buttonBackgroundColor" => (string) $site->buttonColors()->toEntity()->buttonBackgroundColor(),
				"buttonBackgroundColorActive" => (string) $site->buttonColors()->toEntity()->buttonBackgroundColorActive(),
				"buttonTextColor" => (string) $site->buttonColors()->toEntity()->buttonTextColor(),
				"buttonTextColorActive" => (string) $site->buttonColors()->toEntity()->buttonTextColorActive(),
				"buttonBorderColor"	=> (string) $site->buttonColors()->toEntity()->buttonBorderColor(),
				"buttonBorderColorActive" => (string) $site->buttonColors()->toEntity()->buttonBorderColorActive(),

			],
			'intendedTemplate' => $page->intendedTemplate()->name(),
			'title' => (string) $page->title(),
		]
	];
};
