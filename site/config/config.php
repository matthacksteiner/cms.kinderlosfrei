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
	'pju.webhook-field.hooks' => [
		'netlify_deploy' => [
			'url' => 'https://api.netlify.com/build_hooks/6489d44a8b3ccb038ca6a77d',
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

	],

	'medienbaecker.autoresize.maxWidth' => 2048,
	'medienbaecker.autoresize.maxHeight' => 2048,
	'medienbaecker.autoresize.quality' => 99,
	'diesdasdigital.meta-knight' => [
		'siteTitleAsHomePageTitle' => true,
		'separator' => ' | ',
	],

];
