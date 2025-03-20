<?php
/**
 * Test script to verify that ApiController properly delegates prediction operations to PredictionController
 */

// Include necessary files
require_once 'app/bootstrap.php';

use App\Controllers\ApiController;
use App\Controllers\PredictionController;

// Test function
function test_prediction_delegation() {
    echo "Testing ApiController delegation to PredictionController...\n";
    
    // Create ApiController instance
    $apiController = new ApiController();
    
    // Create a mock method to capture the call to apiHandler
    class TestPredictionController extends PredictionController {
        public static $called = false;
        
        public function apiHandler() {
            self::$called = true;
            echo "PredictionController::apiHandler was called successfully!\n";
            return ['success' => true, 'message' => 'Test passed'];
        }
    }
    
    // Replace the original PredictionController class with our test class
    class_alias('TestPredictionController', 'App\Controllers\PredictionController');
    
    // Call predictionOperations which should delegate to apiHandler
    $apiController->predictionOperations();
    
    // Verify the delegation occurred
    if (TestPredictionController::$called) {
        echo "TEST PASSED: ApiController successfully delegated to PredictionController\n";
    } else {
        echo "TEST FAILED: ApiController did not delegate to PredictionController\n";
    }
}

// Run the test
test_prediction_delegation();