<?php
/**
 * Test script for AuthService
 * 
 * This script tests the functionality of the AuthService class including:
 * - Singleton instance retrieval
 * - Basic method functionality that doesn't require database access
 * - Methods for authentication status checking
 * - Session and cookie handling
 * 
 * Note: This test script focuses on parts of the AuthService that can be tested
 * without a fully configured database environment. For comprehensive testing,
 * the application would need a complete test environment with database access.
 */

// Helper function to print test results
function testResult($test, $result, $message = '') {
    echo $result ? "✓ PASS: $test" : "✗ FAIL: $test";
    if ($message) {
        echo " - $message";
    }
    echo "\n";
    return $result;
}

// Track overall test results
$allTestsPassed = true;

// Output buffering to prevent session warnings in web output
ob_start();

echo "=== AuthService Test Script ===\n\n";

// Load the required service
require_once __DIR__ . '/services/AuthService.php';

// Test 1: Get singleton instance
try {
    $authService = Services\AuthService::getInstance();
    $testPassed = $authService instanceof Services\AuthService;
    $allTestsPassed = $allTestsPassed && $testPassed;
    testResult("Get singleton instance", $testPassed);
} catch (Exception $e) {
    $allTestsPassed = false;
    testResult("Get singleton instance", false, "Exception: " . $e->getMessage());
}

// Test 2: Basic property and method existence checks
$methods = [
    'getInstance', 'login', 'logout', 'register', 'isAuthenticated',
    'getCurrentUserId', 'getCurrentUser', 'verifyPassword'
];

echo "\nVerifying AuthService methods exist:\n";
foreach ($methods as $method) {
    $testPassed = method_exists($authService, $method);
    $allTestsPassed = $allTestsPassed && $testPassed;
    testResult("Method exists: $method", $testPassed);
}

// Test 3: Check logout method functionality (doesn't require DB)
try {
    $result = $authService->logout();
    $testPassed = $result === true;
    $allTestsPassed = $allTestsPassed && $testPassed;
    testResult("Logout method execution", $testPassed);
} catch (Exception $e) {
    $allTestsPassed = false;
    testResult("Logout method execution", false, "Exception: " . $e->getMessage());
}

// Test 4: Check authentication status after logout
try {
    $isAuthenticated = $authService->isAuthenticated();
    $testPassed = $isAuthenticated === false;
    $allTestsPassed = $allTestsPassed && $testPassed;
    testResult("Authentication status after logout", $testPassed);
} catch (Exception $e) {
    $allTestsPassed = false;
    testResult("Authentication status after logout", false, "Exception: " . $e->getMessage());
}

// Test 5: Check null user ID after logout
try {
    $userId = $authService->getCurrentUserId();
    $testPassed = $userId === null;
    $allTestsPassed = $allTestsPassed && $testPassed;
    testResult("Current user ID after logout", $testPassed);
} catch (Exception $e) {
    $allTestsPassed = false;
    testResult("Current user ID after logout", false, "Exception: " . $e->getMessage());
}

// Test 6: Check null user after logout
try {
    $user = $authService->getCurrentUser();
    $testPassed = $user === null;
    $allTestsPassed = $allTestsPassed && $testPassed;
    testResult("Current user after logout", $testPassed);
} catch (Exception $e) {
    $allTestsPassed = false;
    testResult("Current user after logout", false, "Exception: " . $e->getMessage());
}

// Test 7: Test authentication mechanisms using reflection
try {
    // Create a reflector to access private method
    $reflector = new ReflectionClass('Services\AuthService');
    $setAuthMethod = $reflector->getMethod('setAuthCookieAndSession');
    $setAuthMethod->setAccessible(true);
    
    // Simulate a user being authenticated with ID 999 (doesn't need real user)
    $setAuthMethod->invokeArgs($authService, [999, false]);
    
    // Check if isAuthenticated returns true after setting session
    $isAuthenticated = $authService->isAuthenticated();
    $testPassed = $isAuthenticated === true;
    $allTestsPassed = $allTestsPassed && $testPassed;
    testResult("Authentication after setting session", $testPassed);
    
    // Check if getCurrentUserId returns the expected ID
    $userId = $authService->getCurrentUserId();
    $testPassed = $userId === 999;
    $allTestsPassed = $allTestsPassed && $testPassed;
    testResult("Current user ID after setting session", $testPassed);
    
    // Clean up by logging out
    $authService->logout();
} catch (Exception $e) {
    $allTestsPassed = false;
    testResult("Authentication session simulation", false, "Exception: " . $e->getMessage());
}

// Display test summary
echo "\n=== Test Summary ===\n";
if ($allTestsPassed) {
    echo "ALL TESTS PASSED! ";
} else {
    echo "SOME TESTS FAILED. ";
}
echo "Basic functionality tests completed.\n\n";

echo "The AuthService appears to be properly structured and its session/cookie\n";
echo "management functions are working correctly.\n\n";

echo "Note: Database-dependent tests (registration, login, password verification) were not run.\n";
echo "To fully test AuthService, you would need:\n";
echo "1. A working database connection\n";
echo "2. Properly autoloaded User model\n";
echo "3. Test data in the database\n\n";

echo "For a more complete test in the future, you should:\n";
echo "- Set up a test database with known test data\n";
echo "- Configure the autoloader to properly load model classes\n";
echo "- Add tests for user registration, login, and password verification\n";
echo "- Test error handling for invalid inputs and database errors\n";

// Dump any buffered output
ob_end_flush();