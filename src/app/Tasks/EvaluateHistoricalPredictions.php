<?php

namespace App\Tasks;

use App\Services\Interfaces\PredictionScoringServiceInterface;
use Illuminate\Support\Facades\Log;

/**
 * EvaluateHistoricalPredictions
 *
 * Backfill job that scores all expired predictions which have never received
 * an accuracy value, regardless of their is_active status.
 * Does not affect the regular predictions:evaluate scheduled job.
 */
class EvaluateHistoricalPredictions
{
    protected $scoringService;

    public function __construct(PredictionScoringServiceInterface $scoringService)
    {
        $this->scoringService = $scoringService;
    }

    public function __invoke()
    {
        Log::info("Starting historical prediction evaluation");

        try {
            $results = $this->scoringService->evaluateHistoricalPredictions();

            Log::info("Historical evaluation results: " .
                     "Total predictions: {$results['total']}, " .
                     "Successfully evaluated: {$results['evaluated']}, " .
                     "Errors: {$results['errors']}");

            if (php_sapi_name() === 'cli') {
                echo "Historical evaluation results: " .
                     "Total predictions: {$results['total']}, " .
                     "Successfully evaluated: {$results['evaluated']}, " .
                     "Errors: {$results['errors']}\n";
            }

            Log::info("Historical prediction evaluation completed");

        } catch (\Exception $e) {
            Log::error("Error in historical prediction evaluation: " . $e->getMessage());

            if (php_sapi_name() === 'cli') {
                echo "Error: " . $e->getMessage() . "\n";
            }
        }
    }
}
