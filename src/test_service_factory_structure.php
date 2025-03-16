<?php
/**
 * Test script for ServiceFactory class structure
 * 
 * This file tests the structure of the ServiceFactory class 
 * without actually instantiating services.
 */

// Include ServiceFactory class
require_once __DIR__ . '/app/Services/ServiceFactory.php';

// Capture output in a string
ob_start();

echo "Testing ServiceFactory class structure...\n\n";

// Check if the class exists
echo "ServiceFactory class exists: " . (class_exists('App\Services\ServiceFactory') ? "YES" : "NO") . "\n";

// Check for required methods
$methods = [
    'getContainer',
    'createAuthService',
    'createDatabaseService',
    'createSearchService',
    'createStockDataService',
    'createPredictionScoringService'
];

echo "\nChecking required methods:\n";
foreach ($methods as $method) {
    echo "- Method $method exists: " . (method_exists('App\Services\ServiceFactory', $method) ? "YES" : "NO") . "\n";
}

// Check parameter counts for dependency injection support
echo "\nChecking method parameter counts:\n";
$reflection = new ReflectionClass('App\Services\ServiceFactory');

foreach ($methods as $method) {
    $reflectionMethod = $reflection->getMethod($method);
    $params = $reflectionMethod->getParameters();
    echo "- Method $method has " . count($params) . " parameter(s)\n";
}

// Output success message
echo "\nServiceFactory structure validation complete.\n";

// Get output and display it
$output = ob_get_clean();
echo $output;

// Determine if test passed
$passed = true;
if (!class_exists('App\Services\ServiceFactory')) {
    $passed = false;
}
foreach ($methods as $method) {
    if (!method_exists('App\Services\ServiceFactory', $method)) {
        $passed = false;
    }
}

// Final result
echo "\nTest result: " . ($passed ? "PASSED" : "FAILED") . "\n";