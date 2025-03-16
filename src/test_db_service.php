<?php
// Test file for DatabaseService

/*
TODO: this test needs to be converted to a proper phpunit unit test and moved to correct folder under tests.
*/

// Include database configuration
require_once __DIR__ . '/includes/db_config.php';

// Include the DatabaseService file
require_once __DIR__ . '/services/DatabaseService.php';

// Try to get an instance of DatabaseService
try {
    $dbService = \App\Services\DatabaseService::getInstance();
    echo "Successfully created DatabaseService instance<br>";
    
    // Try to execute a simple query
    $result = $dbService->fetchOne("SELECT 1 as test");
    if (isset($result['test']) && $result['test'] == 1) {
        echo "Successfully executed query using DatabaseService<br>";
    } else {
        echo "Failed to verify query result<br>";
    }
} catch (Exception $e) {
    echo "Error: " . $e->getMessage() . "<br>";
}