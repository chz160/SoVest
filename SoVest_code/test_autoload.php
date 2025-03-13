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
    
    // Check if it's a service class
    if (strpos($class, 'Services\\') === 0) {
        $file = __DIR__ . DIRECTORY_SEPARATOR . str_replace('Services\\', 'services' . DIRECTORY_SEPARATOR, $file);
        if (file_exists($file)) {
            require_once $file;
            return true;
        }
    }
    
    // Check if it's a model
    if (strpos($class, 'Database\\Models\\') === 0) {
        $file = __DIR__ . DIRECTORY_SEPARATOR . str_replace('Database\\Models\\', 'database' . DIRECTORY_SEPARATOR . 'models' . DIRECTORY_SEPARATOR, $file);
        if (file_exists($file)) {
            require_once $file;
            return true;
        }
    }
    
    return false;
});