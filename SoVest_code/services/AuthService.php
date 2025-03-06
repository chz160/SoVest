<?php
/**
 * SoVest - Authentication Service
 *
 * This service provides authentication and user management functionality
 * following the service pattern established in the application.
 * It centralizes all auth-related logic for easier maintenance.
 */

namespace Services;

use Database\Models\User;
use Exception;

class AuthService
{
    /**
     * @var AuthService|null Singleton instance of the service
     */
    private static $instance = null;

    /**
     * Get the singleton instance of AuthService
     *
     * @return AuthService
     */
    public static function getInstance()
    {
        if (self::$instance === null) {
            self::$instance = new self();
        }

        return self::$instance;
    }

    /**
     * Private constructor to enforce singleton pattern
     */
    private function __construct()
    {
        // Ensure sessions are started
        if (session_status() === PHP_SESSION_NONE) {
            session_start();
        }
    }

    /**
     * Authenticate a user and create session/cookie if successful
     *
     * @param string $email User email
     * @param string $password User password (plaintext)
     * @param bool $rememberMe Whether to set a persistent cookie
     * @return array|false User data if authenticated, false otherwise
     */
    public function login($email, $password, $rememberMe = false)
    {
        try {
            // Find user by email using Eloquent
            $user = User::where('email', $email)->first();
            
            if (!$user) {
                return false; // No user found with this email
            }
            
            // First try with password_verify() for hashed passwords
            if (password_verify($password, $user->password)) {
                // Set session and cookie
                $this->setAuthCookieAndSession($user->id, $rememberMe);
                return $user->toArray();
            }
            // For backward compatibility, check plaintext password
            else if ($user->password == $password) {
                // Update the password to a hashed version
                $user->password = password_hash($password, PASSWORD_DEFAULT);
                $user->updated_at = date('Y-m-d H:i:s');
                $user->save();
                
                // Set session and cookie
                $this->setAuthCookieAndSession($user->id, $rememberMe);
                return $user->toArray();
            }
            
            return false;
        } catch (Exception $e) {
            error_log("Authentication error: " . $e->getMessage());
            return false;
        }
    }

    /**
     * Log out a user by clearing session and cookies
     *
     * @return bool Success status
     */
    public function logout()
    {
        try {
            // Clear session
            $_SESSION = array();
            
            // Clear cookie
            setcookie("userID", "", time() - 3600, "/");
            
            // Destroy session
            if (session_status() == PHP_SESSION_ACTIVE) {
                session_destroy();
            }
            
            return true;
        } catch (Exception $e) {
            error_log("Logout error: " . $e->getMessage());
            return false;
        }
    }

    /**
     * Register a new user
     *
     * @param array $userData User data including email, password, and profile fields
     * @return int|false User ID if registered, false on failure
     */
    public function register($userData)
    {
        try {
            // Create a User model and set properties
            $user = new User();
            
            // Set fillable fields from userData
            foreach ($user->getFillable() as $field) {
                if (isset($userData[$field])) {
                    $user->{$field} = $userData[$field];
                }
            }
            
            // Hash the password
            if (isset($userData['password'])) {
                $user->password = password_hash($userData['password'], PASSWORD_DEFAULT);
            }
            
            // Set default values for required fields
            if (!isset($userData['reputation_score'])) {
                $user->reputation_score = 0;
            }
            
            // Validate the user model
            if (!$user->validate()) {
                error_log("User validation failed: " . json_encode($user->getErrors()));
                return false;
            }
            
            // Save the user
            $user->save();
            
            return $user->id;
        } catch (Exception $e) {
            error_log("Registration error: " . $e->getMessage());
            return false;
        }
    }

    /**
     * Check if user is authenticated
     *
     * @return bool True if user is authenticated, false otherwise
     */
    public function isAuthenticated()
    {
        return isset($_COOKIE["userID"]) || isset($_SESSION['user_id']);
    }

    /**
     * Get current user ID
     *
     * @return int|null User ID if authenticated, null otherwise
     */
    public function getCurrentUserId()
    {
        if (isset($_COOKIE["userID"])) {
            return (int)$_COOKIE["userID"];
        }
        
        if (isset($_SESSION['user_id'])) {
            return (int)$_SESSION['user_id'];
        }
        
        return null;
    }

    /**
     * Get current user data
     *
     * @return array|null User data if authenticated, null otherwise
     */
    public function getCurrentUser()
    {
        $userId = $this->getCurrentUserId();
        
        if ($userId === null) {
            return null;
        }
        
        try {
            // Use Eloquent to find the user by ID
            $user = User::find($userId);
            
            if (!$user) {
                return null;
            }
            
            return $user->toArray();
        } catch (Exception $e) {
            error_log("Error getting current user: " . $e->getMessage());
            return null;
        }
    }

    /**
     * Require authentication to access a resource
     * If user is not authenticated, redirects to login page
     *
     * @param string|null $redirect URL to redirect to after login
     * @return bool True if authenticated (will exit on failure)
     */
    public function requireAuthentication($redirect = null)
    {
        if (!$this->isAuthenticated()) {
            $redirectParam = $redirect ? '?redirect=' . urlencode($redirect) : '';
            header("Location: login.php$redirectParam");
            exit;
        }
        
        return true;
    }
    
    /**
     * Verify if a password is correct for a user
     *
     * @param int $userId User ID
     * @param string $password Plaintext password to verify
     * @return bool True if password is correct, false otherwise
     */
    public function verifyPassword($userId, $password)
    {
        try {
            $user = User::find((int)$userId);
            
            if (!$user) {
                return false;
            }
            
            // Check password using password_verify
            if (password_verify($password, $user->password)) {
                return true;
            }
            
            // Backward compatibility for plaintext passwords
            if ($user->password == $password) {
                // Update to hashed password
                $user->password = password_hash($password, PASSWORD_DEFAULT);
                $user->updated_at = date('Y-m-d H:i:s');
                $user->save();
                
                return true;
            }
            
            return false;
        } catch (Exception $e) {
            error_log("Password verification error: " . $e->getMessage());
            return false;
        }
    }
    
    /**
     * Update user profile information
     *
     * @param int $userId User ID
     * @param array $userData User data to update
     * @return bool True on success, false on failure
     */
    public function updateUserProfile($userId, $userData)
    {
        try {
            // Find the user by ID
            $user = User::find((int)$userId);
            
            if (!$user) {
                return false;
            }
            
            // Update fields that are in the fillable array
            foreach ($user->getFillable() as $field) {
                if (isset($userData[$field])) {
                    // Handle password specially
                    if ($field === 'password') {
                        $user->password = password_hash($userData[$field], PASSWORD_DEFAULT);
                    } else {
                        $user->$field = $userData[$field];
                    }
                }
            }
            
            // Validate the user model
            if (!$user->validate()) {
                error_log("User validation failed on update: " . json_encode($user->getErrors()));
                return false;
            }
            
            // Save the updated user
            return $user->save();
        } catch (Exception $e) {
            error_log("Update profile error: " . $e->getMessage());
            return false;
        }
    }

    /**
     * Set authentication cookie and session
     *
     * @param int $userId User ID
     * @param bool $rememberMe Whether to set a persistent cookie
     * @return void
     */
    private function setAuthCookieAndSession($userId, $rememberMe = false)
    {
        $userId = (int)$userId;
        
        // Store user ID in session
        $_SESSION['user_id'] = $userId;
        
        // Set cookie if remember me is enabled
        if ($rememberMe) {
            // Cookie expires in 30 days
            setcookie("userID", $userId, time() + (86400 * 30), "/", "", false, true);
        } else {
            // Session cookie (expires when browser is closed)
            setcookie("userID", $userId, 0, "/", "", false, true);
        }
    }
}