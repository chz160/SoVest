<?php

/**
 * Test for AuthController with Dependency Injection
 * 
 * This script tests if the AuthController works correctly with dependency injection.
 */

// Define base path
define('APP_BASE_PATH', __DIR__);

// Require the autoloader
require_once __DIR__ . '/vendor/autoload.php';

// Explicitly require the AuthService
require_once __DIR__ . '/services/AuthService.php';

// Load application bootstrap for dependencies
require_once __DIR__ . '/app/bootstrap.php';

// Import the required classes
use App\Controllers\AuthController;
use Services\AuthService;
use App\Services\ServiceProvider;

echo "Testing AuthController with Dependency Injection\n";

try {
    // Test 1: Get controller through ServiceProvider (normal usage)
    echo "Test 1: Get controller through ServiceProvider... ";
    $controller = ServiceProvider::getController(AuthController::class);
    echo $controller instanceof AuthController ? "PASSED\n" : "FAILED\n";
    
    // Test 2: Check if AuthService is properly injected
    echo "Test 2: Check if AuthService is properly injected... ";
    $reflector = new ReflectionClass($controller);
    $authServiceProperty = $reflector->getProperty('authService');
    $authServiceProperty->setAccessible(true);
    $authService = $authServiceProperty->getValue($controller);
    echo $authService instanceof AuthService ? "PASSED\n" : "FAILED\n";
    
    // Test 3: Create controller directly with manual dependency
    echo "Test 3: Create controller directly with manual dependency... ";
    $manualAuthService = AuthService::getInstance();
    $directController = new AuthController($manualAuthService);
    $reflector = new ReflectionClass($directController);
    $authServiceProperty = $reflector->getProperty('authService');
    $authServiceProperty->setAccessible(true);
    $directAuthService = $authServiceProperty->getValue($directController);
    echo $directAuthService === $manualAuthService ? "PASSED\n" : "FAILED\n";
    
    // Test 4: Test backward compatibility (no dependencies provided)
    echo "Test 4: Test backward compatibility (no dependencies provided)... ";
    $backwardController = new AuthController();
    $reflector = new ReflectionClass($backwardController);
    $authServiceProperty = $reflector->getProperty('authService');
    $authServiceProperty->setAccessible(true);
    $backwardAuthService = $authServiceProperty->getValue($backwardController);
    echo $backwardAuthService instanceof AuthService ? "PASSED\n" : "FAILED\n";
    
    echo "\nAll tests completed!\n";
} catch (Exception $e) {
    echo "ERROR: " . $e->getMessage() . "\n";
    echo "Stack trace: " . $e->getTraceAsString() . "\n";
}