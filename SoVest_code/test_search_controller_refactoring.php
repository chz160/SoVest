<?php
/**
 * Test script for the refactored SearchController
 * 
 * This script tests the basic functionality of the refactored SearchController class.
 */

// Include the test autoloader
require_once __DIR__ . '/test_autoload.php';

// Ensure dependencies are loaded
require_once __DIR__ . '/services/DatabaseService.php';
require_once __DIR__ . '/services/AuthService.php';
require_once __DIR__ . '/services/SearchService.php';

// Mock the required objects
class MockRequest {
    public $params = [];
    
    public function getParam($key, $default = null) {
        return $this->params[$key] ?? $default;
    }
    
    public function setParam($key, $value) {
        $this->params[$key] = $value;
    }
}

class MockResponse {
    public $statusCode = 200;
    public $headers = [];
    public $body;
    
    public function setStatusCode($code) {
        $this->statusCode = $code;
        return $this;
    }
    
    public function setHeader($name, $value) {
        $this->headers[$name] = $value;
        return $this;
    }
    
    public function setBody($body) {
        $this->body = $body;
        return $this;
    }
}

// Create a test environment
$_COOKIE["userID"] = 1; // Mock a logged-in user
$_SERVER['HTTP_X_REQUESTED_WITH'] = 'xmlhttprequest'; // Mock AJAX request

// Create a controller instance
require_once __DIR__ . '/app/Controllers/Controller.php';
require_once __DIR__ . '/app/Controllers/SearchController.php';
$controller = new \App\Controllers\SearchController();

echo "==== Testing Refactored SearchController ====\n\n";

// Test suggestions method with Ajax request
try {
    echo "Testing suggestions method...\n";
    $_GET['query'] = 'A';
    $_GET['type'] = 'stocks';
    
    // Call the suggestions method
    ob_start();
    $result = $controller->suggestions();
    $output = ob_get_clean();
    
    // Check the output
    $data = json_decode($output, true);
    if (!$data || !isset($data['suggestions'])) {
        echo "ERROR: Invalid JSON response\n";
    } else {
        echo "SUCCESS: Got " . count($data['suggestions']) . " suggestions\n";
    }
} catch (Exception $e) {
    echo "ERROR: " . $e->getMessage() . "\n";
}

// Test performSearch via index method (this might not work depending on environment)
try {
    echo "\nTesting index method for search...\n";
    $_GET['query'] = 'AAPL';
    $_GET['type'] = 'stocks';
    
    // Call the index method
    ob_start();
    $result = $controller->index();
    $output = ob_get_clean();
    
    // Check the output
    $data = json_decode($output, true);
    if (!$data || !isset($data['results'])) {
        echo "ERROR: Invalid JSON response\n";
    } else {
        echo "SUCCESS: Got " . count($data['results']) . " search results\n";
    }
} catch (Exception $e) {
    echo "ERROR: " . $e->getMessage() . "\n";
}

echo "\n==== SearchController Refactoring Test Complete ====\n";