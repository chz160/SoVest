<?php
/**
 * Realistic Router Benchmark
 * 
 * This script simulates a more realistic scenario where the router is
 * initialized once and used multiple times during a request's lifetime.
 */

// Include bootstrap file for environment setup
require_once __DIR__ . '/bootstrap.php';

// Helper function to format duration
function formatDuration($ms) {
    if ($ms < 1) {
        return round($ms * 1000, 2) . ' μs';
    } elseif ($ms < 1000) {
        return round($ms, 2) . ' ms';
    } else {
        return round($ms / 1000, 2) . ' s';
    }
}

// Test routes
$testUris = [
    '/',
    '/login',
    '/predictions/view/123',
    '/admin/users/456',
    '/api/stocks/AAPL',
    '/nonexistent/path'
];

// Number of iterations 
$iterations = 1000;

// Test with standard Router (uncached)
echo "Testing with standard Router (uncached)...\n";
$routes = getAppRoutes();
$startTime = microtime(true);
$router = new \App\Routes\Router($routes, '', null, false);

for ($i = 0; $i < $iterations; $i++) {
    foreach ($testUris as $uri) {
        // Use reflection to access private findRoute method
        $reflectionClass = new \ReflectionClass($router);
        $method = $reflectionClass->getMethod('findRoute');
        $method->setAccessible(true);
        $routeInfo = $method->invoke($router, $uri);
    }
}

$standardTime = (microtime(true) - $startTime) * 1000;
$standardAvg = $standardTime / ($iterations * count($testUris));
echo "Total time: " . formatDuration($standardTime) . "\n";
echo "Average per route: " . formatDuration($standardAvg) . "\n";
echo "Routes per second: " . round(1000 / $standardAvg) . "\n\n";

// Test with Optimized Router (uncached)
echo "Testing with Optimized Router (uncached)...\n";
$startTime = microtime(true);
$router = new \App\Routes\OptimizedRouter($routes, '', null, false);

for ($i = 0; $i < $iterations; $i++) {
    foreach ($testUris as $uri) {
        // Use reflection to access private findRoute method
        $reflectionClass = new \ReflectionClass($router);
        $method = $reflectionClass->getMethod('findRoute');
        $method->setAccessible(true);
        $routeInfo = $method->invoke($router, $uri);
    }
}

$optimizedTime = (microtime(true) - $startTime) * 1000;
$optimizedAvg = $optimizedTime / ($iterations * count($testUris));
echo "Total time: " . formatDuration($optimizedTime) . "\n";
echo "Average per route: " . formatDuration($optimizedAvg) . "\n";
echo "Routes per second: " . round(1000 / $optimizedAvg) . "\n\n";

// Test with Optimized Router (cached)
echo "Testing with Optimized Router (cached)...\n";
$startTime = microtime(true);
$router = new \App\Routes\OptimizedRouter($routes, '', null, true);

for ($i = 0; $i < $iterations; $i++) {
    foreach ($testUris as $uri) {
        // Use reflection to access private findRoute method
        $reflectionClass = new \ReflectionClass($router);
        $method = $reflectionClass->getMethod('findRoute');
        $method->setAccessible(true);
        $routeInfo = $method->invoke($router, $uri);
    }
}

$optimizedCachedTime = (microtime(true) - $startTime) * 1000;
$optimizedCachedAvg = $optimizedCachedTime / ($iterations * count($testUris));
echo "Total time: " . formatDuration($optimizedCachedTime) . "\n";
echo "Average per route: " . formatDuration($optimizedCachedAvg) . "\n";
echo "Routes per second: " . round(1000 / $optimizedCachedAvg) . "\n\n";

// Show performance comparisons
echo "===== PERFORMANCE COMPARISON =====\n\n";

// Compare standard vs. optimized (uncached)
$improvementUncached = (($standardTime - $optimizedTime) / $standardTime) * 100;
echo "Optimized Router vs. Standard Router (both uncached):\n";
echo "Improvement: " . round($improvementUncached, 2) . "%\n";
if ($improvementUncached > 0) {
    echo "The Optimized Router is FASTER by " . formatDuration(abs($standardAvg - $optimizedAvg)) . " per route.\n\n";
} else {
    echo "The Optimized Router is SLOWER by " . formatDuration(abs($standardAvg - $optimizedAvg)) . " per route.\n\n";
}

// Compare standard uncached vs optimized cached
$improvementTotal = (($standardTime - $optimizedCachedTime) / $standardTime) * 100;
echo "Optimized Router (cached) vs. Standard Router (uncached):\n";
echo "Improvement: " . round($improvementTotal, 2) . "%\n";
if ($improvementTotal > 0) {
    echo "The Optimized Router with cache is FASTER by " . formatDuration(abs($standardAvg - $optimizedCachedAvg)) . " per route.\n\n";
} else {
    echo "The Optimized Router with cache is SLOWER by " . formatDuration(abs($standardAvg - $optimizedCachedAvg)) . " per route.\n\n";
}

// Compare optimized uncached vs optimized cached
$improvementCache = (($optimizedTime - $optimizedCachedTime) / $optimizedTime) * 100;
echo "Optimized Router (cached) vs. Optimized Router (uncached):\n";
echo "Improvement: " . round($improvementCache, 2) . "%\n";
if ($improvementCache > 0) {
    echo "The cache provides a " . round($improvementCache, 2) . "% speed improvement.\n";
} else {
    echo "The cache actually reduces performance by " . abs(round($improvementCache, 2)) . "%.\n";
}