<?php
/**
 * SoVest - Authentication Service Interface
 *
 * This interface defines the contract for authentication services
 * in the SoVest application.
 */

namespace App\Services\Interfaces;

interface AuthServiceInterface
{
    /**
     * Authenticate a user and create session/cookie if successful
     *
     * @param string $email User email
     * @param string $password User password (plaintext)
     * @param bool $rememberMe Whether to set a persistent cookie
     * @return array|false User data if authenticated, false otherwise
     */
    public function login($email, $password, $rememberMe = false);

    /**
     * Log out a user by clearing session and cookies
     *
     * @return bool Success status
     */
    public function logout();

    /**
     * Register a new user
     *
     * @param array $userData User data including email, password, and profile fields
     * @return int|false User ID if registered, false on failure
     */
    public function register($userData);

    /**
     * Check if user is authenticated
     *
     * @return bool True if user is authenticated, false otherwise
     */
    public function isAuthenticated();

    /**
     * Get current user ID
     *
     * @return int|null User ID if authenticated, null otherwise
     */
    public function getCurrentUserId();

    /**
     * Get current user data
     *
     * @return array|null User data if authenticated, null otherwise
     */
    public function getCurrentUser();

    /**
     * Require authentication to access a resource
     * If user is not authenticated, redirects to login page
     *
     * @param string|null $redirect URL to redirect to after login
     * @return bool True if authenticated (will exit on failure)
     */
    public function requireAuthentication($redirect = null);
    
    /**
     * Verify if a password is correct for a user
     *
     * @param int $userId User ID
     * @param string $password Plaintext password to verify
     * @return bool True if password is correct, false otherwise
     */
    public function verifyPassword($userId, $password);
    
    /**
     * Update user profile information
     *
     * @param int $userId User ID
     * @param array $userData User data to update
     * @return bool True on success, false on failure
     */
    public function updateUserProfile($userId, $userData);
}