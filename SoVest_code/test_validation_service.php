<?php
/**
 * SoVest - ValidationService Test
 *
 * This file tests the functionality of the ValidationService.
 */

require_once __DIR__ . '/vendor/autoload.php';
require_once __DIR__ . '/services/ValidationService.php';
require_once __DIR__ . '/app/Services/Interfaces/ValidationServiceInterface.php';
require_once __DIR__ . '/app/Services/ServiceProvider.php';

use Services\ValidationService;
use App\Services\ServiceProvider;

echo "=== ValidationService Test ===\n\n";

// Initialize validation service
$validationService = new ValidationService();

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

// Test 2: Nested Validation
echo "\nTest 2: Nested Validation\n";
$nestedRules = [
    'user' => [
        'name' => 'required',
        'contact' => [
            'email' => 'required|email',
            'phone' => 'numeric'
        ]
    ]
];

$nestedValidData = [
    'user' => [
        'name' => 'Jane Doe',
        'contact' => [
            'email' => 'jane@example.com',
            'phone' => '1234567890'
        ]
    ]
];

$nestedInvalidData = [
    'user' => [
        'name' => '',
        'contact' => [
            'email' => 'invalid-email',
            'phone' => 'not-a-number'
        ]
    ]
];

$nestedResult1 = $validationService->validateRequest($nestedRules, $nestedValidData);
$nestedResult2 = $validationService->validateRequest($nestedRules, $nestedInvalidData);

echo "Valid nested data: " . ($nestedResult1 === true ? "PASSED" : "FAILED") . "\n";
echo "Invalid nested data: " . ($nestedResult2 !== true ? "PASSED" : "FAILED") . "\n";

if ($nestedResult2 !== true) {
    echo "Nested errors detected (expected):\n";
    var_export($nestedResult2);
    echo "\n";
}

// Test 3: Model Validation
echo "\nTest 3: Model Validation\n";

// Create a mock model class
class TestModel {
    public $username;
    public $email;
    public $password;
    
    // ValidationTrait compatible properties
    public $rules = [
        'username' => 'required|min:5',
        'email' => 'required|email',
        'password' => 'required|min:8'
    ];
    
    public $messages = [
        'username.min' => 'Username must be at least %s characters',
        'password.min' => 'Password is too short (minimum is %s characters)'
    ];
    
    public $errors = [];
    
    public function __construct($username, $email, $password) {
        $this->username = $username;
        $this->email = $email;
        $this->password = $password;
    }
    
    public function toArray() {
        return [
            'username' => $this->username,
            'email' => $this->email,
            'password' => $this->password
        ];
    }
    
    public function addError($field, $message) {
        $this->errors[$field][] = $message;
    }
    
    public function clearErrors() {
        $this->errors = [];
    }
    
    public function getErrors() {
        return $this->errors;
    }
}

$validModel = new TestModel('johndoe', 'john@example.com', 'securepassword');
$invalidModel = new TestModel('joe', 'not-email', '123');

$modelResult1 = $validationService->validateModel($validModel);
$modelResult2 = $validationService->validateModel($invalidModel);

echo "Valid model: " . ($modelResult1 === true ? "PASSED" : "FAILED") . "\n";
echo "Invalid model: " . ($modelResult2 !== true ? "PASSED" : "FAILED") . "\n";

if ($modelResult2 !== true) {
    echo "Model errors detected (expected):\n";
    foreach ($modelResult2 as $field => $errors) {
        echo "- $field: " . implode(", ", $errors) . "\n";
    }
    
    echo "\nModel error storage (via ValidationTrait compatibility):\n";
    var_export($invalidModel->getErrors());
    echo "\n";
}

// Test 4: Custom Validation Rules
echo "\nTest 4: Custom Validation Rules\n";

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

// Test 5: Conditional Validation
echo "\nTest 5: Conditional Validation\n";

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

// Test 6: Service Provider Integration
echo "\nTest 6: Service Provider Integration\n";

try {
    // Use ServiceProvider to get the ValidationService
    $container = \App\Services\ServiceProvider::getContainer();
    $containerValidationService = $container->get('App\Services\Interfaces\ValidationServiceInterface');
    
    echo "Service Provider integration: " . ($containerValidationService instanceof ValidationService ? "PASSED" : "FAILED") . "\n";
    
    // Verify the service works
    $simpleValidation = $containerValidationService->validateRequest(['name' => 'required'], ['name' => 'Test']);
    echo "Container service validation: " . ($simpleValidation === true ? "PASSED" : "FAILED") . "\n";
} catch (Exception $e) {
    echo "Service Provider integration test failed: " . $e->getMessage() . "\n";
}

echo "\n=== Validation Service Test Complete ===\n";