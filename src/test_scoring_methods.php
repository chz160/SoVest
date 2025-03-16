<?php
/**
 * Test the refactored methods in PredictionScoringService
 */

// Define application path
define('APP_BASE_PATH', __DIR__);

// Define mock interfaces and traits needed for services and models
if (!interface_exists('App\\Services\\Interfaces\\StockDataServiceInterface')) {
    eval('namespace App\\Services\\Interfaces; interface StockDataServiceInterface {}');
}

if (!interface_exists('App\\Services\\Interfaces\\PredictionScoringServiceInterface')) {
    eval('namespace App\\Services\\Interfaces; interface PredictionScoringServiceInterface {}');
}

if (!interface_exists('App\\Services\\Interfaces\\DatabaseServiceInterface')) {
    eval('namespace App\\Services\\Interfaces; interface DatabaseServiceInterface {}');
}

if (!trait_exists('Database\\Models\\Traits\\ValidationTrait')) {
    eval('namespace Database\\Models\\Traits; trait ValidationTrait { public function validate() { return true; } }');
}

// Include necessary files
require_once __DIR__ . '/includes/db_config.php';
require_once __DIR__ . '/services/StockDataService.php';
require_once __DIR__ . '/services/PredictionScoringService.php';
require_once __DIR__ . '/app/Services/ServiceFactory.php';

// Create the StockDataService and PredictionScoringService directly
// due to issues with the ServiceFactory in the test environment
$stockDataService = new \Services\StockDataService();
$scoringService = new \Services\PredictionScoringService($stockDataService);

// Note: In production code, you would use:
// $scoringService = \App\Services\ServiceFactory::createPredictionScoringService();

echo "Created PredictionScoringService instance with dependency injection.\n\n";

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