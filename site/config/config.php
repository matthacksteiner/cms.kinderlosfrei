<?php

use Kirby\Filesystem\Dir;
use Kirby\Filesystem\F;
use Kirby\Http\Response;

return [
	'debug' => true,
	'panel.install' => true,
	'date.handler' => 'strftime',
	'locale' => 'de_AT.utf-8',
	'error' => 'z-error',
	'frontendUrl' => 'www.foo.com',
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

	],

	'medienbaecker.autoresize.maxWidth' => 2048,
	'medienbaecker.autoresize.maxHeight' => 2048,
	'medienbaecker.autoresize.quality' => 99,
	'diesdasdigital.meta-knight' => [
		'siteTitleAsHomePageTitle' => true,
		'separator' => ' | ',
	],

];
