<?php

use Kirby\Filesystem\Dir;
use Kirby\Filesystem\F;
use Kirby\Http\Response;

return [
	'debug' => true,
	'panel.install' => true,
	'date.handler' => 'strftime',
	'locale' => 'de_AT.utf-8',
	'languages' => false,
	'error' => 'z-error',
	'frontendUrl' => 'www.foo.com',
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
				$languages = [];
				foreach ($kirby->languages() as $language) {
					$languages[] = [
						"code" => $language->code(),
						"name" => $language->name(),
						"url" => $language->url(),
						"active" => $language->code() == $kirby->language()->code(),
					];
				}

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
						'source' => file_get_contents($site->headerLogo()->toEntity()->logoFile()->toFile()->root()),
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

				return response::json([
					'siteTitle' => (string) $site->title(),
					"favicon" => $favicon,
					"languages" => $languages,

					"navHeader" => $header,
					"navHamburger" => $hambuger,

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

				]);
			}
		]

	],

	'medienbaecker.autoresize.maxWidth' => 2560,
	'medienbaecker.autoresize.maxHeight' => 2560,
	'medienbaecker.autoresize.quality' => 99,
	'diesdasdigital.meta-knight' => [
		'siteTitleAsHomePageTitle' => true,
		'separator' => ' | ',
	],

];
