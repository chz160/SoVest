<?php
/**
 * SoVest - Service Factory
 *
 * This class provides a standardized way to create service instances with
 * dependency injection while maintaining backward compatibility with
 * existing code. It serves as a bridge during the transition to fully
 * interface-based dependency injection.
 */

namespace App\Services;

use DI\Container;
use Exception;
use App\Services\AuthService;
use App\Services\DatabaseService;
use App\Services\SearchService;
use App\Services\StockDataService;
use App\Services\PredictionScoringService;
use App\Handlers\ErrorHandler;
use App\Handlers\Interfaces\ErrorHandlerInterface;

class ServiceFactory
{
    /**
     * @var Container|null The dependency injection container instance
     */
    private static $container = null;
    
    /**
     * Get the DI container
     *
     * @return Container|null
     */
    public static function getContainer()
    {
        if (self::$container === null) {
            // Try to load the container from the bootstrap
            try {
                $containerPath = __DIR__ . '/../../bootstrap/container.php';
                if (file_exists($containerPath)) {
                    self::$container = require $containerPath;
                }
            } catch (Exception $e) {
                error_log("Error loading container: " . $e->getMessage());
                self::$container = null;
            }
        }
        
        return self::$container;
    }
    
    /**
     * Create an AuthService instance
     *
     * @return AuthService
     */
    public static function createAuthService()
    {
        try {
            $container = self::getContainer();
            
            // If container exists and has AuthService registered, use it
            if ($container) {
                if (is_object($container) && method_exists($container, 'has') && $container->has(AuthService::class)) {
                    return $container->get(AuthService::class);
                } elseif (is_array($container) && isset($container[AuthService::class])) {
                    return $container[AuthService::class];
                }
            }
            
            // Fall back to singleton pattern
            return AuthService::getInstance();
        } catch (Exception $e) {
            error_log("Error creating AuthService: " . $e->getMessage());
            // Fall back to singleton pattern
            return AuthService::getInstance();
        }
    }
    
    /**
     * Create a DatabaseService instance
     *
     * @return DatabaseService
     */
    public static function createDatabaseService()
    {
        try {
            $container = self::getContainer();
            
            // If container exists and has DatabaseService registered, use it
            if ($container) {
                if (is_object($container) && method_exists($container, 'has') && $container->has(DatabaseService::class)) {
                    return $container->get(DatabaseService::class);
                } elseif (is_array($container) && isset($container[DatabaseService::class])) {
                    return $container[DatabaseService::class];
                }
            }
            
            // Fall back to singleton pattern
            return DatabaseService::getInstance();
        } catch (Exception $e) {
            error_log("Error creating DatabaseService: " . $e->getMessage());
            // Fall back to singleton pattern
            return DatabaseService::getInstance();
        }
    }
    
    /**
     * Create a SearchService instance
     *
     * @param DatabaseService|null $dbService Optional database service dependency
     * @param AuthService|null $authService Optional authentication service dependency
     * @return SearchService
     */
    public static function createSearchService(DatabaseService $dbService = null, AuthService $authService = null)
    {
        try {
            $container = self::getContainer();
            
            // If container exists and has SearchService registered, use it
            if ($container) {
                if (is_object($container) && method_exists($container, 'has') && $container->has(SearchService::class)) {
                    return $container->get(SearchService::class);
                } elseif (is_array($container) && isset($container[SearchService::class])) {
                    return $container[SearchService::class];
                }
            }
            
            // Future enhancement: When SearchService supports constructor injection,
            // we would use the provided dependencies here.
            // For now, fall back to singleton pattern
            return SearchService::getInstance();
        } catch (Exception $e) {
            error_log("Error creating SearchService: " . $e->getMessage());
            // Fall back to singleton pattern
            return SearchService::getInstance();
        }
    }
    
    /**
     * Create a StockDataService instance
     *
     * @return StockDataService
     */
    public static function createStockDataService()
    {
        try {
            $container = self::getContainer();
            
            // If container exists and has StockDataService registered, use it
            if ($container) {
                if (is_object($container) && method_exists($container, 'has') && $container->has(StockDataService::class)) {
                    return $container->get(StockDataService::class);
                } elseif (is_array($container) && isset($container[StockDataService::class])) {
                    return $container[StockDataService::class];
                }
            }
            
            // Fall back to direct instantiation
            return new StockDataService();
        } catch (Exception $e) {
            error_log("Error creating StockDataService: " . $e->getMessage());
            // Fall back to direct instantiation
            return new StockDataService();
        }
    }
    
    /**
     * Create a PredictionScoringService instance
     *
     * @param StockDataService|null $stockDataService Optional stock data service dependency
     * @return PredictionScoringService
     */
    public static function createPredictionScoringService(StockDataService $stockDataService = null)
    {
        try {
            $container = self::getContainer();
            
            // If container exists and has PredictionScoringService registered, use it
            if ($container) {
                if (is_object($container) && method_exists($container, 'has') && $container->has(PredictionScoringService::class)) {
                    return $container->get(PredictionScoringService::class);
                } elseif (is_array($container) && isset($container[PredictionScoringService::class])) {
                    return $container[PredictionScoringService::class];
                }
            }
            
            // PredictionScoringService now supports constructor injection
            // Use the provided stockDataService if available
            return new PredictionScoringService($stockDataService);
        } catch (Exception $e) {
            error_log("Error creating PredictionScoringService: " . $e->getMessage());
            // Fall back to direct instantiation with dependency injection
            return new PredictionScoringService($stockDataService);
        }
    }
    
    /**
     * Create an ErrorHandler instance
     *
     * @param \App\Controllers\ErrorController|null $errorController Optional error controller dependency
     * @return ErrorHandlerInterface
     */
    public static function createErrorHandler($errorController = null)
    {
        try {
            $container = self::getContainer();
            
            // If container exists and has ErrorHandler registered, use it
            if ($container) {
                if (is_object($container) && method_exists($container, 'has') && $container->has(ErrorHandlerInterface::class)) {
                    return $container->get(ErrorHandlerInterface::class);
                } elseif (is_array($container) && isset($container[ErrorHandlerInterface::class])) {
                    return $container[ErrorHandlerInterface::class];
                }
            }
            
            // Use the concrete implementation with constructor injection if available
            if (class_exists(ErrorHandler::class)) {
                // Support dependency injection
                return new ErrorHandler($errorController);
            }
            
            // Fall back to singleton pattern for backward compatibility
            if (method_exists(ErrorHandler::class, 'getInstance')) {
                return ErrorHandler::getInstance();
            }
            
            // If all else fails, log an error
            error_log("No ErrorHandler implementation found");
            return null;
        } catch (Exception $e) {
            error_log("Error creating ErrorHandler: " . $e->getMessage());
            
            // Fall back to singleton pattern for backward compatibility
            if (class_exists(ErrorHandler::class) && method_exists(ErrorHandler::class, 'getInstance')) {
                return ErrorHandler::getInstance();
            }
            
            // If all else fails, log an error
            error_log("No ErrorHandler implementation found");
            return null;
        }
    }
}