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

echo "=== DATA STRUCTURE DEBUG ===\n\n";

// Check index data structure
echo "INDEX DATA STRUCTURE:\n";
echo "---------------------\n";
$indexData = \BaukastenApi\Api\IndexApi::getData($kirby);
if (!empty($indexData)) {
    $firstPage = $indexData[0];
    echo "First page keys: " . implode(', ', array_keys($firstPage)) . "\n";
    echo "Sample data:\n";
    foreach ($firstPage as $key => $value) {
        if (is_string($value) || is_numeric($value)) {
            echo "  $key: " . substr($value, 0, 50) . "\n";
        } else {
            echo "  $key: " . gettype($value) . "\n";
        }
    }
}

echo "\n";

// Check global data structure
echo "GLOBAL DATA STRUCTURE:\n";
echo "----------------------\n";
$globalData = \BaukastenApi\Api\GlobalApi::getData($kirby);
echo "Global data keys: " . implode(', ', array_keys($globalData)) . "\n";
echo "Sample global data:\n";
$count = 0;
foreach ($globalData as $key => $value) {
    if ($count++ > 10) break; // Show only first 10 keys
    if (is_string($value) || is_numeric($value)) {
        echo "  $key: " . substr($value, 0, 50) . "\n";
    } else {
        echo "  $key: " . gettype($value) . "\n";
    }
}

echo "\n=== DEBUG COMPLETE ===\n";
