<?php

use Kirby\Cms\App as Kirby;
use Kirby\Cms\Page;
use FabianMichael\Meta\PageMeta;

Kirby::plugin('baukasten/sitemap', [
    'hooks' => [
        // Filter out pages with coverOnly set to true
        'meta.sitemap.url' => function (
            Page $page,
        ) {
            // Simply return false to exclude the page from the sitemap
            if ($page->intendedTemplate()->name() == 'item' && $page->coverOnly()->toBool(false)) {
                return false;
            }
        },

        // Handle URL transformations for remaining pages
        'meta.sitemap:after' => function (
            Kirby $kirby,
            DOMElement $root
        ) {
            $site = $kirby->site();
            $cmsUrl = $kirby->url('index');
            $frontendUrl = rtrim($site->frontendUrl(), '/');
            $allLanguages = $kirby->languages();
            $defaultLanguage = $kirby->defaultLanguage();

            if ($frontendUrl) {
                foreach ($root->getElementsByTagName('url') as $url) {
                    foreach ($url->getElementsByTagName('loc') as $loc) {
                        $loc->nodeValue = str_replace($cmsUrl, $frontendUrl, $loc->nodeValue);
                        if (count($allLanguages) === 1 || (option('prefixDefaultLocale') === false)) {
                            $loc->nodeValue = str_replace('/' . $defaultLanguage->code(), '', $loc->nodeValue);
                        }
                    }
                    foreach ($url->getElementsByTagName('xhtml:link') as $xhtml) {
                        $xhtml->setAttribute('href', str_replace($cmsUrl, $frontendUrl, $xhtml->getAttribute('href')));
                        if (count($allLanguages) === 1 || (option('prefixDefaultLocale') === false)) {
                            $xhtml->setAttribute('href', str_replace('/' . $defaultLanguage->code(), '', $xhtml->getAttribute('href')));
                        }
                    }
                }
            }
        }
    ]
]);
