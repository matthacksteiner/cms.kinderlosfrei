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

echo "=== Endpoint Test ===\n\n";

// Test by simulating HTTP requests
echo "Testing endpoints via router...\n";

// Test index.json
try {
    $request = new \Kirby\Http\Request([
        'url' => 'http://localhost/index.json',
        'method' => 'GET'
    ]);

    $kirby->setRequest($request);
    $response = $kirby->render();

    if ($response) {
        echo "✓ index.json endpoint responds\n";
        $content = $response->body();
        if (strpos($content, '[') === 0) {
            echo "✓ index.json returns JSON array\n";
        } else {
            echo "✗ index.json doesn't return JSON array\n";
            echo "Response: " . substr($content, 0, 100) . "...\n";
        }
    } else {
        echo "✗ index.json endpoint failed\n";
    }
} catch (Exception $e) {
    echo "✗ index.json error: " . $e->getMessage() . "\n";
}

echo "\n";

// Test global.json
try {
    $request = new \Kirby\Http\Request([
        'url' => 'http://localhost/global.json',
        'method' => 'GET'
    ]);

    $kirby->setRequest($request);
    $response = $kirby->render();

    if ($response) {
        echo "✓ global.json endpoint responds\n";
        $content = $response->body();
        if (strpos($content, '{') === 0) {
            echo "✓ global.json returns JSON object\n";
        } else {
            echo "✗ global.json doesn't return JSON object\n";
            echo "Response: " . substr($content, 0, 100) . "...\n";
        }
    } else {
        echo "✗ global.json endpoint failed\n";
    }
} catch (Exception $e) {
    echo "✗ global.json error: " . $e->getMessage() . "\n";
}

echo "\n=== Test Complete ===\n";
