<?php

use Kirby\Toolkit\Str;
use Kirby\Cms\App as Kirby;

Kirby::plugin('baukasten/sitemap', [
    'hooks' => [
        'meta.sitemap:after' => function (
            Kirby $kirby,
            DOMElement $root,
        ) {
            $site = $kirby->site();
            $cmsUrl = $kirby->url('index');
            $frontendUrl = rtrim($site->frontendUrl(), '/');

            if ($frontendUrl) {
                foreach ($root->getElementsByTagName('url') as $url) {
                    foreach ($url->getElementsByTagName('loc') as $loc) {
                        $loc->nodeValue = Str::replace($loc->nodeValue, $cmsUrl, $frontendUrl);
                    }
                    foreach ($url->getElementsByTagName('xhtml:link') as $xhtml) {
                        $xhtml->setAttribute('href', Str::replace($xhtml->getAttribute('href'), $cmsUrl, $frontendUrl));
                    }
                }
            }
        },
    ]
]);
