<?php

/**
 * SoVest Application Entry Point
 * 
 * This is the main entry point for the SoVest application.
 * It loads the bootstrap file and dispatches the request to the appropriate controller.
 */

// Define the application base directory
define('APP_BASE_PATH', __DIR__ . '/..');

// Include the bootstrap file
$router = require_once APP_BASE_PATH . '/app/bootstrap.php';

// Dispatch the request
$router->dispatch();
