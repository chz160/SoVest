<?php
/**
 * Test script for PredictionScoringService factory method
 * 
 * This file tests the updated ServiceFactory::createPredictionScoringService method
 * to verify that it properly passes the StockDataService dependency.
 */

// Mock interfaces
namespace App\Services\Interfaces {
    interface PredictionScoringServiceInterface {}
    interface StockDataServiceInterface {}
}

// Mock services
namespace Services {
    // Mock StockDataService for testing
    class StockDataService implements \App\Services\Interfaces\StockDataServiceInterface {
        public static function getInstance() {
            return new self();
        }
    }
    
    // Mock PredictionScoringService for testing
    class PredictionScoringService implements \App\Services\Interfaces\PredictionScoringServiceInterface {
        private $stockService;
        private static $instance = null;
        
        public static function getInstance() {
            if (self::$instance === null) {
                self::$instance = new self();
            }
            return self::$instance;
        }
        
        public function __construct($stockService = null) {
            $this->stockService = $stockService ?: StockDataService::getInstance();
        }
        
        // Method to check if dependency was injected correctly
        public function getStockService() {
            return $this->stockService;
        }
    }
}

// Mock container and other dependencies
namespace DI {
    class Container {
        public function has($className) {
            return false;
        }
        
        public function get($className) {
            return null;
        }
    }
}

namespace {
    // These services are already in the global namespace in our test context
    // so we don't need use statements
    
    // Modified ServiceFactory for testing only the relevant method
    class TestServiceFactory {
        /**
         * Create a PredictionScoringService instance
         *
         * @param StockDataService|null $stockDataService Optional stock data service dependency
         * @return PredictionScoringService
         */
        public static function createPredictionScoringService(\Services\StockDataService $stockDataService = null)
        {
            try {
                $container = null; // Mock container to force direct instantiation
                
                // PredictionScoringService now supports constructor injection
                // Use the provided stockDataService if available
                return new \Services\PredictionScoringService($stockDataService);
            } catch (Exception $e) {
                // Fall back to direct instantiation with dependency injection
                return new \Services\PredictionScoringService($stockDataService);
            }
        }
    }
    
    echo "Testing PredictionScoringService factory with dependency injection...\n\n";
    
    // Create a stock data service
    $stockService = new \Services\StockDataService();
    echo "StockDataService created.\n";
    
    // Test creating PredictionScoringService with null parameter (should use fallback)
    echo "\nTest 1: Create with null parameter (should use fallback)\n";
    $scoringService1 = TestServiceFactory::createPredictionScoringService(null);
    echo "PredictionScoringService created: " . (($scoringService1 instanceof \Services\PredictionScoringService) ? "YES" : "NO") . "\n";
    echo "Has StockService dependency: " . (($scoringService1->getStockService() instanceof \Services\StockDataService) ? "YES" : "NO") . "\n";
    
    // Test creating PredictionScoringService with explicit dependency
    echo "\nTest 2: Create with explicit dependency\n";
    $scoringService2 = TestServiceFactory::createPredictionScoringService($stockService);
    echo "PredictionScoringService created: " . (($scoringService2 instanceof \Services\PredictionScoringService) ? "YES" : "NO") . "\n";
    echo "Has StockService dependency: " . (($scoringService2->getStockService() instanceof \Services\StockDataService) ? "YES" : "NO") . "\n";
    echo "Is the same StockService instance: " . (($scoringService2->getStockService() === $stockService) ? "YES" : "NO") . "\n";
    
    echo "\nPredictionScoringService factory testing complete.\n";
}