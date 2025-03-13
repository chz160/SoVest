<?php
/**
 * PHPUnit Bootstrap File
 *
 * This file is used to set up the testing environment for SoVest
 */

// Report all PHP errors
error_reporting(E_ALL);
ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);

// Set error handler
set_error_handler(function ($severity, $message, $file, $line) {
    if (!(error_reporting() & $severity)) {
        // This error code is not included in error_reporting
        return;
    }
    throw new ErrorException($message, 0, $severity, $file, $line);
});

// Define the application environment
define('APP_ENV', 'testing');
define('APP_ROOT', realpath(__DIR__ . '/..'));
define('TEST_MODE', true);

// Include the composer autoloader
$composerAutoload = APP_ROOT . '/vendor/autoload.php';
if (file_exists($composerAutoload)) {
    require_once $composerAutoload;
} else {
    $composerAutoload = APP_ROOT . '/SoVest_code/vendor/autoload.php';
    if (file_exists($composerAutoload)) {
        require_once $composerAutoload;
    } else {
        die('Composer autoloader not found. Please run "composer install" in the project root.');
    }
}

// Include SoVest legacy autoloader for models and services
$legacyAutoload = APP_ROOT . '/SoVest_code/test_autoload.php';
if (file_exists($legacyAutoload)) {
    require_once $legacyAutoload;
}

// Autoload test classes according to PSR-4 standards
spl_autoload_register(function ($class) {
    // Check if the class is a test class
    if (strpos($class, 'Tests\\') === 0) {
        // Convert namespace to directory structure
        $file = APP_ROOT . '/' . str_replace('\\', '/', $class) . '.php';
        if (file_exists($file)) {
            require_once $file;
            return true;
        }
    }
    return false;
});

// Set up the database connection for testing using in-memory SQLite
use Illuminate\Database\Capsule\Manager as Capsule;

// Create a new database connection
$capsule = new Capsule;

// Configure the connection for testing
$capsule->addConnection([
    'driver'   => 'sqlite',
    'database' => ':memory:',
    'prefix'   => '',
]);

// Make this connection available globally
$capsule->setAsGlobal();

// Boot Eloquent ORM
$capsule->bootEloquent();

// Set up the container if used in the application
if (file_exists(APP_ROOT . '/SoVest_code/bootstrap/container.php')) {
    $container = require APP_ROOT . '/SoVest_code/bootstrap/container.php';
}

// Seed database with test data if needed
// This can be customized based on your specific test requirements
if (defined('SEED_TEST_DB') && SEED_TEST_DB) {
    // Add code to seed the database with test data
    require_once APP_ROOT . '/tests/Support/DatabaseSeeder.php';
}

// Return the initialized Capsule manager so it can be used in tests if needed
return $capsule;