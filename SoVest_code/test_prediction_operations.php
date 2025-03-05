<?php
/**
 * Test for prediction_operations.php model validation implementation
 * Tests the key functionality of the updated create_prediction function
 */

// Clean output buffer for test results
ob_start();

// Mock a logged-in user
$_COOKIE["userID"] = 1;

// Set POST data with invalid values to trigger validation errors
$_POST = [
    'action' => 'create',
    'stock_id' => 999, // Non-existent stock ID
    'prediction_type' => 'Invalid', // Invalid prediction type
    'end_date' => '2022-01-01', // Past date
    'reasoning' => '' // Empty reasoning
];

// Include the API file
require_once 'api/prediction_operations.php';

// This should never be reached as respond_json calls exit
echo "ERROR: Test failed - respond_json didn't exit as expected";
?>