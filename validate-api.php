<?php

/**
 * API Validation Script
 * Tests API endpoints to ensure backward compatibility after refactoring
 */

require_once 'kirby/bootstrap.php';

$kirby = new Kirby([
    'roots' => [
        'index'   => __DIR__ . '/public',
        'base'    => __DIR__,
        'content' => __DIR__ . '/content',
        'site'    => __DIR__ . '/site',
        'storage' => __DIR__ . '/storage',
    ]
]);

echo "=== API Validation Test ===\n\n";

// Test 1: Index JSON endpoint
echo "Testing /index.json endpoint...\n";
try {
    $response = $kirby->call('index.json');
    if ($response && is_array($response)) {
        echo "✓ Index JSON endpoint returns array\n";
        echo "✓ Found " . count($response) . " pages in index\n";

        // Check structure of first page if exists
        if (!empty($response)) {
            $firstPage = $response[0];
            $requiredFields = ['id', 'title', 'uri', 'url'];
            foreach ($requiredFields as $field) {
                if (isset($firstPage[$field])) {
                    echo "✓ First page has '$field' field\n";
                } else {
                    echo "✗ First page missing '$field' field\n";
                }
            }
        }
    } else {
        echo "✗ Index JSON endpoint failed or returned invalid data\n";
    }
} catch (Exception $e) {
    echo "✗ Index JSON endpoint error: " . $e->getMessage() . "\n";
}

echo "\n";

// Test 2: Global JSON endpoint
echo "Testing /global.json endpoint...\n";
try {
    $response = $kirby->call('global.json');
    if ($response && is_array($response)) {
        echo "✓ Global JSON endpoint returns array\n";

        // Check for expected global fields
        $expectedFields = ['site', 'languages', 'navigation'];
        foreach ($expectedFields as $field) {
            if (isset($response[$field])) {
                echo "✓ Global data has '$field' field\n";
            } else {
                echo "✗ Global data missing '$field' field\n";
            }
        }
    } else {
        echo "✗ Global JSON endpoint failed or returned invalid data\n";
    }
} catch (Exception $e) {
    echo "✗ Global JSON endpoint error: " . $e->getMessage() . "\n";
}

echo "\n";

// Test 3: Helper functions availability
echo "Testing helper functions availability...\n";

$helperFunctions = [
    'getPageNavigation',
    'getNavigationSiblings',
    'getEffectiveParent',
    'generatePageUri',
    'getSectionToggleState',
    'getTranslations',
    'getAllLanguages',
    'getDefaultLanguage',
    'getFavicon',
    'getNavigation',
    'getLogoFile',
    'getFonts'
];

foreach ($helperFunctions as $function) {
    if (function_exists($function)) {
        echo "✓ Helper function '$function' is available\n";
    } else {
        echo "✗ Helper function '$function' is missing\n";
    }
}

echo "\n";

// Test 4: Plugin loading
echo "Testing plugin loading...\n";
$plugin = $kirby->plugin('baukasten/api');
if ($plugin) {
    echo "✓ Baukasten API plugin is loaded\n";
} else {
    echo "✗ Baukasten API plugin is not loaded\n";
}

echo "\n";

// Test 5: Routes configuration
echo "Testing routes configuration...\n";
$routes = $kirby->option('routes');
if ($routes && is_array($routes)) {
    echo "✓ Routes are configured\n";
    echo "✓ Found " . count($routes) . " routes\n";

    // Check for specific routes
    $expectedRoutes = ['index.json', 'global.json'];
    $foundRoutes = [];

    foreach ($routes as $route) {
        if (isset($route['pattern'])) {
            $foundRoutes[] = $route['pattern'];
        }
    }

    foreach ($expectedRoutes as $expectedRoute) {
        if (in_array($expectedRoute, $foundRoutes)) {
            echo "✓ Route '$expectedRoute' is configured\n";
        } else {
            echo "✗ Route '$expectedRoute' is missing\n";
        }
    }
} else {
    echo "✗ Routes are not properly configured\n";
}

echo "\n=== Validation Complete ===\n";
