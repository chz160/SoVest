<?php
/**
 * Test script for ServiceFactory
 * 
 * This file tests the functionality of the ServiceFactory class.
 */

 /*
TODO: this test needs to be converted to a proper phpunit unit test and moved to correct folder under tests.
*/

// This is a special test implementation of the interface to avoid dependency issues
namespace App\Services\Interfaces {
    interface AuthServiceInterface {}
}

namespace {
    // Define application path
    define('APP_BASE_PATH', __DIR__);

    // Include necessary files
    require_once __DIR__ . '/app/Services/ServiceFactory.php';
    require_once __DIR__ . '/services/AuthService.php';
    require_once __DIR__ . '/services/DatabaseService.php';
    require_once __DIR__ . '/services/SearchService.php';
    require_once __DIR__ . '/services/StockDataService.php';
    require_once __DIR__ . '/services/PredictionScoringService.php';

    // Test header
    echo "Testing ServiceFactory...\n\n";

    // Test creating AuthService
    echo "Testing createAuthService()...\n";
    $authService = \App\Services\ServiceFactory::createAuthService();
    echo "AuthService created: " . (($authService instanceof \Services\AuthService) ? "YES" : "NO") . "\n\n";

    // Test creating DatabaseService
    echo "Testing createDatabaseService()...\n";
    $dbService = \App\Services\ServiceFactory::createDatabaseService();
    echo "DatabaseService created: " . (($dbService instanceof \Services\DatabaseService) ? "YES" : "NO") . "\n\n";

    // Test creating SearchService
    echo "Testing createSearchService()...\n";
    $searchService = \App\Services\ServiceFactory::createSearchService();
    echo "SearchService created: " . (($searchService instanceof \Services\SearchService) ? "YES" : "NO") . "\n\n";

    // Test creating StockDataService
    echo "Testing createStockDataService()...\n";
    $stockService = \App\Services\ServiceFactory::createStockDataService();
    echo "StockDataService created: " . (($stockService instanceof \Services\StockDataService) ? "YES" : "NO") . "\n\n";

    // Test creating PredictionScoringService
    echo "Testing createPredictionScoringService()...\n";
    $scoringService = \App\Services\ServiceFactory::createPredictionScoringService();
    echo "PredictionScoringService created: " . (($scoringService instanceof \Services\PredictionScoringService) ? "YES" : "NO") . "\n\n";

    echo "ServiceFactory testing complete.\n";
}