<?php
/**
 * Test script for User model validation
 * 
 * This tests the validation rules and error messages 
 * implemented in the User model
 */

 /*
TODO: this test needs to be converted to a proper phpunit unit test and moved to correct folder under tests.
*/

// Initialize database and autoloader first
require_once __DIR__ . '/vendor/autoload.php';
require_once __DIR__ . '/bootstrap/database.php';

// Import models
use Database\Models\User;

// Test case for invalid email
$user1 = new User();
$user1->email = 'invalid-email';
$user1->password = 'password123';
$user1->first_name = 'John';
$user1->last_name = 'Doe';

echo "Testing invalid email validation:\n";
if (!$user1->validate()) {
    echo "Validation failed as expected. Errors:\n";
    print_r($user1->getErrors());
} else {
    echo "ERROR: Validation should have failed for invalid email\n";
}

// Test case for short password
$user2 = new User();
$user2->email = 'valid@example.com';
$user2->password = '12345'; // Too short
$user2->first_name = 'Jane';
$user2->last_name = 'Smith';

echo "\nTesting password length validation:\n";
if (!$user2->validate()) {
    echo "Validation failed as expected. Errors:\n";
    print_r($user2->getErrors());
} else {
    echo "ERROR: Validation should have failed for short password\n";
}

// Test case for valid user
$user3 = new User();
$user3->email = 'valid@example.com';
$user3->password = 'validpassword';
$user3->first_name = 'Valid';
$user3->last_name = 'User';

echo "\nTesting valid user validation:\n";
if ($user3->validate()) {
    echo "Validation passed as expected.\n";
} else {
    echo "ERROR: Validation should have passed. Errors:\n";
    print_r($user3->getErrors());
}

// Test case for email uniqueness (mocked in this test)
// This will depend on validateUnique implementation
echo "\nNote: Email uniqueness validation requires database connection to test fully.\n";