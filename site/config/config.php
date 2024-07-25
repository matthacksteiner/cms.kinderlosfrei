<?php

use Kirby\Filesystem\Dir;
use Kirby\Filesystem\F;
use Kirby\Http\Response;

return [
	'debug' => true,
	'auth' => [
		'methods' => ['password', 'password-reset']
	],
	'panel.install' => true,
	'date.handler' => 'strftime',
	'locale' => 'de_AT.utf-8',
	'languages' => true,
	'prefixDefaultLocale' => false,
	'error' => 'z-error',
	'pju.webhook-field.hooks' => [
		'netlify_deploy' => [
			'url' => 'https://api.netlify.com/build_hooks/65142ee2a2de9b24080dcc95',
			'callback' => function ($status) {
				if ($status === 'error') {
					error_log('There was an error with the production webhook');
				}
			}
		]
	],
	'pju.webhook-field.labels' => [
		'success' => [
			'name' => 'Webhook %hookName% Erfolgreich',
			'cta'  => 'Nochmals versuchen?'
		]
	],
	'panel' => [
		'css' => 'assets/css/baukasten-panel.css',
		'favicon' => 'assets/img/baukasten-favicon.ico',
	],
	'thumbs' => [
		'quality'   => 99,
		'format'    => 'webp',
	],
	'routes' => [
		[
			'pattern' => 'index.json',
			'language' => '*',
			'method' => 'GET',
			'action' => function () {
				$index = [];
				foreach (site()->index() as $page) {
					$index[] = [
						"uri" => $page->uri(),
						"intendedTemplate" => $page->intendedTemplate()->name(),
						"parent" => $page->intendedTemplate()->name() == 'item' ? $page->parent()->uri() : null,

					];
				}

				return response::json($index);
			}
		],
		[
			'pattern' => 'global.json',
			'language' => '*',
			'method' => 'GET',
			'action' => function () {
				$site = site();
				$kirby = kirby();

				// languages
				$translations = [];
				$defaultLang = $kirby->defaultLanguage();
				foreach ($kirby->languages() as $language) {
					if ($language->code() != $defaultLang->code()) {
						$translations[] = [
							"code" => $language->code(),
							"name" => $language->name(),
							"url" => $language->url(),
							"locale" => $language->locale(LC_ALL),
							"active" => $language->code() == $kirby->language()->code(),
						];
					}
				}
				$allLang = [];
				foreach ($kirby->languages() as $language) {
					$allLang[] = [
						"code" => $language->code(),
						"name" => $language->name(),
						"url" => $language->url(),
						"locale" => $language->locale(LC_ALL),
						"active" => $language->code() == $kirby->language()->code(),
					];
				}
				$defaultLang = [
					"code" => $defaultLang->code(),
					"name" => $defaultLang->name(),
					"url" => $defaultLang->url(),
					"locale" => $defaultLang->locale(LC_ALL),
					"active" => $defaultLang->code() == $kirby->language()->code(),
				];


				// favicon
				$siteFavicon = $site->faviconFiles()->toObject();
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
					$link = $headerItem;
					$linkArray = getNavArray($link);
					$header[] = $linkArray;
				}
				$header = count($header) > 0 ? $header : null;

				// hamburger menu
				$hamburger = [];
				foreach ($site->navHambuger()->toStructure() as $hamburgerItem) {
					$link = $hamburgerItem;
					$linkArray = getNavArray($link);
					$hamburger[] = $linkArray;
				}
				$hamburger = count($hamburger) > 0 ? $hamburger : null;



				// ---------- design ----------
				// header
				$logoFile = [];
				if ($site->headerLogo()->toObject()->logoFile()->isNotEmpty()) {
					$logoFile = [
						"src" => (string) $site->headerLogo()->toObject()->logoFile()->toFile()->url(),
						"alt" => (string) $site->headerLogo()->toObject()->logoFile()->toFile()->alt()->or($site->title() . " Logo"),
						'source' => file_get_contents($site->headerLogo()->toObject()->logoFile()->toFile()->root()),
					];
				}

				// cta button
				$logoCta = [];
				if ($site->headerLogo()->toObject()->logoCta()->isNotEmpty()) {
					$logoCta = getLinkArray($site->headerLogo()->toObject()->logoCta());
				}




				//font files
				$font = [];
				foreach ($site->fontFile()->toStructure() as $fontItem) {
					$fontData1 = file_get_contents($fontItem->file1()->toFile()->root());
					$fontData2 = file_get_contents($fontItem->file2()->toFile()->root());

					$font[] = [
						"name" => (string)$fontItem->name(),
						"url1" => (string)$fontItem->file1()->toFile()->url(),
						"url2" => (string)$fontItem->file2()->toFile()->url(),
						"base64Data1" => base64_encode($fontData1),
						"base64Data2" => base64_encode($fontData2),
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
						"letterSpacingMobile" => (string)$fontSizeItem->letterSpacingMobile(),
						"sizeDesktop" => (string)$fontSizeItem->sizeDesktop(),
						"lineHeightDesktop" => (string)$fontSizeItem->lineHeightDesktop(),
						"letterSpacingDesktop" => (string)$fontSizeItem->letterSpacingDesktop(),
						"sizeDesktopXl" => (string)$fontSizeItem->sizeDesktopXl(),
						"lineHeightDesktopXl" => (string)$fontSizeItem->lineHeightDesktopXl(),
						"letterSpacingDesktopXl" => (string)$fontSizeItem->letterSpacingDesktopXl(),
						"transform" => (string)$fontSizeItem->transform() ?: 'none',
						"decoration" => (string)$fontSizeItem->decoration() ?: 'none',
					];
				}

				// headlines
				$headlines = [];

				for ($i = 1; $i <= 6; $i++) {
					$tag = "h$i";
					$fontKey = "{$tag}font";
					$sizeKey = "{$tag}size";

					$headline = [
						"font" => (string) $site->headlines()->toObject()->$fontKey(),
						"size" => (string) $site->headlines()->toObject()->$sizeKey(),
					];

					$headlines[$tag] = $headline;
				}

				// Analytics
				$searchConsoleCode = null;
				if ($site->searchConsoleToggle()->toBool(false)) {
					$searchConsoleCode = (string) $site->searchConsoleCode();
				}

				$googleAnalyticsCode = null;
				if ($site->googleAnalyticsToggle()->toBool(false)) {
					$googleAnalyticsCode = (string) $site->googleAnalyticsCode();
				}

				return response::json([
					"kirbyUrl" => (string) $kirby->url('index'),
					"siteUrl" => (string) $site->url(),
					'siteTitle' => (string) $site->title(),
					"defaultLang" => $defaultLang,
					"translations" => $translations,
					"prefixDefaultLocale" => option('prefixDefaultLocale'),
					"allLang" => $allLang,
					"favicon" => $favicon,

					"frontendUrl" => (string) $site->frontendUrl(),
					"navHeader" => $header,
					"navHamburger" => $hamburger,

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

					"headerActive" => $site->headerActive()->toBool(),
					"headerFont" => (string) $site->headerMenu()->toObject()->headerFont(),
					"headerFontSize" => (string) $site->headerMenu()->toObject()->headerFontSize(),
					"headerColor" => (string) $site->headerMenu()->toObject()->headerColor(),
					"headerColorActive" => (string) $site->headerMenu()->toObject()->headerColorActive(),
					"headerBackground" => (string) $site->headerMenu()->toObject()->headerBackground(),
					"headerBackgroundActive" => (string) $site->headerMenu()->toObject()->headerBackgroundActive(),

					"hamburgerFont" => (string) $site->headerHamburger()->toObject()->hamburgerFont(),
					"hamburgerFontSize" => (string) $site->headerHamburger()->toObject()->hamburgerFontSize(),
					"hamburgerFontColor" => (string) $site->headerHamburger()->toObject()->hamburgerFontColor(),
					"hamburgerMenuColor" => (string) $site->headerHamburger()->toObject()->hamburgerMenuColor(),
					"hamburgerMenuColorActive" => (string) $site->headerHamburger()->toObject()->hamburgerMenuColorActive(),
					"hamburgerOverlay" => (string) $site->headerHamburger()->toObject()->hamburgerOverlay(),

					"logoFile" => $logoFile,
					"logoAlign" => (string) $site->headerLogo()->toObject()->logoAlign(),
					"logoCta" => $logoCta,
					"logoDesktop" => (string) $site->headerLogo()->toObject()->logoDesktop(),
					"logoMobile" => (string) $site->headerLogo()->toObject()->logoMobile(),
					"logoDesktopActive" => (string) $site->headerLogo()->toObject()->logoDesktopActive(),
					"logoMobileActive" => (string) $site->headerLogo()->toObject()->logoMobileActive(),

					"gridGapMobile" => (string) $site->gridGapMobile(),
					"gridMarginMobile" => (string) $site->gridMarginMobile(),
					"gridGapDesktop" => (string) $site->gridGapDesktop(),
					"gridMarginDesktop" => (string) $site->gridMarginDesktop(),
					"gridBlockMobile" => (string) $site->gridBlockMobile(),
					"gridBlockDesktop" => (string) $site->gridBlockDesktop(),

					"buttonFont" => (string) $site->buttonSettings()->toObject()->buttonFont(),
					"buttonFontSize" => (string) $site->buttonSettings()->toObject()->buttonFontSize(),
					"buttonBorderRadius" => (string) $site->buttonSettings()->toObject()->buttonBorderRadius(),
					"buttonBorderWidth" => (string) $site->buttonSettings()->toObject()->buttonBorderWidth(),
					"buttonPadding" => (string) $site->buttonSettings()->toObject()->buttonPadding(),
					"buttonBackgroundColor" => (string) $site->buttonColors()->toObject()->buttonBackgroundColor(),
					"buttonBackgroundColorActive" => (string) $site->buttonColors()->toObject()->buttonBackgroundColorActive(),
					"buttonTextColor" => (string) $site->buttonColors()->toObject()->buttonTextColor(),
					"buttonTextColorActive" => (string) $site->buttonColors()->toObject()->buttonTextColorActive(),
					"buttonBorderColor"	=> (string) $site->buttonColors()->toObject()->buttonBorderColor(),
					"buttonBorderColorActive" => (string) $site->buttonColors()->toObject()->buttonBorderColorActive(),

					"paginationFont" => (string) $site->paginationSettings()->toObject()->paginationFont(),
					"paginationFontSize" => (string) $site->paginationSettings()->toObject()->paginationFontSize(),
					"paginationBorderRadius" => (string) $site->paginationSettings()->toObject()->paginationBorderRadius(),
					"paginationBorderWidth" => (string) $site->paginationSettings()->toObject()->paginationBorderWidth(),
					"paginationPadding" => (string) $site->paginationSettings()->toObject()->paginationPadding() ?: '10',
					"paginationMargin" => (string) $site->paginationSettings()->toObject()->paginationMargin() ?: '10',
					"paginationElements" => (string) $site->paginationSettings()->toObject()->paginationElements(),
					"paginationTop" => (string) $site->paginationSettings()->toObject()->paginationTop() ?: '16',
					"paginationBottom" => (string) $site->paginationSettings()->toObject()->paginationBottom() ?: '16',
					"paginationBackgroundColor" => (string) $site->paginationColors()->toObject()->paginationBackgroundColor(),
					"paginationBackgroundColorHover" => (string) $site->paginationColors()->toObject()->paginationBackgroundColorHover(),
					"paginationBackgroundColorActive" => (string) $site->paginationColors()->toObject()->paginationBackgroundColorActive(),
					"paginationTextColor" => (string) $site->paginationColors()->toObject()->paginationTextColor(),
					"paginationTextColorHover" => (string) $site->paginationColors()->toObject()->paginationTextColorHover(),
					"paginationTextColorActive" => (string) $site->paginationColors()->toObject()->paginationTextColorActive(),
					"paginationBorderColor"	=> (string) $site->paginationColors()->toObject()->paginationBorderColor(),
					"paginationBorderColorHover" => (string) $site->paginationColors()->toObject()->paginationBorderColorHover(),
					"paginationBorderColorActive" => (string) $site->paginationColors()->toObject()->paginationBorderColorActive(),

					"searchConsoleToggle" => $site->searchConsoleToggle()->toBool(false),
					"searchConsoleCode" => $searchConsoleCode,
					"googleAnalyticsToggle" => $site->googleAnalyticsToggle()->toBool(false),
					"googleAnalyticsCode" => $googleAnalyticsCode,
				]);
			}
		]

	],
];
