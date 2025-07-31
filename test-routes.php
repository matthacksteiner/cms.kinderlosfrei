<?php

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

echo "=== Routes Test ===\n\n";

// Check if Routes class exists
if (class_exists('\BaukastenApi\Api\Routes')) {
    echo "✓ Routes class exists\n";

    try {
        $routes = \BaukastenApi\Api\Routes::getRoutes();
        echo "✓ Routes::getRoutes() method works\n";
        echo "✓ Found " . count($routes) . " routes\n";

        foreach ($routes as $i => $route) {
            if (isset($route['pattern'])) {
                echo "  - Route $i: " . $route['pattern'] . "\n";
            }
        }
    } catch (Exception $e) {
        echo "✗ Error calling Routes::getRoutes(): " . $e->getMessage() . "\n";
    }
} else {
    echo "✗ Routes class not found\n";
}

// Check Kirby routes configuration
echo "\nKirby routes configuration:\n";
$kirbyRoutes = $kirby->option('routes');
if ($kirbyRoutes) {
    echo "✓ Kirby has routes configured\n";
    echo "✓ Type: " . gettype($kirbyRoutes) . "\n";
    if (is_array($kirbyRoutes)) {
        echo "✓ Routes count: " . count($kirbyRoutes) . "\n";
    }
} else {
    echo "✗ Kirby routes not configured\n";
}

echo "\n=== Test Complete ===\n";
