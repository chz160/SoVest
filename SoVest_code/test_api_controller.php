<?php
/**
 * Test script for ApiController
 */

// Include necessary files for bootstrapping
require_once 'app/bootstrap.php';

// Instantiate the ApiController directly
$controller = new App\Controllers\ApiController();

// Helper function to display test results
function display_test_result($name, $success, $message = '') {
    echo "<div style='margin: 10px; padding: 10px; border: 1px solid " . ($success ? "green" : "red") . ";'>";
    echo "<strong>" . ($success ? "PASS" : "FAIL") . ":</strong> $name<br>";
    if (!empty($message)) {
        echo "Message: $message";
    }
    echo "</div>";
}

// Test that controller was created successfully
if ($controller instanceof App\Controllers\ApiController) {
    display_test_result('Controller Instantiation', true, 'ApiController successfully instantiated');
} else {
    display_test_result('Controller Instantiation', false, 'Failed to instantiate ApiController');
}

// Display information about the methods
$reflectionClass = new ReflectionClass('App\Controllers\ApiController');
$methods = $reflectionClass->getMethods(ReflectionMethod::IS_PUBLIC);

echo "<h3>Public Methods:</h3>";
echo "<ul>";
foreach ($methods as $method) {
    if ($method->class === 'App\Controllers\ApiController') {
        echo "<li>{$method->name}()</li>";
    }
}
echo "</ul>";

echo "<p>Note: Full functionality testing requires HTTP requests with appropriate parameters</p>";
echo "<p>Check these endpoints manually:</p>";
echo "<ul>";
echo "<li>/api/predictions - Prediction CRUD operations</li>";
echo "<li>/api/search - Search functionality and saved searches</li>";
echo "<li>/api/stocks - Stock search</li>";
echo "</ul>";
?>