<?php
/**
 * Mock service classes for testing the DI container
 */

// Interface namespace
namespace App\Services\Interfaces {
    interface AuthServiceInterface {
        public function login($email, $password, $rememberMe = false);
        public function logout();
        public function register($userData);
        public function isAuthenticated();
        public function getCurrentUserId();
        public function getCurrentUser();
        public function requireAuthentication($redirect = null);
        public function verifyPassword($userId, $password);
        public function updateUserProfile($userId, $userData);
    }
    
    interface DatabaseServiceInterface {}
    interface SearchServiceInterface {}
    interface StockDataServiceInterface {}
    interface PredictionScoringServiceInterface {}
}

// Services namespace
namespace Services {
    class AuthService implements \App\Services\Interfaces\AuthServiceInterface {
        private static $instance = null;
        
        public static function getInstance() {
            if (self::$instance === null) {
                self::$instance = new self();
            }
            return self::$instance;
        }
        
        public function login($email, $password, $rememberMe = false) { return []; }
        public function logout() { return true; }
        public function register($userData) { return 1; }
        public function isAuthenticated() { return true; }
        public function getCurrentUserId() { return 1; }
        public function getCurrentUser() { return []; }
        public function requireAuthentication($redirect = null) { return true; }
        public function verifyPassword($userId, $password) { return true; }
        public function updateUserProfile($userId, $userData) { return true; }
    }
    
    class DatabaseService {
        private static $instance = null;
        
        public static function getInstance() {
            if (self::$instance === null) {
                self::$instance = new self();
            }
            return self::$instance;
        }
    }
    
    class SearchService {
        private static $instance = null;
        
        public static function getInstance() {
            if (self::$instance === null) {
                self::$instance = new self();
            }
            return self::$instance;
        }
    }
    
    class StockDataService {}
    
    class PredictionScoringService {}
}

// Database namespace
namespace Database\Models {
    class StockService {
        public function __construct($db = null) {}
    }
}

namespace Database {
    class DatabaseConnection {}
}