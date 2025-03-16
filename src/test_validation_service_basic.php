<?php
/**
 * SoVest - ValidationService Basic Test
 *
 * This file tests the basic functionality of the ValidationService without dependencies.
 */

 /*
TODO: this test needs to be converted to a proper phpunit unit test and moved to correct folder under tests.
*/

// Define interface
interface ValidationServiceInterface {
    public function validateRequest(array $rules, array $data, array $customMessages = []);
    public function validateModel($model, array $rules = null, array $customMessages = []);
    public function applyRule($rule, $field, $value, array $params = [], array $customMessages = [], array $data = []);
    public function getAvailableRules();
    public function addRule($rule, callable $callback, $errorMessage);
}

// Include the service class
require_once __DIR__ . '/services/ValidationService.php';

// Create test class that removes dependency
class TestValidationService extends \App\Services\ValidationService {
    // Constructor override to avoid dependency issues
    public function __construct() {
        parent::__construct();
    }
}

echo "=== ValidationService Basic Test ===\n\n";

// Initialize validation service
$validationService = new TestValidationService();

// Test 1: Basic Request Validation
echo "Test 1: Basic Request Validation\n";
$rules = [
    'name' => 'required|min:3',
    'email' => 'required|email',
    'age' => 'numeric|min:18'
];

$validData = [
    'name' => 'John Doe',
    'email' => 'john@example.com',
    'age' => 25
];

$invalidData = [
    'name' => 'Jo',
    'email' => 'not-an-email',
    'age' => 16
];

$result1 = $validationService->validateRequest($rules, $validData);
$result2 = $validationService->validateRequest($rules, $invalidData);

echo "Valid data validation: " . ($result1 === true ? "PASSED" : "FAILED") . "\n";
echo "Invalid data validation: " . ($result2 !== true ? "PASSED" : "FAILED") . "\n";

if ($result2 !== true) {
    echo "Errors detected (expected):\n";
    foreach ($result2 as $field => $errors) {
        echo "- $field: " . implode(", ", $errors) . "\n";
    }
}

// Test 2: Custom Validation Rules
echo "\nTest 2: Custom Validation Rules\n";

// Add a custom validation rule
$validationService->addRule('alpha', function($value) {
    return empty($value) || preg_match('/^[a-zA-Z]+$/', $value);
}, 'The %s field must contain only alphabetic characters');

$customRules = [
    'firstname' => 'required|alpha',
    'lastname' => 'required|alpha'
];

$validCustomData = [
    'firstname' => 'John',
    'lastname' => 'Doe'
];

$invalidCustomData = [
    'firstname' => 'John123',
    'lastname' => 'Doe!'
];

$customResult1 = $validationService->validateRequest($customRules, $validCustomData);
$customResult2 = $validationService->validateRequest($customRules, $invalidCustomData);

echo "Valid custom rules: " . ($customResult1 === true ? "PASSED" : "FAILED") . "\n";
echo "Invalid custom rules: " . ($customResult2 !== true ? "PASSED" : "FAILED") . "\n";

if ($customResult2 !== true) {
    echo "Custom rule errors (expected):\n";
    foreach ($customResult2 as $field => $errors) {
        echo "- $field: " . implode(", ", $errors) . "\n";
    }
}

// Test 3: Conditional Validation
echo "\nTest 3: Conditional Validation\n";

$conditionalRules = [
    'payment_type' => 'required|in:credit,bank',
    'credit_card' => 'required_if:payment_type,credit',
    'bank_account' => 'required_if:payment_type,bank'
];

$validConditionalData1 = [
    'payment_type' => 'credit',
    'credit_card' => '4111111111111111',
    'bank_account' => ''
];

$validConditionalData2 = [
    'payment_type' => 'bank',
    'credit_card' => '',
    'bank_account' => '12345678'
];

$invalidConditionalData = [
    'payment_type' => 'credit',
    'credit_card' => '',
    'bank_account' => '12345678'
];

$conditionalResult1 = $validationService->validateRequest($conditionalRules, $validConditionalData1);
$conditionalResult2 = $validationService->validateRequest($conditionalRules, $validConditionalData2);
$conditionalResult3 = $validationService->validateRequest($conditionalRules, $invalidConditionalData);

echo "Valid conditional (credit): " . ($conditionalResult1 === true ? "PASSED" : "FAILED") . "\n";
echo "Valid conditional (bank): " . ($conditionalResult2 === true ? "PASSED" : "FAILED") . "\n";
echo "Invalid conditional: " . ($conditionalResult3 !== true ? "PASSED" : "FAILED") . "\n";

if ($conditionalResult3 !== true) {
    echo "Conditional errors (expected):\n";
    foreach ($conditionalResult3 as $field => $errors) {
        echo "- $field: " . implode(", ", $errors) . "\n";
    }
}

echo "\n=== ValidationService Basic Test Complete ===\n";