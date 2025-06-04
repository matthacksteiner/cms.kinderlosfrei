<?php

/**
 * Baukasten Cache Manager Plugin
 *
 * This plugin provides cache management functionality for the Baukasten CMS.
 * It adds routes for cache status and manual cache clearing.
 */

use Kirby\Cms\App as Kirby;
use Kirby\Http\Response;

Kirby::plugin('baukasten/cache-manager', [
    'options' => [
        'route' => 'cache-status', // The URL path to access cache status
    ],
    'routes' => [
        [
            'pattern' => 'cache-status',
            'method'  => 'GET',
            'action'  => function () {
                return baukastenCacheStatus();
            }
        ],
        [
            'pattern' => 'cache-clear',
            'method'  => 'POST',
            'action'  => function () {
                if (kirby()->user() && kirby()->user()->isAdmin()) {
                    return baukastenCacheClear();
                }
                return Response::json(['error' => 'Unauthorized'], 401);
            }
        ],
        [
            'pattern' => 'cache-clear/(:any)',
            'method'  => 'POST',
            'action'  => function ($type = 'all') {
                if (kirby()->user() && kirby()->user()->isAdmin()) {
                    return baukastenCacheClear($type);
                }
                return Response::json(['error' => 'Unauthorized'], 401);
            }
        ],
    ],
]);

/**
 * Display cache status information
 */
function baukastenCacheStatus()
{
    $kirby = kirby();
    $apiCache = $kirby->cache('api');
    $pagesCache = $kirby->cache('pages');

    // Get cache statistics
    $cacheStats = [
        'api' => [
            'active' => option('cache.api.active', false),
            'type' => option('cache.api.type', 'file'),
            'root' => getCacheRoot($apiCache) ?? 'N/A',
            'size' => getCacheSize($apiCache),
            'keys' => getCacheKeys($apiCache)
        ],
        'pages' => [
            'active' => option('cache.pages.active', false),
            'type' => option('cache.pages.type', 'file'),
            'root' => getCacheRoot($pagesCache) ?? 'N/A',
            'size' => getCacheSize($pagesCache)
        ]
    ];

    $html = '<!DOCTYPE html>
    <html lang="en">
    <head>
        <meta charset="UTF-8">
        <meta name="viewport" content="width=device-width, initial-scale=1.0">
        <title>Baukasten Cache Status</title>
        <style>
            body {
                font-family: -apple-system, BlinkMacSystemFont, "Segoe UI", Roboto, Helvetica, Arial, sans-serif;
                line-height: 1.6;
                max-width: 1200px;
                margin: 0 auto;
                padding: 20px;
                color: #333;
            }
            h1, h2 {
                color: #2c3e50;
            }
            .cache-section {
                background: #f8f9fa;
                border: 1px solid #e9ecef;
                border-radius: 6px;
                padding: 20px;
                margin-bottom: 20px;
            }
            .status-active {
                color: #28a745;
                font-weight: bold;
            }
            .status-inactive {
                color: #dc3545;
                font-weight: bold;
            }
            .cache-keys {
                background: #fff;
                border: 1px solid #dee2e6;
                border-radius: 4px;
                padding: 10px;
                margin-top: 10px;
                max-height: 200px;
                overflow-y: auto;
            }
            .cache-key {
                font-family: monospace;
                padding: 2px 4px;
                background: #f8f9fa;
                border-radius: 3px;
                margin: 2px;
                display: inline-block;
            }
            .clear-button {
                background: #dc3545;
                color: white;
                border: none;
                padding: 8px 16px;
                border-radius: 4px;
                cursor: pointer;
                margin: 5px;
            }
            .clear-button:hover {
                background: #c82333;
            }
            code {
                background-color: #f5f5f5;
                padding: 2px 4px;
                border-radius: 3px;
                font-family: monospace;
            }
        </style>
    </head>
    <body>
        <h1>Baukasten Cache Status</h1>
        <p>Cache management for Baukasten CMS and Astro frontend integration.</p>';

    foreach ($cacheStats as $cacheName => $stats) {
        $statusClass = $stats['active'] ? 'status-active' : 'status-inactive';
        $statusText = $stats['active'] ? 'ACTIVE' : 'INACTIVE';

        $html .= '<div class="cache-section">
            <h2>' . ucfirst($cacheName) . ' Cache</h2>
            <p><strong>Status:</strong> <span class="' . $statusClass . '">' . $statusText . '</span></p>
            <p><strong>Type:</strong> ' . $stats['type'] . '</p>
            <p><strong>Root:</strong> <code>' . $stats['root'] . '</code></p>
            <p><strong>Size:</strong> ' . formatBytes($stats['size']) . '</p>';

        if ($cacheName === 'api' && !empty($stats['keys'])) {
            $html .= '<p><strong>Cached Keys:</strong></p>
                <div class="cache-keys">';
            foreach ($stats['keys'] as $key) {
                $html .= '<span class="cache-key">' . $key . '</span>';
            }
            $html .= '</div>';
        }

        $html .= '<button class="clear-button" onclick="clearCache(\'' . $cacheName . '\')">Clear ' . ucfirst($cacheName) . ' Cache</button>
        </div>';
    }

    $html .= '<div class="cache-section">
        <h2>Cache Management</h2>
        <button class="clear-button" onclick="clearCache(\'all\')">Clear All Caches</button>
        <p><em>Note: Cache will be automatically cleared when content is updated.</em></p>
    </div>

    <script>
        function clearCache(type) {
            if (confirm("Are you sure you want to clear the " + type + " cache?")) {
                fetch("/cache-clear/" + type, {
                    method: "POST",
                    headers: {
                        "Content-Type": "application/json",
                    }
                })
                .then(response => response.json())
                .then(data => {
                    alert(data.message || data.error);
                    location.reload();
                })
                .catch(error => {
                    alert("Error: " + error);
                });
            }
        }
    </script>
    </body>
    </html>';

    return new Response($html, 'text/html');
}

/**
 * Clear cache
 */
function baukastenCacheClear($type = 'all')
{
    $kirby = kirby();
    $cleared = [];

    try {
        if ($type === 'all' || $type === 'api') {
            $kirby->cache('api')->flush();
            $cleared[] = 'api';
        }

        if ($type === 'all' || $type === 'pages') {
            $kirby->cache('pages')->flush();
            $cleared[] = 'pages';
        }

        return Response::json([
            'success' => true,
            'message' => 'Cleared ' . implode(' and ', $cleared) . ' cache(s)',
            'cleared' => $cleared
        ]);
    } catch (Exception $e) {
        return Response::json([
            'success' => false,
            'error' => 'Failed to clear cache: ' . $e->getMessage()
        ], 500);
    }
}

/**
 * Get cache root directory safely
 */
function getCacheRoot($cache)
{
    try {
        // For file cache types, check if root method exists
        if (method_exists($cache, 'root')) {
            return $cache->root();
        }

        // Fallback for other cache types
        $cacheOptions = $cache->options();
        if (isset($cacheOptions['root'])) {
            return $cacheOptions['root'];
        }

        // Default fallback to storage/cache directory
        return kirby()->root('cache');
    } catch (Exception $e) {
        return null;
    }
}

/**
 * Get cache size in bytes
 */
function getCacheSize($cache)
{
    try {
        $root = getCacheRoot($cache);
        if (!$root || !is_dir($root)) {
            return 0;
        }

        $size = 0;
        $iterator = new RecursiveIteratorIterator(
            new RecursiveDirectoryIterator($root, RecursiveDirectoryIterator::SKIP_DOTS)
        );

        foreach ($iterator as $file) {
            $size += $file->getSize();
        }

        return $size;
    } catch (Exception $e) {
        return 0;
    }
}

/**
 * Get cache keys (for file cache)
 */
function getCacheKeys($cache)
{
    try {
        $root = getCacheRoot($cache);
        if (!$root || !is_dir($root)) {
            return [];
        }

        $keys = [];
        $iterator = new RecursiveIteratorIterator(
            new RecursiveDirectoryIterator($root, RecursiveDirectoryIterator::SKIP_DOTS)
        );

        foreach ($iterator as $file) {
            if ($file->isFile()) {
                $relativePath = str_replace($root . DIRECTORY_SEPARATOR, '', $file->getPathname());
                $keys[] = str_replace(DIRECTORY_SEPARATOR, '.', $relativePath);
            }
        }

        return $keys;
    } catch (Exception $e) {
        return [];
    }
}

/**
 * Format bytes to human readable format
 */
function formatBytes($bytes, $precision = 2)
{
    $units = array('B', 'KB', 'MB', 'GB', 'TB');

    for ($i = 0; $bytes > 1024; $i++) {
        $bytes /= 1024;
    }

    return round($bytes, $precision) . ' ' . $units[$i];
}
