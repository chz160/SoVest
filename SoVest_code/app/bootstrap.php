<?php

/**
 * SoVest Application Bootstrap
 * 
 * This file initializes the application by loading dependencies,
 * configuring the environment, and setting up the router.
 */

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

// Load routes
$routes = require_once __DIR__ . '/Routes/routes.php';

// Initialize router
$router = new \App\Routes\Router($routes);

// Return the router so it can be used in index.php
return $router;
