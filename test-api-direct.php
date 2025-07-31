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

echo "=== Direct API Test ===\n\n";

// Test IndexApi directly
echo "Testing IndexApi directly...\n";
try {
    if (class_exists('\BaukastenApi\Api\IndexApi')) {
        echo "✓ IndexApi class exists\n";

        $data = \BaukastenApi\Api\IndexApi::getData($kirby);
        if (is_array($data)) {
            echo "✓ IndexApi::getData() returns array\n";
            echo "✓ Found " . count($data) . " pages\n";
        } else {
            echo "✗ IndexApi::getData() failed\n";
        }

        $response = \BaukastenApi\Api\IndexApi::handle($kirby);
        if ($response) {
            echo "✓ IndexApi::handle() returns response\n";
        } else {
            echo "✗ IndexApi::handle() failed\n";
        }
    } else {
        echo "✗ IndexApi class not found\n";
    }
} catch (Exception $e) {
    echo "✗ IndexApi error: " . $e->getMessage() . "\n";
}

echo "\n";

// Test GlobalApi directly
echo "Testing GlobalApi directly...\n";
try {
    if (class_exists('\BaukastenApi\Api\GlobalApi')) {
        echo "✓ GlobalApi class exists\n";

        $data = \BaukastenApi\Api\GlobalApi::getData($kirby);
        if (is_array($data)) {
            echo "✓ GlobalApi::getData() returns array\n";
            echo "✓ Global data has " . count($data) . " keys\n";
        } else {
            echo "✗ GlobalApi::getData() failed\n";
        }

        $response = \BaukastenApi\Api\GlobalApi::handle($kirby);
        if ($response) {
            echo "✓ GlobalApi::handle() returns response\n";
        } else {
            echo "✗ GlobalApi::handle() failed\n";
        }
    } else {
        echo "✗ GlobalApi class not found\n";
    }
} catch (Exception $e) {
    echo "✗ GlobalApi error: " . $e->getMessage() . "\n";
}

echo "\n=== Test Complete ===\n";
