<?php
/**
 * Test script for the refactored PredictionScoringService
 * 
 * This file tests the refactored methods to ensure they
 * maintain the same functionality as before.
 */

 /*
TODO: this test needs to be converted to a proper phpunit unit test and moved to correct folder under tests.
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

// Initialize database and autoloader first
require_once __DIR__ . '/vendor/autoload.php';
require_once __DIR__ . '/bootstrap/database.php';

// Import models
use Database\Models\User;
use Database\Models\Prediction;
use Database\Models\Stock;
use Database\Models\StockPrice;
use Illuminate\Database\Capsule\Manager as DB;

// Include necessary files
require_once __DIR__ . '/services/StockDataService.php';
require_once __DIR__ . '/services/PredictionScoringService.php';
require_once __DIR__ . '/app/Services/ServiceFactory.php';

// Create the StockDataService and PredictionScoringService directly
// due to issues with the ServiceFactory in the test environment
$stockDataService = new \App\Services\StockDataService();
$service = new \App\Services\PredictionScoringService($stockDataService);

// Note: In production code, you would use:
// $service = \App\Services\ServiceFactory::createPredictionScoringService();

echo "Created PredictionScoringService instance with dependency injection.\n\n";

// Test getTopUsers
echo "Testing getTopUsers method:\n";
$topUsers = $service->getTopUsers(5);
echo "Found " . count($topUsers) . " top users\n";
if (count($topUsers) > 0) {
    echo "First user details:\n";
    print_r($topUsers[0]);
}
echo "\n";

// Test getUserPredictionStats (assuming user ID 1 exists)
echo "Testing getUserPredictionStats method:\n";
$userId = 1; // Adjust if needed
$stats = $service->getUserPredictionStats($userId);
echo "Stats for user ID $userId:\n";
print_r($stats);

echo "\nTests completed.\n";
?>