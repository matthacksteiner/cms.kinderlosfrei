<?php

use Kirby\Cms\App as Kirby;
use Kirby\Cms\Page;
use FabianMichael\Meta\PageMeta;

Kirby::plugin('baukasten/sitemap', [
    'hooks' => [
<<<<<<< HEAD
        // Filter out pages with coverOnly set to true
        'meta.sitemap.url' => function (
            Page $page,
        ) {
            // Simply return false to exclude the page from the sitemap
            if ($page->intendedTemplate()->name() == 'item' && $page->coverOnly()->toBool(false)) {
                return false;
            }
=======
        // Filter out pages with coverOnly set to true and handle section toggle
        'meta.sitemap.url' => function (
            Page $page,
        ) {
            // Exclude pages with coverOnly set to true
            if ($page->intendedTemplate()->name() == 'item' && $page->coverOnly()->toBool(false)) {
                return false;
            }

            // Exclude section pages when designSectionToggle is disabled
            if ($page->intendedTemplate()->name() === 'section' && !getSectionToggleState()) {
                return false;
            }
>>>>>>> temp-template
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
<<<<<<< HEAD
=======
            $sectionToggleEnabled = getSectionToggleState();
>>>>>>> temp-template

            if ($frontendUrl) {
                foreach ($root->getElementsByTagName('url') as $url) {
                    foreach ($url->getElementsByTagName('loc') as $loc) {
<<<<<<< HEAD
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
=======
                        $originalUrl = $loc->nodeValue;

                        // Replace CMS URL with frontend URL
                        $transformedUrl = str_replace($cmsUrl, $frontendUrl, $originalUrl);

                        // Handle language prefix removal
                        if (count($allLanguages) === 1 || (option('prefixDefaultLocale') === false)) {
                            $transformedUrl = str_replace('/' . $defaultLanguage->code(), '', $transformedUrl);
                        }

                        // Handle flat URL structure when section toggle is disabled
                        if (!$sectionToggleEnabled) {
                            // Find the page by its original URL to generate the flat URI
                            $pageUri = str_replace($cmsUrl, '', $originalUrl);
                            $pageUri = ltrim($pageUri, '/');

                            // Remove language prefix from URI for page lookup
                            foreach ($allLanguages as $lang) {
                                if (strpos($pageUri, $lang->code() . '/') === 0) {
                                    $pageUri = substr($pageUri, strlen($lang->code()) + 1);
                                    break;
                                }
                            }

                            // Try to find the page and generate flat URI
                            $page = $kirby->page($pageUri);
                            if ($page) {
                                $flatUri = generatePageUri($page, true);

                                // Reconstruct the full URL with language prefix if needed
                                $languagePrefix = '';
                                foreach ($allLanguages as $lang) {
                                    if (strpos($originalUrl, '/' . $lang->code() . '/') !== false) {
                                        if (count($allLanguages) > 1 && (option('prefixDefaultLocale') === true || $lang->code() !== $defaultLanguage->code())) {
                                            $languagePrefix = '/' . $lang->code();
                                        }
                                        break;
                                    }
                                }

                                $transformedUrl = $frontendUrl . $languagePrefix . '/' . $flatUri;
                                $transformedUrl = rtrim($transformedUrl, '/');
                            }
                        }

                        $loc->nodeValue = $transformedUrl;
                    }

                    // Handle xhtml:link elements (alternate language links)
                    foreach ($url->getElementsByTagName('xhtml:link') as $xhtml) {
                        $originalHref = $xhtml->getAttribute('href');
                        $transformedHref = str_replace($cmsUrl, $frontendUrl, $originalHref);

                        // Handle language prefix removal
                        if (count($allLanguages) === 1 || (option('prefixDefaultLocale') === false)) {
                            $transformedHref = str_replace('/' . $defaultLanguage->code(), '', $transformedHref);
                        }

                        // Handle flat URL structure when section toggle is disabled
                        if (!$sectionToggleEnabled) {
                            $pageUri = str_replace($cmsUrl, '', $originalHref);
                            $pageUri = ltrim($pageUri, '/');

                            // Remove language prefix from URI for page lookup
                            foreach ($allLanguages as $lang) {
                                if (strpos($pageUri, $lang->code() . '/') === 0) {
                                    $pageUri = substr($pageUri, strlen($lang->code()) + 1);
                                    break;
                                }
                            }

                            // Try to find the page and generate flat URI
                            $page = $kirby->page($pageUri);
                            if ($page) {
                                $flatUri = generatePageUri($page, true);

                                // Reconstruct the full URL with language prefix if needed
                                $languagePrefix = '';
                                foreach ($allLanguages as $lang) {
                                    if (strpos($originalHref, '/' . $lang->code() . '/') !== false) {
                                        if (count($allLanguages) > 1 && (option('prefixDefaultLocale') === true || $lang->code() !== $defaultLanguage->code())) {
                                            $languagePrefix = '/' . $lang->code();
                                        }
                                        break;
                                    }
                                }

                                $transformedHref = $frontendUrl . $languagePrefix . '/' . $flatUri;
                                $transformedHref = rtrim($transformedHref, '/');
                            }
                        }

                        $xhtml->setAttribute('href', $transformedHref);
>>>>>>> temp-template
                    }
                }
            }
        }
    ]
]);
