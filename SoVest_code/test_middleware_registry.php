<?php
/**
 * SoVest Middleware Registry and Middleware Classes Test
 * 
 * This file tests the functionality of the MiddlewareRegistry
 * and the various middleware classes.
 */
 
// First, define the AuthServiceInterface needed by AuthMiddleware
if (!interface_exists('App\Services\Interfaces\AuthServiceInterface')) {
    // Define it in the global namespace with the fully qualified name
    eval('
        namespace App\Services\Interfaces;
        interface AuthServiceInterface {
            public function isAuthenticated();
        }
    ');
}

// Now require the middleware files
require_once __DIR__ . '/app/Middleware/MiddlewareInterface.php';
require_once __DIR__ . '/app/Middleware/MiddlewareRegistry.php';
require_once __DIR__ . '/app/Middleware/AuthMiddleware.php';
require_once __DIR__ . '/app/Middleware/CSRFMiddleware.php';
require_once __DIR__ . '/app/Middleware/RateLimitMiddleware.php';
require_once __DIR__ . '/app/Middleware/CORSMiddleware.php';
require_once __DIR__ . '/app/Middleware/LoggingMiddleware.php';
require_once __DIR__ . '/app/Middleware/MaintenanceMiddleware.php';

use App\Middleware\MiddlewareRegistry;
use App\Middleware\AuthMiddleware;
use App\Middleware\CSRFMiddleware;
use App\Middleware\RateLimitMiddleware;
use App\Middleware\CORSMiddleware;
use App\Middleware\LoggingMiddleware;
use App\Middleware\MaintenanceMiddleware;

// Helper functions
function testHeader($title) {
    echo "\n" . str_repeat("=", 80) . "\n";
    echo "  " . $title . "\n";
    echo str_repeat("=", 80) . "\n";
}

function testSubheader($title) {
    echo "\n" . str_repeat("-", 60) . "\n";
    echo "  " . $title . "\n";
    echo str_repeat("-", 60) . "\n";
}

function testResult($name, $success, $message = null) {
    echo sprintf("  %-30s: %s", $name, $success ? "PASS" : "FAIL");
    if ($message) {
        echo " - " . $message;
    }
    echo "\n";
}

// Start testing
testHeader("TESTING MIDDLEWARE REGISTRY AND MIDDLEWARE CLASSES");

// Create a middleware registry directly
testSubheader("Testing MiddlewareRegistry Basic Functionality");

$registry = new MiddlewareRegistry();

// Create middleware instances
// Create simple mock AuthService that implements AuthServiceInterface
$authService = new class implements \App\Services\Interfaces\AuthServiceInterface {
    public function isAuthenticated() {
        return true; // Always return authenticated for testing
    }
};
$authMiddleware = new AuthMiddleware($authService);
$csrfMiddleware = new CSRFMiddleware();
$rateLimitMiddleware = new RateLimitMiddleware(10, 60);
$corsMiddleware = new CORSMiddleware();
$loggingMiddleware = new LoggingMiddleware();
$maintenanceMiddleware = new MaintenanceMiddleware();

// Test registering middleware
$registry->register('auth', $authMiddleware);
testResult("Register middleware", $registry->has('auth'));

$registry->register('csrf', $csrfMiddleware);
$registry->register('rate_limit', $rateLimitMiddleware);
$registry->register('cors', $corsMiddleware);
$registry->register('logging', $loggingMiddleware);
$registry->register('maintenance', $maintenanceMiddleware);

// Test retrieving middleware
testResult("Get middleware", $registry->get('auth') === $authMiddleware);

// Test registering global middleware
$registry->registerGlobal('logging');
$registry->registerGlobal('cors');
$globalMiddleware = $registry->getGlobalMiddleware();
testResult("Register global middleware", count($globalMiddleware) === 2);

// Test resolving middleware
$resolved = $registry->resolve(['auth', 'csrf']);
testResult("Resolve middleware", count($resolved) === 2);

// Test applying middleware chain
testSubheader("Testing Middleware Chain Execution");

// Create a simple test request
$testRequest = [
    'test_param' => 'test_value'
];

// Test a middleware that should pass
$corsResult = null;
$finalHandler = function($request) use (&$corsResult) {
    $corsResult = "CORS middleware passed";
    return true;
};

// Apply only the CORS middleware
$registry->apply($testRequest, ['cors'], $finalHandler);
testResult("CORS middleware", $corsResult === "CORS middleware passed");

// Test middleware chain with multiple middleware
testSubheader("Testing Multiple Middleware in Chain");

// Create a new registry for this test
$chainRegistry = new MiddlewareRegistry();
$chainRegistry->register('logging', $loggingMiddleware);
$chainRegistry->register('cors', $corsMiddleware);
$chainRegistry->register('rate_limit', $rateLimitMiddleware);

// Register global middleware
$chainRegistry->registerGlobal('logging');

// Create a test chain
$chainResult = false;
$finalChainHandler = function($request) use (&$chainResult) {
    $chainResult = true;
    return "Chain completed successfully";
};

// Apply middleware chain (should include global + specified middleware)
$chainRegistry->apply($testRequest, ['cors', 'rate_limit'], $finalChainHandler);
testResult("Multiple middleware chain", $chainResult === true);

testSubheader("Testing Individual Middleware Functionality");

// Test Rate Limit Middleware
$rateLimitMiddleware = new RateLimitMiddleware(5, 60);
$rateLimitResult = null;

// Process 5 requests that should pass
for ($i = 0; $i < 5; $i++) {
    $result = $rateLimitMiddleware->handle($testRequest, function() use (&$rateLimitResult) {
        $rateLimitResult = true;
        return true;
    });
    
    if (!$result) {
        $rateLimitResult = false;
        break;
    }
}

testResult("Rate Limit (within limit)", $rateLimitResult === true);

// Test CORS Middleware
$corsMiddleware = new CORSMiddleware();
$corsResult = null;

// Simulate an OPTIONS request
$_SERVER['REQUEST_METHOD'] = 'OPTIONS';
ob_start();
$corsResult = $corsMiddleware->handle($testRequest, function() {
    return true;
});
ob_end_clean();
testResult("CORS OPTIONS handling", $corsResult === false);

// Reset REQUEST_METHOD
$_SERVER['REQUEST_METHOD'] = 'GET';

// Test Logging Middleware
$logFile = __DIR__ . '/logs/test_logging.log';
$loggingMiddleware = new LoggingMiddleware($logFile);
$loggingResult = $loggingMiddleware->handle($testRequest, function() {
    return true;
});

// Check if log file was created
$logCreated = file_exists($logFile);
testResult("Logging Middleware", $logCreated && $loggingResult === true);

// Test Maintenance Middleware
$maintenanceFile = __DIR__ . '/storage/maintenance.php';
$maintenanceMiddleware = new MaintenanceMiddleware($maintenanceFile, ['127.0.0.1']);

// Test when not in maintenance mode
$maintenanceResult = $maintenanceMiddleware->handle($testRequest, function() {
    return true;
});
testResult("Maintenance Mode (inactive)", $maintenanceResult === true);

// Create a maintenance file temporarily
if (!is_dir(dirname($maintenanceFile))) {
    mkdir(dirname($maintenanceFile), 0755, true);
}
file_put_contents($maintenanceFile, '<?php return true;');

// Set client IP to allowed list
$_SERVER['REMOTE_ADDR'] = '127.0.0.1';

// Test maintenance mode with allowed IP
$maintenanceResult = $maintenanceMiddleware->handle($testRequest, function() {
    return true;
});
testResult("Maintenance Mode (active, allowed IP)", $maintenanceResult === true);

// Set client IP to non-allowed IP
$_SERVER['REMOTE_ADDR'] = '192.168.1.1';

// Catch output to avoid affecting the terminal
ob_start();
$maintenanceResult = $maintenanceMiddleware->handle($testRequest, function() {
    return true;
});
ob_end_clean();
testResult("Maintenance Mode (active, blocked IP)", $maintenanceResult === false);

// Clean up maintenance file
if (file_exists($maintenanceFile)) {
    unlink($maintenanceFile);
}

// Final summary
testHeader("MIDDLEWARE TESTING COMPLETED");
echo "\nAll tests completed. Check for any FAIL messages above.\n";