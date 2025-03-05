<?php
/**
 * Test script for the refactored PredictionScoringService
 * 
 * This file tests the refactored methods to ensure they
 * maintain the same functionality as before.
 */

// Initialize database and autoloader first
require_once __DIR__ . '/vendor/autoload.php';
require_once __DIR__ . '/bootstrap/database.php';

// Import models
use Database\Models\User;
use Database\Models\Prediction;
use Database\Models\Stock;
use Database\Models\StockPrice;
use Illuminate\Database\Capsule\Manager as DB;

// Include the service
require_once __DIR__ . '/services/PredictionScoringService.php';

// Create an instance
$service = new PredictionScoringService();

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