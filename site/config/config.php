<?php

use Kirby\Filesystem\Dir;
use Kirby\Filesystem\F;
use Kirby\Http\Response;

return [
	'debug' => true,
	'date.handler' => 'strftime',
	'locale' => 'de_AT.utf-8',
	'error' => 'z-error',
	'frontendUrl' => 'www.foo.com',
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
		]
	],
	'medienbaecker.autoresize.maxWidth' => 3600,
	'medienbaecker.autoresize.maxHeight' => 3600,
	'medienbaecker.autoresize.quality' => 99,
	'f-mahler.kirby-vercel.deployurl' => 'https://api.vercel.com/v1/integrations/deploy/prj_1pcYb8rWqv2XlqTUd5VJQkKVAGME/4hS6ckdcac',
	'f-mahler.kirby-vercel.hooks' => [],
	'bnomei.janitor.jobs' => [
		'downloadBackup' => function (Kirby\Cms\Page $page = null, string $data = null) {
			$dir = realpath(kirby()->roots()->accounts() . '/../') . '/backups';

			$files = Dir::files($dir, null, true);
			$fileDownload = end($files);

			foreach ($files as $f) {
				if ($f != $fileDownload) {
					F::remove($f);
				}
			}

			return [
				'status' => 200,
				'download' => F::uri($fileDownload),
			];
		},
	]
];
