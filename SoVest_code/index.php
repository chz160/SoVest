<?php

/**
 * SoVest Legacy Index Redirect
 * 
 * This file redirects requests to the new MVC structure while maintaining backward compatibility.
 * It serves as a bridge during the transition period.
 */

// Check if we're accessing directly or from the new MVC structure
if (!defined('APP_BASE_PATH')) {
    // Define paths
    define('APP_BASE_PATH', __DIR__);
    
    // Determine if we should use the new MVC structure or maintain backward compatibility
    $useMvc = false;
    
    // Check if the request is for an API endpoint
    $isApiRequest = (strpos($_SERVER['REQUEST_URI'], '/api/') !== false);
    
    // If it's an AJAX request or specifically flagged as an API request, use the new structure
    if ($isApiRequest || (isset($_SERVER['HTTP_X_REQUESTED_WITH']) && strtolower($_SERVER['HTTP_X_REQUESTED_WITH']) === 'xmlhttprequest')) {
        $useMvc = true;
    }
    
    // If we're using the MVC structure, include the bootstrap file
    if ($useMvc) {
        $router = require_once APP_BASE_PATH . '/app/bootstrap.php';
        $router->dispatch();
        exit;
    }
    
    // Otherwise, this is the original site entry point
    // We can display the original index page
    // The rest of this file should contain the original index.php content
    
    // Include the original index content
    include __DIR__ . '/views/index.php';
    exit;
}

// If we've reached here, we're being included from the MVC structure
// This section can be used to define common constants or functions needed by legacy code
