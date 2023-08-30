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

		$headline = [
			"font" => (string) $site->headlines()->toEntity()->$fontKey(),
			"size" => (string) $site->headlines()->toEntity()->$sizeKey(),
		];

		$headlines[$tag] = $headline;
	}


	return [
		'json' => [
			'global' => [
				"meta" => getMeta($site, $page, $kirby),
				'siteTitle' => (string) $site->title(),
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
