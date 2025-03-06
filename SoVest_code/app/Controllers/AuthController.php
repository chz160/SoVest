<?php

namespace App\Controllers;

use Database\Models\User;
use Exception;

/**
 * Auth Controller
 * 
 * Handles authentication and user registration.
 */
class AuthController extends Controller
{
    /**
     * @var \Services\AuthService Auth service instance
     */
    protected $authService;
    
    /**
     * Constructor
     */
    public function __construct()
    {
        parent::__construct();
        // Load the AuthService, ensuring it's available
        require_once __DIR__ . '/../../services/AuthService.php';
        $this->authService = \Services\AuthService::getInstance();
    }
    
    /**
     * Display login form
     * 
     * @return void
     */
    public function loginForm()
    {
        // If user is already logged in, redirect to home
        if ($this->authService->isAuthenticated()) {
            return $this->redirect('home.php');
        }
        
        // Set page title
        $pageTitle = 'Login';
        
        // Check for success message from registration
        $success = $this->input('success');
        
        // Render the login view
        $this->render('user/login', [
            'pageTitle' => $pageTitle,
            'success' => $success
        ]);
    }
    
    /**
     * Handle login form submission
     * 
     * @return void
     */
    public function login()
    {
        // Extract the form data
        $email = $this->input('tryEmail', '');
        $password = $this->input('tryPass', '');
        
        try {
            // Attempt to login using AuthService
            $result = $this->authService->login($email, $password, true);
            
            if ($result) {
                // Login successful, redirect to home
                return $this->redirect('home.php');
            } else {
                // Login failed, redirect to login page with error
                return $this->redirect('login.php', ['error' => 'invalid_credentials']);
            }
        } catch (Exception $e) {
            // Log the error
            error_log("Login error: " . $e->getMessage());
            
            // Redirect to login page with a generic error
            return $this->redirect('login.php', ['error' => 'system_error']);
        }
    }
    
    /**
     * Display registration form
     * 
     * @return void
     */
    public function registerForm()
    {
        // If user is already logged in, redirect to home
        if ($this->authService->isAuthenticated()) {
            return $this->redirect('home.php');
        }
        
        // Set page title
        $pageTitle = 'Create Account';
        
        // Get error message if any
        $error = $this->input('error');
        
        // Render the registration view
        $this->render('user/register', [
            'pageTitle' => $pageTitle,
            'error' => $error
        ]);
    }
    
    /**
     * Handle registration form submission
     * 
     * @return void
     */
    public function register()
    {
        // Extract the form data
        $userData = [
            'email' => $this->input('newEmail', ''),
            'password' => $this->input('newPass', ''),
            'major' => $this->input('newMajor', ''),
            'year' => $this->input('newYear', ''),
            'scholarship' => $this->input('newScholarship', '')
        ];
        
        try {
            // Register user using AuthService
            $userId = $this->authService->register($userData);
            
            if ($userId) {
                // Registration successful, redirect to login page with success message
                return $this->redirect('/login', ['success' => 1]);
            } else {
                // Registration failed, check user model for validation errors
                $user = new User();
                $user->email = $userData['email'];
                $user->password = $userData['password'];
                $user->validate();
                $errors = $user->getErrors();
                
                if (isset($errors['email'])) {
                    return $this->redirect('/register', ['error' => 'invalid_email']);
                } elseif (isset($errors['password'])) {
                    return $this->redirect('/register', ['error' => 'password_too_short']);
                } else {
                    // Generic error for other validation failures
                    return $this->redirect('/register', ['error' => 'validation_failed']);
                }
            }
        } catch (Exception $e) {
            // Log the error
            error_log("Registration error: " . $e->getMessage());
            
            // Redirect to registration page with error
            return $this->redirect('/register', ['error' => 'system_error']);
        }
    }
    
    /**
     * Handle user logout
     * 
     * @return void
     */
    public function logout()
    {
        // Logout using AuthService
        $this->authService->logout();
        
        // Redirect to login page
        return $this->redirect('index.php');
    }
}