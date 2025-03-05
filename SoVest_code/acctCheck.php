<?php
/**
 * SoVest - Account Creation Handler
 * 
 * Validates and processes new user registration.
 * Uses Eloquent ORM with model validation for database operations.
 */

// Include Eloquent ORM setup and User model
require_once 'bootstrap/database.php';
require_once 'database/models/User.php';

use Database\Models\User;

// Extract the form data with POST
$newEmail = isset($_POST['newEmail']) ? filter_var($_POST['newEmail'], FILTER_SANITIZE_EMAIL) : '';
$newPass = isset($_POST['newPass']) ? $_POST['newPass'] : '';
$newMajor = isset($_POST['newMajor']) ? filter_var($_POST['newMajor'], FILTER_SANITIZE_STRING) : '';
$newYear = isset($_POST['newYear']) ? filter_var($_POST['newYear'], FILTER_SANITIZE_STRING) : '';
$newScholarship = isset($_POST['newScholarship']) ? filter_var($_POST['newScholarship'], FILTER_SANITIZE_STRING) : '';

try {
    // Create a new User model instance with form data
    $user = new User();
    $user->email = $newEmail;
    $user->password = $newPass; // Plain password, will be hashed if validation passes
    $user->major = $newMajor;
    $user->year = $newYear;
    $user->scholarship = $newScholarship;
    
    // Validate the user model using the validation rules
    if (!$user->validate()) {
        // Handle validation errors by checking which fields failed
        $errors = $user->getErrors();
        
        if (isset($errors['email'])) {
            header("Location: acctNew.php?error=invalid_email");
            exit;
        } elseif (isset($errors['password'])) {
            header("Location: acctNew.php?error=password_too_short");
            exit;
        } else {
            // Generic error for other validation failures
            header("Location: acctNew.php?error=validation_failed");
            exit;
        }
    }
    
    // Hash the password before saving
    $user->password = password_hash($newPass, PASSWORD_DEFAULT);
    
    // Save the validated user
    $user->save();
    
    // Redirect to the login page with success message
    header("Location: login.php?success=1");
    exit;
    
} catch (Exception $e) {
    // Log the error
    error_log("Account creation error: " . $e->getMessage());
    
    // Redirect to account creation page with error
    header("Location: acctNew.php?error=system_error");
    exit;
}
?>