<?php

require_once 'vendor/autoload.php';
require_once 'app/Handlers/Interfaces/ErrorHandlerInterface.php';
require_once 'app/Handlers/ErrorHandler.php';

// Create a new ErrorHandler instance
$errorHandler = new App\Handlers\ErrorHandler();

// Register error handlers
$errorHandler->register();

// Test log error
echo "Testing logError method...\n";
$errorHandler->logError("Test error message", "warning", ["test" => "context"]);
echo "Log written successfully.\n\n";

// Test handleError
echo "Testing handleError method...\n";
$errorHandler->handleError(E_WARNING, "Test warning", __FILE__, __LINE__, []);
echo "Error handled successfully.\n\n";

// Test exception handling
echo "Testing exception handling...\n";
try {
    throw new Exception("Test exception");
} catch (Exception $e) {
    $errorHandler->handleException($e);
    // This code won't be reached because handleException will exit
    echo "Exception handled.\n\n";
}

// This code won't be reached due to the exception handling above
echo "Test completed.\n";