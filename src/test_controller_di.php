<?php

/**
 * Test script for validating the enhanced Controller dependency injection
 */

 /*
TODO: this can probably be deleted as Laravel should handle all DI to controllers.
*/

// Required interfaces
namespace App\Services\Interfaces {
    if (!interface_exists('AuthServiceInterface')) {
        interface AuthServiceInterface {
            public function login($email, $password, $rememberMe = false);
            public function logout();
            public function register($userData);
            public function isAuthenticated();
            public function getCurrentUserId();
            public function getCurrentUser();
            public function requireAuthentication($redirect = null);
            public function verifyPassword($userId, $password);
            public function updateUserProfile($userId, $userData);
        }
    }
    
    if (!interface_exists('StockDataServiceInterface')) {
        interface StockDataServiceInterface {
            public function getStockPrice($symbol);
        }
    }
    
    if (!interface_exists('DatabaseServiceInterface')) {
        interface DatabaseServiceInterface {
            public function query($sql, $params = []);
        }
    }
}

namespace {
    // Include just the Controller file
    require_once __DIR__ . '/app/Controllers/Controller.php';
    
    // Import necessary classes
    use App\Controllers\Controller;
    use App\Services\Interfaces\AuthServiceInterface;
    use App\Services\Interfaces\StockDataServiceInterface;
    use App\Services\Interfaces\DatabaseServiceInterface;
    
    // Create mock implementations of the service interfaces
    class MockAuthService implements AuthServiceInterface {
        public function login($email, $password, $rememberMe = false) { return ['id' => 1, 'email' => $email]; }
        public function logout() { return true; }
        public function register($userData) { return 1; }
        public function isAuthenticated() { return true; }
        public function getCurrentUserId() { return 1; }
        public function getCurrentUser() { return ['id' => 1, 'name' => 'Test User']; }
        public function requireAuthentication($redirect = null) { return true; }
        public function verifyPassword($userId, $password) { return true; }
        public function updateUserProfile($userId, $userData) { return true; }
    }
    
    class MockStockDataService implements StockDataServiceInterface {
        public function getStockPrice($symbol) { return ['price' => 100.00]; }
    }
    
    class MockDatabaseService implements DatabaseServiceInterface {
        public function query($sql, $params = []) { return []; }
    }
    
    // Create a test controller extending the base Controller
    class TestController extends Controller {
        public function testMethod() {
            return [
                'authUser' => $this->getAuthUser(),
                'stockService' => isset($this->stockDataService) ? get_class($this->stockDataService) : null,
                'databaseService' => isset($this->databaseService) ? get_class($this->databaseService) : null,
                'services' => $this->services
            ];
        }
        
        // Expose the getService method for testing
        public function getServicePublic($className) {
            return $this->getService($className);
        }
    }
    
    // Test 1: Controller with named services
    echo "Test 1: Controller with named services\n";
    $authService = new MockAuthService();
    $stockService = new MockStockDataService();
    $controller1 = new TestController($authService, [
        'stockDataService' => $stockService
    ]);
    $result1 = $controller1->testMethod();
    echo "Auth User: " . json_encode($result1['authUser']) . "\n";
    echo "Stock Service: " . $result1['stockService'] . "\n";
    echo "Database Service: " . $result1['databaseService'] . "\n\n";
    
    // Test 2: Controller with unnamed services
    echo "Test 2: Controller with unnamed services\n";
    $authService = new MockAuthService();
    $stockService = new MockStockDataService();
    $dbService = new MockDatabaseService();
    $controller2 = new TestController($authService, [
        $stockService,
        $dbService
    ]);
    $result2 = $controller2->testMethod();
    echo "Auth User: " . json_encode($result2['authUser']) . "\n";
    echo "Stock Service: " . $result2['stockService'] . "\n";
    echo "Database Service: " . $result2['databaseService'] . "\n\n";
    
    // Test 3: Getting service by interface
    echo "Test 3: Getting service by interface\n";
    $authService = new MockAuthService();
    $stockService = new MockStockDataService();
    $dbService = new MockDatabaseService();
    $controller3 = new TestController($authService, [
        $stockService,
        $dbService
    ]);
    $retrievedStockService = $controller3->getServicePublic(StockDataServiceInterface::class);
    $retrievedDbService = $controller3->getServicePublic(DatabaseServiceInterface::class);
    echo "Retrieved Stock Service: " . ($retrievedStockService ? get_class($retrievedStockService) : "null") . "\n";
    echo "Retrieved DB Service: " . ($retrievedDbService ? get_class($retrievedDbService) : "null") . "\n\n";
    
    echo "\nAll tests completed.\n";
}