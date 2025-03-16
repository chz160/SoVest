<?php
/**
 * SoVest Prediction Evaluation Cron Job
 * 
 * This script should be run daily via cron to evaluate predictions
 * Example cron entry (daily at midnight):
 * 0 0 * * * php /path/to/SoVest/SoVest_code/cron/evaluate_predictions.php
 */

 /*
    TODO: this should become an invocable object and should be setup as a Task Schedule from
    routes/console.php or as a withSchedule from bootstrap/app.php.
    Also this should be converted to use Laravels DI framework.
 */

// Define mock interfaces needed for services, only if they don't exist
if (!interface_exists('App\\Services\\Interfaces\\StockDataServiceInterface')) {
    eval('namespace App\\Services\\Interfaces; interface StockDataServiceInterface {}');
}

if (!interface_exists('App\\Services\\Interfaces\\PredictionScoringServiceInterface')) {
    eval('namespace App\\Services\\Interfaces; interface PredictionScoringServiceInterface {}');
}

if (!interface_exists('App\\Services\\Interfaces\\DatabaseServiceInterface')) {
    eval('namespace App\\Services\\Interfaces; interface DatabaseServiceInterface {}');
}

// Include the required services
require_once __DIR__ . '/../services/StockDataService.php';
require_once __DIR__ . '/../services/PredictionScoringService.php';
require_once __DIR__ . '/../app/Services/ServiceFactory.php';

// Set error logging
ini_set('display_errors', 0);
ini_set('log_errors', 1);
ini_set('error_log', __DIR__ . '/../logs/prediction_evaluation.log');

// Function to log messages
function logMessage($message) {
    $timestamp = date('Y-m-d H:i:s');
    $logEntry = "[$timestamp] $message" . PHP_EOL;
    
    $logDir = __DIR__ . '/../logs';
    if (!is_dir($logDir)) {
        mkdir($logDir, 0755, true);
    }
    
    file_put_contents($logDir . '/prediction_evaluation.log', $logEntry, FILE_APPEND);
    
    // Output to console if run from CLI
    if (php_sapi_name() === 'cli') {
        echo $logEntry;
    }
}

// Log script start
logMessage("Starting scheduled prediction evaluation");

try {
    // Create service instance using ServiceFactory
    $scoringService = \App\Services\ServiceFactory::createPredictionScoringService();
    
    // Evaluate all active predictions
    $results = $scoringService->evaluateActivePredictions();
    
    // Log results
    logMessage("Evaluation results: " . 
               "Total predictions: {$results['total']}, " . 
               "Successfully evaluated: {$results['evaluated']}, " . 
               "Errors: {$results['errors']}");
    
} catch (Exception $e) {
    // Log error
    logMessage("Error in prediction evaluation: " . $e->getMessage());
}

// Log script end
logMessage("Prediction evaluation completed");
?>