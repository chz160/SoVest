<?php

/**
 * SoVest Application Bootstrap
 * 
 * This file initializes the application by loading dependencies,
 * configuring the environment, and setting up the router.
 */

use App\Handlers\Interfaces\ErrorHandlerInterface;

// Load Composer autoloader
require_once __DIR__ . '/../vendor/autoload.php';

// Load database configuration
require_once __DIR__ . '/../bootstrap/database.php';

// Load environment configuration
if (file_exists(__DIR__ . '/../includes/db_config.php')) {
    require_once __DIR__ . '/../includes/db_config.php';
}

// Initialize session
if (session_status() == PHP_SESSION_NONE) {
    session_start();
}

// For backward compatibility, load common files
if (file_exists(__DIR__ . '/../includes/auth.php')) {
    require_once __DIR__ . '/../includes/auth.php';
}

// Register the autoloader for app classes
spl_autoload_register(function ($class) {
    // Convert namespace separators to directory separators
    $class = str_replace('\\', '/', $class);
    
    // Define base directories for different namespaces
    $baseDirectories = [
        'App/' => __DIR__ . '/',
        'Database/Models/' => __DIR__ . '/../database/models/',
    ];
    
    // Check if the class belongs to one of our namespaces
    foreach ($baseDirectories as $namespace => $baseDir) {
        if (strpos($class, $namespace) === 0) {
            $relativeClass = substr($class, strlen($namespace));
            $file = $baseDir . $relativeClass . '.php';
            
            if (file_exists($file)) {
                require $file;
                return true;
            }
        }
    }
    
    return false;
});

// Update Controller render method to support layouts
\App\Controllers\Controller::prototype('render', function($originalRender, $view, $data = [], $return = false) {
    // Call the original method to get the content
    $content = $originalRender($view, $data, true);
    
    // If a layout is specified, render the content within the layout
    if (isset($data['layout'])) {
        $layout = $data['layout'];
        $layoutPath = __DIR__ . '/Views/layouts/' . $layout . '.php';
        
        if (file_exists($layoutPath)) {
            // Add content to data for the layout
            $data['content'] = $content;
            
            // Extract variables from the data array
            extract($data);
            
            // Start output buffering
            ob_start();
            
            // Include the layout
            include $layoutPath;
            
            // Get the contents of the output buffer
            $content = ob_get_clean();
        }
    }
    
    // Return or output the content
    if ($return) {
        return $content;
    }
    
    echo $content;
});

// Load helpers
require_once __DIR__ . '/Helpers/route_helpers.php';

// Load the container
$container = null;
if (file_exists(APP_BASE_PATH . '/bootstrap/container.php')) {
    try {
        // Get the container from ServiceProvider
        require_once __DIR__ . '/Services/ServiceProvider.php';
        $container = \App\Services\ServiceProvider::getContainer();
        
        // Register global error handler
        try {
            // Get error handler instance through ServiceProvider
            if ($container && $container->has(ErrorHandlerInterface::class)) {
                $errorHandler = $container->get(ErrorHandlerInterface::class);
                
                // Register the error handler to handle PHP errors and exceptions
                $errorHandler->register();
            } else {
                // Fallback if the container isn't available or doesn't have the error handler
                error_log('Error handler not available in container');
            }
        } catch (\Exception $e) {
            // Log the error but continue application execution
            error_log('Failed to register error handler: ' . $e->getMessage());
        }
    } catch (Exception $e) {
        error_log("Error loading container: " . $e->getMessage());
    }
}

// Load routes
$routes = require_once __DIR__ . '/Routes/routes.php';

// Initialize router with container
// Use OptimizedRouter for better performance
$router = new \App\Routes\OptimizedRouter($routes, '', $container);

// Return the router so it can be used in index.php
return $router;
