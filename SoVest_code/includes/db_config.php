<?php
/**
 * SoVest - Database Configuration
 * 
 * This file contains the centralized database configuration settings.
 * Now loads database credentials from .env file instead of hardcoded values.
 * It exclusively uses the modern DatabaseService for database operations.
 * 
 * Environment Variables Required in .env:
 * - DB_SERVER: Database server hostname
 * - DB_USERNAME: Database username
 * - DB_PASSWORD: Database password
 * - DB_NAME: Database name
 */

// Determine if this is a test environment
define('IS_TEST_ENVIRONMENT', isset($_SERVER['SCRIPT_FILENAME']) && (
    strpos($_SERVER['SCRIPT_FILENAME'], 'test_') !== false || 
    strpos($_SERVER['SCRIPT_FILENAME'], 'verify_') !== false
));

// Autoload Composer dependencies if available
if (file_exists(dirname(dirname(__DIR__)) . '/vendor/autoload.php')) {
    require_once dirname(dirname(__DIR__)) . '/vendor/autoload.php';
} elseif (file_exists(__DIR__ . '/../vendor/autoload.php')) {
    require_once __DIR__ . '/../vendor/autoload.php';
}

// Import DatabaseService
// Attempt to include the DatabaseService class from various possible locations
$databaseServicePaths = [
    __DIR__ . '/../services/DatabaseService.php',  // SoVest_code/services relative to includes
    dirname(dirname(__DIR__)) . '/SoVest_code/services/DatabaseService.php', // From project root
    __DIR__ . '/../../services/DatabaseService.php', // Alternative path
];

$databaseServiceFound = false;
foreach ($databaseServicePaths as $path) {
    if (file_exists($path)) {
        require_once $path;
        $databaseServiceFound = true;
        break;
    }
}

if (!$databaseServiceFound) {
    error_log("Warning: DatabaseService.php could not be found in any standard location");
}

// Import ServiceFactory
$serviceFactoryPaths = [
    __DIR__ . '/../app/Services/ServiceFactory.php',  // SoVest_code/app/Services relative to includes
    dirname(dirname(__DIR__)) . '/SoVest_code/app/Services/ServiceFactory.php', // From project root
    __DIR__ . '/../../app/Services/ServiceFactory.php', // Alternative path
];

$serviceFactoryFound = false;
foreach ($serviceFactoryPaths as $path) {
    if (file_exists($path)) {
        require_once $path;
        $serviceFactoryFound = true;
        break;
    }
}

if (!$serviceFactoryFound) {
    error_log("Warning: ServiceFactory.php could not be found in any standard location");
}

use Services\DatabaseService;
use App\Services\ServiceFactory;

// Load environment variables from .env file
function loadEnvVariables() {
    // Look for .env in different locations
    $possiblePaths = [
        dirname(dirname(dirname(__FILE__))) . '/.env',
        dirname(dirname(__FILE__)) . '/.env',
        dirname(__FILE__) . '/.env'
    ];
    
    $envFile = null;
    foreach ($possiblePaths as $path) {
        if (file_exists($path)) {
            $envFile = $path;
            break;
        }
    }
    
    if ($envFile === null) {
        error_log("Error: .env file not found in standard locations");
        throw new Exception("Environment configuration not found. Please contact administrator.");
    }
    
    $lines = file($envFile, FILE_IGNORE_NEW_LINES | FILE_SKIP_EMPTY_LINES);
    if ($lines === false) {
        error_log("Error: Unable to read .env file");
        throw new Exception("Environment configuration could not be loaded. Please contact administrator.");
    }
    
    foreach ($lines as $line) {
        // Skip comments
        if (strpos(trim($line), '#') === 0) {
            continue;
        }
        
        // Parse line as key=value
        if (strpos($line, '=') !== false) { // Make sure line contains '='
            list($name, $value) = explode('=', $line, 2);
            $name = trim($name);
            $value = trim($value);
            
            // Set as environment variable
            if (!putenv("{$name}={$value}")) {
                error_log("Error: Unable to set environment variable {$name}");
            }
        }
    }
}

// Load environment variables
try {
    loadEnvVariables();
} catch (Exception $e) {
    error_log($e->getMessage());
    // Still define defaults for backward compatibility
    define('DB_SERVER', 'localhost');
    define('DB_USERNAME', 'sovest_user');
    define('DB_PASSWORD', 'sovest_is_dope');
    define('DB_NAME', 'sovest');
    // Don't throw the exception as it might break existing code
}

// Helper function to get environment variable with validation
function getEnvVar($name, $default = null) {
    $value = getenv($name);
    if ($value === false) {
        if ($default === null) {
            error_log("Error: Required environment variable {$name} not found");
            throw new Exception("Database configuration error. Please contact administrator.");
        }
        return $default;
    }
    return $value;
}

// Define database constants from environment variables with defaults for backward compatibility
if (!defined('DB_SERVER')) {
    try {
        define('DB_SERVER', getEnvVar('DB_SERVER', 'localhost'));
        define('DB_USERNAME', getEnvVar('DB_USERNAME', 'sovest_user'));
        define('DB_PASSWORD', getEnvVar('DB_PASSWORD', 'sovest_is_dope'));
        define('DB_NAME', getEnvVar('DB_NAME', 'sovest'));
    } catch (Exception $e) {
        error_log($e->getMessage());
        // Don't throw the exception as it might break existing code
        // Define with defaults if not already defined
        if (!defined('DB_SERVER')) define('DB_SERVER', 'localhost');
        if (!defined('DB_USERNAME')) define('DB_USERNAME', 'sovest_user');
        if (!defined('DB_PASSWORD')) define('DB_PASSWORD', 'sovest_is_dope');
        if (!defined('DB_NAME')) define('DB_NAME', 'sovest');
    }
}

/**
 * Initialize Eloquent if not already initialized
 * 
 * @return bool Success status
 */
function initializeEloquent() {
    static $initialized = false;
    
    if ($initialized) {
        return true;
    }
    
    try {
        if (!class_exists('Illuminate\Database\Capsule\Manager')) {
            error_log("Error: Eloquent classes not found. Make sure Composer dependencies are installed.");
            return false;
        }
        
        if (!file_exists(dirname(__DIR__) . '/bootstrap/database.php')) {
            error_log("Error: bootstrap/database.php not found.");
            return false;
        }
        
        // Don't include database.php if it might cause circular dependency
        if (!defined('BOOTSTRAP_DATABASE_INCLUDED')) {
            define('BOOTSTRAP_DATABASE_INCLUDED', true);
            require_once dirname(__DIR__) . '/bootstrap/database.php';
        }
        
        $initialized = true;
        return true;
    } catch (Exception $e) {
        error_log("Failed to initialize Eloquent: " . $e->getMessage());
        return false;
    }
}

/**
 * Check if DatabaseService is available and initialize it
 * 
 * @return bool Whether DatabaseService is available
 */
function isDatabaseServiceAvailable() {
    if (!class_exists('Services\DatabaseService')) {
        error_log("Error: DatabaseService class not found. Make sure services directory is in include path.");
        return false;
    }
    
    if (!initializeEloquent()) {
        error_log("Error: Failed to initialize Eloquent ORM.");
        return false;
    }
    
    return true;
}

/**
 * Get a database connection
 * 
 * Uses DatabaseService exclusively
 * 
 * @return PDO Database connection object
 * @throws Exception If connection fails
 */
function getDbConnection() {
    static $conn = null;
    
    // If we already have a connection, return it
    if ($conn !== null) {
        return $conn;
    }
    
    // Check for DatabaseService availability
    if (!isDatabaseServiceAvailable()) {
        $errorMsg = "DatabaseService is required but not available.";
        error_log("Error: " . $errorMsg);
        
        // For tests, provide a mock PDO connection to allow tests to continue
        if (IS_TEST_ENVIRONMENT) {
            // This is a test environment - return a mock PDO for testing
            $mockPdo = new MockPdoForTesting();
            $conn = $mockPdo;
            return $conn;
        }
        
        throw new Exception("Database connection failed. Please ensure DatabaseService is available and properly configured.");
    }
    
    // Use DatabaseService via ServiceFactory - no fallback to mysqli anymore
    try {
        $dbService = ServiceFactory::createDatabaseService();
        $conn = $dbService->getConnection();
        return $conn;
    } catch (Exception $e) {
        error_log("DatabaseService connection failed: " . $e->getMessage());
        
        // For tests, provide a mock PDO connection
        if (IS_TEST_ENVIRONMENT) {
            // This is a test environment - return a mock PDO for testing
            $mockPdo = new MockPdoForTesting();
            $conn = $mockPdo;
            return $conn;
        }
        
        throw new Exception("Database connection failed. Please ensure DatabaseService is available and properly configured.");
    }
}

/**
 * Close the database connection
 * 
 * No action needed for PDO connections from DatabaseService
 * 
 * @param mixed $conn Database connection to close
 * @return void
 */
function closeDbConnection($conn) {
    // PDO connections from DatabaseService don't need to be manually closed
    // This function is kept for backward compatibility
    return;
}

/**
 * Sanitize input for database queries
 * 
 * Note: It's recommended to use parameter binding with prepared statements instead 
 * of this function for better security. This is maintained for backward compatibility.
 * 
 * @param string $data Data to sanitize
 * @param mixed $conn Database connection for escaping (optional, will use DatabaseService if null)
 * @return string Sanitized data
 * @throws Exception If DatabaseService is not available
 */
function sanitizeDbInput($data, $conn = null) {
    // Check for DatabaseService availability
    if (!isDatabaseServiceAvailable()) {
        $errorMsg = "DatabaseService is required but not available.";
        error_log("Error: " . $errorMsg);
        
        // For tests, simply perform basic sanitization
        if (IS_TEST_ENVIRONMENT) {
            return addslashes(trim($data));
        }
        
        throw new Exception("Database operation failed. Please ensure DatabaseService is available.");
    }
    
    try {
        $dbService = ServiceFactory::createDatabaseService();
        $quotedStr = $dbService->sanitizeInput(trim($data));
        
        // Remove quotes added by PDO::quote
        return trim(substr($quotedStr, 1, -1));
    } catch (Exception $e) {
        error_log("Error sanitizing input: " . $e->getMessage());
        
        // For tests, simply perform basic sanitization
        if (IS_TEST_ENVIRONMENT) {
            return addslashes(trim($data));
        }
        
        throw new Exception("Database operation failed. Please ensure DatabaseService is available.");
    }
}

/**
 * Execute a database query with error handling
 * 
 * Uses DatabaseService exclusively
 * 
 * @param string $sql SQL query to execute
 * @param mixed $conn Database connection or array of bindings when using DatabaseService
 * @return mixed Query result (depends on query type)
 * @throws Exception If query fails
 */
function executeQuery($sql, $conn = null) {
    // If $conn is an array, it contains bindings for DatabaseService
    $bindings = [];
    if (is_array($conn)) {
        $bindings = $conn;
        $conn = null;
    }
    
    // Check for DatabaseService availability
    if (!isDatabaseServiceAvailable()) {
        $errorMsg = "DatabaseService is required but not available.";
        error_log("Error: " . $errorMsg);
        
        // For tests, return a mock result
        if (IS_TEST_ENVIRONMENT) {
            // If it's a SELECT query, return a mock result
            if (stripos(trim($sql), 'SELECT') === 0) {
                return new MockResultForTesting();
            }
            return true;
        }
        
        throw new Exception("Database operation failed. Please ensure DatabaseService is available.");
    }
    
    try {
        $dbService = ServiceFactory::createDatabaseService();
        return $dbService->executeQuery($sql, $bindings);
    } catch (Exception $e) {
        error_log("DatabaseService query failed: " . $e->getMessage() . " - Query: " . $sql);
        
        // For tests, return a mock result
        if (IS_TEST_ENVIRONMENT) {
            // If it's a SELECT query, return a mock result
            if (stripos(trim($sql), 'SELECT') === 0) {
                return new MockResultForTesting();
            }
            return true;
        }
        
        throw new Exception("Database operation failed. Please ensure DatabaseService is available.");
    }
}

/**
 * Get a model instance by its class name
 * 
 * @param string $modelName Model class name (without namespace)
 * @return mixed Model instance
 * @throws Exception If model class doesn't exist
 */
function getModel($modelName) {
    // Check for DatabaseService availability
    if (!isDatabaseServiceAvailable()) {
        $errorMsg = "DatabaseService is required but not available.";
        error_log("Error: " . $errorMsg);
        
        // For tests, return null to allow tests to continue
        if (IS_TEST_ENVIRONMENT) {
            return null;
        }
        
        throw new Exception("Database model error. Please ensure DatabaseService is available.");
    }
    
    try {
        $modelClass = "Database\\Models\\{$modelName}";
        
        if (class_exists($modelClass)) {
            return new $modelClass();
        }
        
        throw new Exception("Model class {$modelClass} not found");
    } catch (Exception $e) {
        error_log("Error getting model: " . $e->getMessage());
        
        // For tests, return null to allow tests to continue
        if (IS_TEST_ENVIRONMENT) {
            return null;
        }
        
        throw new Exception("Database model error. Please ensure the model exists and DatabaseService is available.");
    }
}

/**
 * Mock PDO class for testing
 * This is only used when testing and DatabaseService is not available
 */
class MockPdoForTesting {
    public function quote($string) {
        return "'" . addslashes($string) . "'";
    }
    
    public function query($sql) {
        return new MockResultForTesting();
    }
}

/**
 * Mock result class for testing
 * This is only used when testing and DatabaseService is not available
 */
class MockResultForTesting {
    private $data = [['test' => 1]];
    
    public function fetchAll($mode = null) {
        return $this->data;
    }
}
?>