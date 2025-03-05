<?php
/**
 * Test the refactored methods in PredictionScoringService
 */

// Include necessary files
require_once __DIR__ . '/includes/db_config.php';
require_once __DIR__ . '/services/PredictionScoringService.php';

// Create an instance of the service
$scoringService = new PredictionScoringService();

// Test output function
function outputTestResult($name, $success, $details = null) {
    echo "Test: $name - " . ($success ? "PASSED" : "FAILED") . "\n";
    if ($details) {
        echo "Details: $details\n";
    }
    echo "-------------------\n";
}

// Test 1: getTopUsers method
try {
    $topUsers = $scoringService->getTopUsers(3);
    $testPassed = is_array($topUsers);
    outputTestResult("getTopUsers", $testPassed, "Retrieved " . count($topUsers) . " users");
    
    if ($testPassed && count($topUsers) > 0) {
        echo "First user data (sample):\n";
        print_r($topUsers[0]);
        echo "\n";
    }
} catch (Exception $e) {
    outputTestResult("getTopUsers", false, "Error: " . $e->getMessage());
}

// Test 2: getUserPredictionStats method
try {
    // Use user ID 1 for testing
    $userId = 1;
    $stats = $scoringService->getUserPredictionStats($userId);
    $testPassed = is_array($stats) && isset($stats['total']) && isset($stats['accurate']) && 
                 isset($stats['inaccurate']) && isset($stats['pending']) && isset($stats['avg_accuracy']);
    
    outputTestResult("getUserPredictionStats", $testPassed, "Stats for user ID $userId");
    
    if ($testPassed) {
        echo "User prediction stats:\n";
        print_r($stats);
        echo "\n";
    }
} catch (Exception $e) {
    outputTestResult("getUserPredictionStats", false, "Error: " . $e->getMessage());
}

echo "Testing complete.\n";
?>