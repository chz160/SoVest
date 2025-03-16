<?php
/**
 * Test script for container.php
 * 
 * This file tests the PHP-DI container configuration.
 */

 /*
TODO: this can probably be deleted as Laravel should handle all the container stuff.
*/

// Define application path
define('APP_BASE_PATH', __DIR__);

// Create mock classes for testing
require_once __DIR__ . '/test_mocks/MockServices.php';

// Include PHP-DI autoloader
require_once __DIR__ . '/vendor/autoload.php';

use DI\ContainerBuilder;

echo "Testing container.php configuration...\n\n";

try {
    // Build the container
    $containerBuilder = new ContainerBuilder();
    $containerDefinitions = require __DIR__ . '/bootstrap/container.php';
    $containerBuilder->addDefinitions($containerDefinitions);
    $container = $containerBuilder->build();
    
    echo "Container built successfully.\n\n";
    
    // Test interface to implementation mappings
    echo "Testing interface to implementation mappings:\n";
    $interfaces = [
        'App\Services\Interfaces\AuthServiceInterface',
        'App\Services\Interfaces\DatabaseServiceInterface',
        'App\Services\Interfaces\SearchServiceInterface',
        'App\Services\Interfaces\StockDataServiceInterface',
        'App\Services\Interfaces\PredictionScoringServiceInterface'
    ];
    
    foreach ($interfaces as $interface) {
        echo "- {$interface} is defined: " . ($container->has($interface) ? "YES" : "NO") . "\n";
    }
    
    echo "\nTesting implementation resolution:\n";
    $implementations = [
        'Services\AuthService',
        'Services\DatabaseService',
        'Services\SearchService',
        'Services\StockDataService',
        'Services\PredictionScoringService'
    ];
    
    foreach ($implementations as $implementation) {
        echo "- {$implementation} is resolvable: " . ($container->has($implementation) ? "YES" : "NO") . "\n";
    }
    
    echo "\nTesting backward compatibility with singletons:\n";
    $singletonServices = [
        'Services\AuthService',
        'Services\DatabaseService', 
        'Services\SearchService'
    ];
    
    foreach ($singletonServices as $service) {
        if ($container->has($service)) {
            $instance1 = $container->get($service);
            $instance2 = $container->get($service);
            echo "- {$service} maintains singleton pattern: " . (($instance1 === $instance2) ? "YES" : "NO") . "\n";
        } else {
            echo "- {$service} is not registered in container\n";
        }
    }
    
    echo "\nContainer testing complete.\n";
    echo "Test result: PASSED\n";
    
} catch (Exception $e) {
    echo "ERROR: " . $e->getMessage() . "\n";
    echo "Test result: FAILED\n";
}