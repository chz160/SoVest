<?php
/**
 * Bootstrap file for the route tool
 * 
 * Sets up the necessary environment for the tool to work properly.
 */

// Define the application base path
define('APP_BASE_PATH', dirname(__DIR__));

// Try to use Composer's autoloader if it exists
$composerAutoloader = APP_BASE_PATH . '/vendor/autoload.php';
if (file_exists($composerAutoloader)) {
    require $composerAutoloader;
}

// Manually include the required files for the tool
require_once APP_BASE_PATH . '/app/Routes/Router.php';
require_once APP_BASE_PATH . '/app/Routes/OptimizedRouter.php';
require_once APP_BASE_PATH . '/app/Routes/RouteCacheGenerator.php';
require_once APP_BASE_PATH . '/app/Helpers/RoutingHelper.php';

// Additional includes if needed
if (file_exists(APP_BASE_PATH . '/app/Services/ServiceProvider.php')) {
    require_once APP_BASE_PATH . '/app/Services/ServiceProvider.php';
}

// Utility function to get application routes
if (!function_exists('getAppRoutes')) {
    function getAppRoutes() {
        $routesPath = APP_BASE_PATH . '/app/Routes/routes.php';
        if (file_exists($routesPath)) {
            return require $routesPath;
        }
        return [];
    }
}

// Set up any required environment variables
$_SERVER['REQUEST_METHOD'] = $_SERVER['REQUEST_METHOD'] ?? 'GET';
$_SERVER['HTTP_HOST'] = $_SERVER['HTTP_HOST'] ?? 'localhost';
$_SERVER['SERVER_PROTOCOL'] = $_SERVER['SERVER_PROTOCOL'] ?? 'HTTP/1.1';