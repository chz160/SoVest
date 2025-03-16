<?php
/**
 * Test Autoloader
 * 
 * Registers autoloading for classes to ensure tests work properly
 */

// Register autoloader
spl_autoload_register(function ($class) {
    // Convert namespace to directory structure
    $file = str_replace('\\', DIRECTORY_SEPARATOR, $class) . '.php';
    
    // Check if it's a service interface
    if (strpos($class, 'App\\Services\\Interfaces') === 0) {
        $path = __DIR__ . DIRECTORY_SEPARATOR . 'app' . DIRECTORY_SEPARATOR . 
                'Services' . DIRECTORY_SEPARATOR . 'Interfaces' . DIRECTORY_SEPARATOR . 
                substr($class, strlen('App\\Services\\Interfaces\\')) . '.php';
        
        if (file_exists($path)) {
            require_once $path;
            return true;
        }
    }
    
    // Check if it's a service class with App\Services namespace
    if (strpos($class, 'App\\Services') === 0) {
        $path = __DIR__ . DIRECTORY_SEPARATOR . 'app' . DIRECTORY_SEPARATOR . 
                'Services' . DIRECTORY_SEPARATOR . 
                substr($class, strlen('App\\Services\\')) . '.php';
        
        if (file_exists($path)) {
            require_once $path;
            return true;
        }
    }
    
    // Check if it's a service class with legacy Services namespace (for backward compatibility)
    if (strpos($class, 'Services\\') === 0) {
        $path = __DIR__ . DIRECTORY_SEPARATOR . 'app' . DIRECTORY_SEPARATOR . 
                'Services' . DIRECTORY_SEPARATOR . 
                substr($class, strlen('Services\\')) . '.php';
        
        if (file_exists($path)) {
            require_once $path;
            return true;
        }
    }
    
    // Check if it's a model
    if (strpos($class, 'Database\\Models') === 0) {
        $path = __DIR__ . DIRECTORY_SEPARATOR . 'database' . DIRECTORY_SEPARATOR . 
                'models' . DIRECTORY_SEPARATOR . 
                substr($class, strlen('Database\\Models\\')) . '.php';
        
        if (file_exists($path)) {
            require_once $path;
            return true;
        }
    }
    
    return false;
});