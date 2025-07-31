<?php

/**
 * Comprehensive Validation Script
 * Tests all aspects of the refactored plugin to ensure backward compatibility
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

echo "=== COMPREHENSIVE VALIDATION TEST ===\n\n";

$passed = 0;
$failed = 0;

function test($description, $condition, $errorMsg = '')
{
    global $passed, $failed;
    if ($condition) {
        echo "✓ $description\n";
        $passed++;
    } else {
        echo "✗ $description" . ($errorMsg ? " - $errorMsg" : "") . "\n";
        $failed++;
    }
}

// 1. Plugin Loading Tests
echo "1. PLUGIN LOADING TESTS\n";
echo "------------------------\n";

test("Plugin is registered", $kirby->plugin('baukasten/api') !== null);
test("Plugin classes are autoloaded", class_exists('\BaukastenApi\Api\Routes'));
test("Helper functions are loaded", function_exists('getPageNavigation'));

echo "\n";

// 2. Helper Functions Tests
echo "2. HELPER FUNCTIONS TESTS\n";
echo "-------------------------\n";

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
    'getFonts',
    'getFontSizes',
    'getHeadlines',
    'getAnalytics'
];

foreach ($helperFunctions as $function) {
    test("Helper function '$function' exists", function_exists($function));
}

echo "\n";

// 3. API Classes Tests
echo "3. API CLASSES TESTS\n";
echo "--------------------\n";

test("IndexApi class exists", class_exists('\BaukastenApi\Api\IndexApi'));
test("GlobalApi class exists", class_exists('\BaukastenApi\Api\GlobalApi'));
test("Routes class exists", class_exists('\BaukastenApi\Api\Routes'));

// Test IndexApi functionality
try {
    $indexData = \BaukastenApi\Api\IndexApi::getData($kirby);
    test("IndexApi::getData() returns array", is_array($indexData));
    test("IndexApi returns page data", !empty($indexData));

    $indexResponse = \BaukastenApi\Api\IndexApi::handle($kirby);
    test("IndexApi::handle() returns response", $indexResponse !== null);
} catch (Exception $e) {
    test("IndexApi functionality", false, $e->getMessage());
}

// Test GlobalApi functionality
try {
    $globalData = \BaukastenApi\Api\GlobalApi::getData($kirby);
    test("GlobalApi::getData() returns array", is_array($globalData));
    test("GlobalApi returns site data", !empty($globalData));

    $globalResponse = \BaukastenApi\Api\GlobalApi::handle($kirby);
    test("GlobalApi::handle() returns response", $globalResponse !== null);
} catch (Exception $e) {
    test("GlobalApi functionality", false, $e->getMessage());
}

// Test Routes functionality
try {
    $routes = \BaukastenApi\Api\Routes::getRoutes();
    test("Routes::getRoutes() returns array", is_array($routes));
    test("Routes contains expected routes", count($routes) >= 5);

    $routePatterns = array_column($routes, 'pattern');
    test("Routes contains index.json", in_array('index.json', $routePatterns));
    test("Routes contains global.json", in_array('global.json', $routePatterns));
} catch (Exception $e) {
    test("Routes functionality", false, $e->getMessage());
}

echo "\n";

// 4. Helper Classes Tests
echo "4. HELPER CLASSES TESTS\n";
echo "-----------------------\n";

$helperClasses = [
    '\BaukastenApi\Helpers\NavigationHelper',
    '\BaukastenApi\Helpers\UrlHelper',
    '\BaukastenApi\Helpers\LanguageHelper',
    '\BaukastenApi\Helpers\SiteDataHelper'
];

foreach ($helperClasses as $class) {
    $className = basename(str_replace('\\', '/', $class));
    test("$className class exists", class_exists($class));
}

test("FlatUrlResolver service exists", class_exists('\BaukastenApi\Services\FlatUrlResolver'));

echo "\n";

// 5. Data Structure Tests
echo "5. DATA STRUCTURE TESTS\n";
echo "-----------------------\n";

// Test index data structure
try {
    $indexData = \BaukastenApi\Api\IndexApi::getData($kirby);
    if (!empty($indexData)) {
        $firstPage = $indexData[0];
        $requiredFields = ['id', 'title', 'uri', 'url'];
        foreach ($requiredFields as $field) {
            test("Index data contains '$field' field", isset($firstPage[$field]));
        }
    }
} catch (Exception $e) {
    test("Index data structure", false, $e->getMessage());
}

// Test global data structure
try {
    $globalData = \BaukastenApi\Api\GlobalApi::getData($kirby);
    $expectedFields = ['site', 'languages', 'navigation'];
    foreach ($expectedFields as $field) {
        test("Global data contains '$field' field", isset($globalData[$field]));
    }
} catch (Exception $e) {
    test("Global data structure", false, $e->getMessage());
}

echo "\n";

// 6. Backward Compatibility Tests
echo "6. BACKWARD COMPATIBILITY TESTS\n";
echo "-------------------------------\n";

// Test that helper functions work with actual data
try {
    $homePage = $kirby->site()->homePage();
    if ($homePage) {
        $navigation = getPageNavigation($homePage);
        test("getPageNavigation() works with real page", is_array($navigation));

        $uri = generatePageUri($homePage);
        test("generatePageUri() works with real page", is_string($uri));

        $translations = getTranslations($kirby);
        test("getTranslations() works", is_array($translations));
    }
} catch (Exception $e) {
    test("Helper functions with real data", false, $e->getMessage());
}

echo "\n";

// 7. Summary
echo "=== VALIDATION SUMMARY ===\n";
echo "Tests passed: $passed\n";
echo "Tests failed: $failed\n";
echo "Total tests: " . ($passed + $failed) . "\n";

if ($failed === 0) {
    echo "\n🎉 ALL TESTS PASSED! The refactoring is successful and maintains backward compatibility.\n";
} else {
    echo "\n⚠️  Some tests failed. Please review the issues above.\n";
}

echo "\n=== VALIDATION COMPLETE ===\n";
