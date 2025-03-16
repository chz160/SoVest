<?php
/**
 * SoVest Dependency Injection Container Configuration
 * 
 * This file defines the service bindings for the PHP-DI container.
 * It registers service interfaces to their concrete implementations,
 * supports constructor injection, and maintains backward compatibility.
 */

// Use statements for services and interfaces
use App\Services\AuthService;
use App\Services\DatabaseService;
use App\Services\SearchService;
use App\Services\StockDataService;
use App\Services\PredictionScoringService;
use App\Services\ValidationService;
use App\Services\ResponseFormatter;
use App\Services\Interfaces\AuthServiceInterface;
use App\Services\Interfaces\DatabaseServiceInterface;
use App\Services\Interfaces\SearchServiceInterface;
use App\Services\Interfaces\StockDataServiceInterface;
use App\Services\Interfaces\PredictionScoringServiceInterface;
use App\Services\Interfaces\ValidationServiceInterface;
use App\Services\Interfaces\ResponseFormatterInterface;
use App\Handlers\ErrorHandler;
use App\Handlers\Interfaces\ErrorHandlerInterface;
use App\Controllers\ErrorController;
use Database\Models\StockService;
use Database\DatabaseConnection;

// Middleware classes
use App\Middleware\MiddlewareRegistry;
use App\Middleware\AuthMiddleware;
use App\Middleware\CSRFMiddleware;
use App\Middleware\RateLimitMiddleware;
use App\Middleware\CORSMiddleware;
use App\Middleware\LoggingMiddleware;
use App\Middleware\MaintenanceMiddleware;

use function DI\factory;
use function DI\get;
use function DI\create;
use function DI\autowire;
use function DI\value;

return [
    //========================================================================
    // Middleware Registry and Middleware Classes
    //========================================================================
    
    // Middleware Registry
    MiddlewareRegistry::class => factory(function($container) {
        $registry = new MiddlewareRegistry();
        
        // Register middleware with aliases
        $registry->register('auth', $container->get(AuthMiddleware::class));
        $registry->register('csrf', $container->get(CSRFMiddleware::class));
        $registry->register('rate_limit', $container->get(RateLimitMiddleware::class));
        $registry->register('cors', $container->get(CORSMiddleware::class));
        $registry->register('logging', $container->get(LoggingMiddleware::class));
        $registry->register('maintenance', $container->get(MaintenanceMiddleware::class));
        
        // Register global middleware (middleware that runs on every request)
        $registry->registerGlobal('logging');     // Log all requests
        $registry->registerGlobal('cors');        // Handle CORS for all requests
        
        return $registry;
    }),
    
    // Middleware Classes
    AuthMiddleware::class => factory(function($container) {
        return new AuthMiddleware(
            $container->get(AuthServiceInterface::class),
            '/login'
        );
    }),
    
    CSRFMiddleware::class => factory(function() {
        return new CSRFMiddleware();
    }),
    
    RateLimitMiddleware::class => factory(function() {
        return new RateLimitMiddleware(60, 60); // 60 requests per minute
    }),
    
    CORSMiddleware::class => factory(function() {
        return new CORSMiddleware(
            ['*'], // Allow all origins
            ['GET', 'POST', 'PUT', 'DELETE', 'OPTIONS'],
            ['Content-Type', 'Authorization', 'X-Requested-With', 'X-CSRF-TOKEN']
        );
    }),
    
    LoggingMiddleware::class => factory(function() {
        return new LoggingMiddleware(null, false);
    }),
    
    MaintenanceMiddleware::class => factory(function() {
        return new MaintenanceMiddleware(
            null,
            ['127.0.0.1', '::1'], // Allow localhost during maintenance
            true // Allow API requests during maintenance
        );
    }),
    
    //========================================================================
    // Database and Core Services
    //========================================================================
    
    // Register database connection as a shared instance
    DatabaseConnection::class => create(DatabaseConnection::class),
    
    StockService::class => factory(function ($container) {
        return new StockService($container->get(DatabaseConnection::class));
    }),
    
    //========================================================================
    // Service Interface Bindings
    //========================================================================
    
    // Map each service interface to its concrete implementation
    // This allows type-hinting against interfaces while using concrete implementations
    
    // AuthService - binds interface to implementation
    AuthServiceInterface::class => get(AuthService::class),
    
    // DatabaseService - binds interface to implementation
    DatabaseServiceInterface::class => get(DatabaseService::class),
    
    // SearchService - binds interface to implementation
    SearchServiceInterface::class => get(SearchService::class),
    
    // StockDataService - binds interface to implementation
    StockDataServiceInterface::class => get(StockDataService::class),
    
    // PredictionScoringService - binds interface to implementation
    PredictionScoringServiceInterface::class => get(PredictionScoringService::class),
    
    // ValidationService - binds interface to implementation
    ValidationServiceInterface::class => get(ValidationService::class),
    
    // ResponseFormatter - binds interface to implementation
    ResponseFormatterInterface::class => get(ResponseFormatter::class),
    
    // ErrorHandler - binds interface to implementation
    ErrorHandlerInterface::class => get(ErrorHandler::class),
    
    //========================================================================
    // Service Implementation Bindings
    //========================================================================
    
    // AuthService - uses singleton pattern with backward compatibility
    AuthService::class => factory(function() {
        // Check if service has already been instantiated as a singleton
        if (method_exists(AuthService::class, 'getInstance') && AuthService::getInstance() !== null) {
            return AuthService::getInstance();
        }
        // Otherwise create a new instance with constructor injection
        return new AuthService();
    }),
    
    // DatabaseService - uses singleton pattern with backward compatibility
    DatabaseService::class => factory(function() {
        if (class_exists(DatabaseService::class)) {
            // Use singleton if available
            if (method_exists(DatabaseService::class, 'getInstance')) {
                return DatabaseService::getInstance();
            }
            // Fall back to constructor
            return new DatabaseService();
        }
        return null;
    }),
    
    // SearchService - uses singleton pattern with dependency injection support
    SearchService::class => factory(function($container) {
        if (class_exists(SearchService::class)) {
            // First try to get singleton instance for backward compatibility
            if (method_exists(SearchService::class, 'getInstance')) {
                return SearchService::getInstance();
            }
            
            // If service supports constructor injection, resolve dependencies
            try {
                $dbService = $container->get(DatabaseService::class);
                $authService = $container->get(AuthService::class);
                
                // SearchService now supports constructor injection
                return new SearchService($dbService, $authService);
            } catch (Exception $e) {
                error_log("Error resolving SearchService dependencies: " . $e->getMessage());
                return new SearchService();
            }
        }
        return null;
    }),
    
    // StockDataService - uses direct instantiation with potential for constructor injection
    StockDataService::class => factory(function($container) {
        if (class_exists(StockDataService::class)) {
            // Check for singleton pattern first for backward compatibility
            if (method_exists(StockDataService::class, 'getInstance')) {
                return StockDataService::getInstance();
            }
            
            // StockDataService currently uses direct instantiation
            // When it supports constructor injection, we would resolve dependencies here
            return new StockDataService();
        }
        return null;
    }),
    
    // PredictionScoringService - uses direct instantiation with dependency injection support
    PredictionScoringService::class => factory(function($container) {
        if (class_exists(PredictionScoringService::class)) {
            // Check for singleton pattern first for backward compatibility
            if (method_exists(PredictionScoringService::class, 'getInstance')) {
                return PredictionScoringService::getInstance();
            }
            
            // Resolve dependencies for constructor injection
            try {
                $stockDataService = $container->get(StockDataService::class);
                
                // PredictionScoringService now supports constructor injection
                return new PredictionScoringService($stockDataService);
            } catch (Exception $e) {
                error_log("Error resolving PredictionScoringService dependencies: " . $e->getMessage());
                return new PredictionScoringService();
            }
        }
        return null;
    }),
    
    // ValidationService - uses singleton pattern with direct instantiation
    ValidationService::class => factory(function() {
        if (class_exists(ValidationService::class)) {
            // Check for singleton pattern first for backward compatibility
            if (method_exists(ValidationService::class, 'getInstance')) {
                return ValidationService::getInstance();
            }
            
            // ValidationService uses direct instantiation
            return new ValidationService();
        }
        return null;
    }),
    
    // ResponseFormatter - uses singleton pattern with direct instantiation
    ResponseFormatter::class => factory(function() {
        if (class_exists(ResponseFormatter::class)) {
            // Check for singleton pattern first for backward compatibility
            if (method_exists(ResponseFormatter::class, 'getInstance')) {
                return ResponseFormatter::getInstance();
            }
            
            // ResponseFormatter uses direct instantiation
            return new ResponseFormatter();
        }
        return null;
    }),
    
    // ErrorHandler - uses singleton pattern with dependency injection support
    ErrorHandler::class => factory(function($container) {
        if (class_exists(ErrorHandler::class)) {
            // Check for singleton pattern first for backward compatibility
            if (method_exists(ErrorHandler::class, 'getInstance')) {
                return ErrorHandler::getInstance();
            }
            
            // Resolve dependencies for constructor injection
            try {
                $errorController = null;
                
                // Inject ErrorController if available
                if (class_exists(ErrorController::class)) {
                    $errorController = $container->get(ErrorController::class);
                }
                
                // ErrorHandler supports constructor injection
                return new ErrorHandler($errorController);
            } catch (Exception $e) {
                error_log("Error resolving ErrorHandler dependencies: " . $e->getMessage());
                return new ErrorHandler();
            }
        }
        return null;
    }),
    
    //========================================================================
    // Controller Factory Configuration
    //========================================================================
    
    // Controller factory with dependency injection is handled by ServiceProvider
    // This configuration ensures that controllers can receive the services
    // they need via constructor injection
];