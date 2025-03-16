<?php

namespace App\Controllers;

use Database\Models\User;
use Database\Models\Stock;
use Database\Models\Prediction;
use Database\Models\SearchHistory;
use Database\Models\SavedSearch;
use Database\Models\PredictionVote;
use Illuminate\Database\Capsule\Manager as DB;
use App\Services\Interfaces\AuthServiceInterface;
use App\Services\Interfaces\SearchServiceInterface;
use App\Services\Interfaces\StockDataServiceInterface;
use App\Services\ServiceFactory;

/**
 * ApiController
 * 
 * Handles API endpoints for predictions, search, and stocks.
 * Migrated from legacy API endpoints to use the Controller architecture.
 */
class ApiController extends Controller
{
    /**
     * @var SearchServiceInterface Search service instance
     */
    protected $searchService;
    
    /**
     * @var StockDataServiceInterface Stock data service instance
     */
    protected $stockService;
    
    /**
     * Constructor
     * 
     * @param AuthServiceInterface|null $authService Authentication service (optional)
     * @param SearchServiceInterface|null $searchService Search service (optional)
     * @param StockDataServiceInterface|null $stockService Stock data service (optional)
     * @param array $services Additional services to inject (optional)
     */
    public function __construct(
        AuthServiceInterface $authService = null, 
        SearchServiceInterface $searchService = null,
        StockDataServiceInterface $stockService = null,
        array $services = []
    ) {
        parent::__construct($authService, $services);
        
        // Initialize search service with dependency injection
        $this->searchService = $searchService;
        
        // Initialize stock service with dependency injection
        $this->stockService = $stockService;
        
        // Fallback to ServiceFactory for backward compatibility
        if ($this->searchService === null) {
            $this->searchService = ServiceFactory::createSearchService();
        }
        
        if ($this->stockService === null) {
            $this->stockService = ServiceFactory::createStockDataService();
        }
    }
    
    /**
     * Handle CRUD operations for predictions
     * 
     * @return void
     */
    public function predictionOperations()
    {
        // Check authentication
        if (!$this->isAuthenticated()) {
            return $this->jsonError('User not logged in', [], 401);
        }
        
        $userID = $_COOKIE["userID"] ?? null;
        
        try {
            // Verify user exists using Eloquent
            $user = User::find($userID);
            if (!$user) {
                return $this->jsonError('User not found');
            }
        } catch (\Exception $e) {
            return $this->jsonError('Database connection failed: ' . $e->getMessage());
        }
        
        // Determine action
        $action = $this->input('action', '');
        
        switch ($action) {
            case 'create':
                return $this->createPrediction($userID);
                break;
            case 'update':
                return $this->updatePrediction($userID);
                break;
            case 'delete':
                return $this->deletePrediction($userID);
                break;
            case 'get':
                return $this->getPrediction($userID);
                break;
            default:
                return $this->jsonError('Invalid action specified');
        }
    }
    
    /**
     * Create a new prediction
     * 
     * @param int $userID User ID
     * @return void
     */
    private function createPrediction($userID)
    {
        try {
            // Create a new Prediction model instance
            $prediction = new Prediction([
                'user_id' => $userID,
                'stock_id' => $this->input('stock_id'),
                'prediction_type' => $this->input('prediction_type'),
                'target_price' => $this->has('target_price') && !empty($this->input('target_price')) ? 
                            (float) $this->input('target_price') : null,
                'end_date' => $this->input('end_date'),
                'reasoning' => $this->input('reasoning'),
                'prediction_date' => date('Y-m-d H:i:s'),
                'is_active' => 1,
                'accuracy' => null
            ]);
            
            // Use model validation
            if ($prediction->validate()) {
                // Validation passed, save the prediction
                $prediction->save();
                return $this->jsonSuccess("Prediction created successfully", ['prediction_id' => $prediction->prediction_id], 'my_predictions.php');
            } else {
                // Get validation errors and create an error message
                $errors = $prediction->getErrors();
                $errorMessage = "Validation failed: ";
                
                // Format errors for response
                foreach ($errors as $field => $fieldErrors) {
                    foreach ($fieldErrors as $error) {
                        $errorMessage .= $error . " ";
                    }
                }
                
                return $this->jsonError(trim($errorMessage));
            }
        } catch (\Exception $e) {
            return $this->jsonError("Error creating prediction: " . $e->getMessage());
        }
    }
    
    /**
     * Update an existing prediction
     * 
     * @param int $userID User ID
     * @return void
     */
    private function updatePrediction($userID)
    {
        try {
            // Validate required fields
            if (!$this->has('prediction_id') || empty($this->input('prediction_id'))) {
                return $this->jsonError("Missing prediction ID");
            }
            
            $prediction_id = $this->input('prediction_id');
            
            // Check if prediction exists and belongs to user using Eloquent
            $prediction = Prediction::where('prediction_id', $prediction_id)
                                  ->where('user_id', $userID)
                                  ->first();
            
            if (!$prediction) {
                return $this->jsonError("Prediction not found or you don't have permission to edit it");
            }
            
            // Check if prediction can be edited (is still active)
            if (!$prediction->is_active) {
                return $this->jsonError("Cannot edit inactive predictions");
            }
            
            // Update prediction attributes
            $prediction->prediction_type = $this->has('prediction_type') && !empty($this->input('prediction_type')) ? 
                            $this->input('prediction_type') : $prediction->prediction_type;
            
            $prediction->target_price = $this->has('target_price') && $this->input('target_price') !== '' ? 
                            (float) $this->input('target_price') : $prediction->target_price;
            
            $prediction->end_date = $this->has('end_date') && !empty($this->input('end_date')) ? 
                        $this->input('end_date') : $prediction->end_date;
            
            $prediction->reasoning = $this->has('reasoning') && !empty($this->input('reasoning')) ? 
                        $this->input('reasoning') : $prediction->reasoning;
            
            // Use model validation
            if ($prediction->validate()) {
                // Validation passed, save the prediction
                $prediction->save();
                return $this->jsonSuccess("Prediction updated successfully", [], 'my_predictions.php');
            } else {
                // Get validation errors and create an error message
                $errors = $prediction->getErrors();
                $errorMessage = "Validation failed: ";
                
                // Format errors for response
                foreach ($errors as $field => $fieldErrors) {
                    foreach ($fieldErrors as $error) {
                        $errorMessage .= $error . " ";
                    }
                }
                
                return $this->jsonError(trim($errorMessage));
            }
        } catch (\Exception $e) {
            return $this->jsonError("Error updating prediction: " . $e->getMessage());
        }
    }
    
    /**
     * Delete a prediction
     * 
     * @param int $userID User ID
     * @return void
     */
    private function deletePrediction($userID)
    {
        try {
            // Validate required fields
            if (!$this->has('prediction_id') || empty($this->input('prediction_id'))) {
                return $this->jsonError("Missing prediction ID");
            }
            
            $prediction_id = $this->input('prediction_id');
            
            // Check if prediction exists and belongs to user using Eloquent
            $prediction = Prediction::where('prediction_id', $prediction_id)
                                  ->where('user_id', $userID)
                                  ->first();
            
            if (!$prediction) {
                return $this->jsonError("Prediction not found or you don't have permission to delete it");
            }
            
            // Delete prediction using Eloquent
            $prediction->delete();
            
            return $this->jsonSuccess("Prediction deleted successfully");
        } catch (\Exception $e) {
            return $this->jsonError("Error deleting prediction: " . $e->getMessage());
        }
    }
    
    /**
     * Get a single prediction
     * 
     * @param int $userID User ID
     * @return void
     */
    private function getPrediction($userID)
    {
        try {
            if (!isset($_GET['prediction_id']) || empty($_GET['prediction_id'])) {
                return $this->jsonError("Missing prediction ID");
            }
            
            $prediction_id = $_GET['prediction_id'];
            
            // Use Eloquent with eager loading to get prediction with related stock data
            $prediction = Prediction::with('stock')
                                  ->where('prediction_id', $prediction_id)
                                  ->where('user_id', $userID)
                                  ->first();
            
            if ($prediction) {
                // Format data to match the old response structure
                $predictionData = $prediction->toArray();
                $predictionData['symbol'] = $prediction->stock->symbol;
                $predictionData['company_name'] = $prediction->stock->company_name;
                
                return $this->jsonSuccess("Prediction retrieved successfully", $predictionData);
            } else {
                return $this->jsonError("Prediction not found or you don't have permission to view it");
            }
        } catch (\Exception $e) {
            return $this->jsonError("Error retrieving prediction: " . $e->getMessage());
        }
    }
    
    /**
     * Handle search suggestions and saved searches API
     * 
     * @return void
     */
    public function search()
    {
        // Check authentication
        if (!$this->isAuthenticated()) {
            return $this->jsonError('Authentication required', [], 401);
        }
        
        $userID = $_COOKIE["userID"] ?? null;
        $action = $this->input('action', '');
        
        switch ($action) {
            case 'suggestions':
                return $this->getSearchSuggestions();
                break;
            case 'save_search':
                return $this->saveSearchToFavorites($userID);
                break;
            case 'clear_history':
                return $this->clearUserSearchHistory($userID);
                break;
            case 'remove_saved':
                return $this->removeSavedUserSearch($userID);
                break;
            default:
                return $this->jsonError('Invalid action');
        }
    }
    
    /**
     * Get real-time search suggestions as user types
     * 
     * @return void
     */
    private function getSearchSuggestions()
    {
        $query = $this->input('query', '');
        $type = $this->input('type', 'all');
        
        if (empty($query) || strlen($query) < 2) {
            return $this->json(['suggestions' => []]);
        }
        
        try {
            // Use search service if available
            if ($this->searchService) {
                $suggestions = $this->searchService->getSuggestions($query, $type);
                return $this->json(['suggestions' => $suggestions]);
            }
            
            // Fallback to direct database queries
            $suggestions = [];
            $searchParam = "%$query%";
            
            // Different suggestion queries based on search type
            switch ($type) {
                case 'stocks':
                    $stocks = Stock::where('symbol', 'like', $searchParam)
                                ->orWhere('company_name', 'like', $searchParam)
                                ->limit(10)
                                ->get(['symbol', 'company_name']);
                    
                    foreach ($stocks as $stock) {
                        $suggestions[] = [
                            'text' => $stock->symbol . ' - ' . $stock->company_name,
                            'type' => 'stock'
                        ];
                    }
                    break;
                    
                case 'users':
                    $users = User::where('email', 'like', $searchParam)
                               ->orWhereRaw("CONCAT(first_name, ' ', last_name) LIKE ?", [$searchParam])
                               ->limit(10)
                               ->get(['email', 'first_name', 'last_name']);
                    
                    foreach ($users as $user) {
                        $name = $user->first_name . ' ' . $user->last_name;
                        $suggestions[] = [
                            'text' => $name . ' (' . $user->email . ')',
                            'type' => 'user'
                        ];
                    }
                    break;
                    
                case 'predictions':
                    $predictions = DB::table('predictions')
                                    ->join('stocks', 'predictions.stock_id', '=', 'stocks.stock_id')
                                    ->where('stocks.symbol', 'like', $searchParam)
                                    ->limit(10)
                                    ->select(['stocks.symbol', 'predictions.prediction_type'])
                                    ->get();
                    
                    foreach ($predictions as $prediction) {
                        $suggestions[] = [
                            'text' => $prediction->symbol . ' - ' . $prediction->prediction_type,
                            'type' => 'prediction'
                        ];
                    }
                    break;
                    
                default: // Combined results
                    // Get stock symbols (limited to 5)
                    $stocks = Stock::where('symbol', 'like', $searchParam)
                                ->orWhere('company_name', 'like', $searchParam)
                                ->limit(5)
                                ->get(['symbol', 'company_name']);
                    
                    foreach ($stocks as $stock) {
                        $suggestions[] = [
                            'text' => $stock->symbol . ' - ' . $stock->company_name,
                            'type' => 'stock'
                        ];
                    }
                    
                    // Get usernames (limited to 3)
                    $users = User::where('email', 'like', $searchParam)
                               ->orWhereRaw("CONCAT(first_name, ' ', last_name) LIKE ?", [$searchParam])
                               ->limit(3)
                               ->get(['email', 'first_name', 'last_name']);
                    
                    foreach ($users as $user) {
                        $name = $user->first_name . ' ' . $user->last_name;
                        $suggestions[] = [
                            'text' => $name . ' (' . $user->email . ')',
                            'type' => 'user'
                        ];
                    }
                    
                    // Get prediction symbols (limited to 5)
                    $predictions = DB::table('predictions')
                                    ->join('stocks', 'predictions.stock_id', '=', 'stocks.stock_id')
                                    ->where('stocks.symbol', 'like', $searchParam)
                                    ->select('stocks.symbol')
                                    ->distinct()
                                    ->limit(5)
                                    ->get();
                    
                    foreach ($predictions as $prediction) {
                        $suggestions[] = [
                            'text' => $prediction->symbol . ' predictions',
                            'type' => 'prediction'
                        ];
                    }
            }
            
            return $this->json(['suggestions' => $suggestions]);
        } catch (\Exception $e) {
            return $this->jsonError('Failed to get suggestions: ' . $e->getMessage(), [], 500);
        }
    }
    
    /**
     * Save a search to user's favorites
     * 
     * @param int $userID User ID
     * @return void
     */
    private function saveSearchToFavorites($userID)
    {
        $query = $this->input('query', '');
        $type = $this->input('type', 'all');
        
        if (empty($query)) {
            return $this->jsonError('Search query is required', [], 400);
        }
        
        try {
            // Use search service if available
            if ($this->searchService) {
                $result = $this->searchService->saveSearch($query, $type);
                
                if ($result) {
                    $savedSearch = SavedSearch::where('user_id', $userID)
                        ->where('search_query', $query)
                        ->where('search_type', $type)
                        ->first();
                    
                    return $this->jsonSuccess('Search saved successfully', [
                        'search_id' => $savedSearch ? $savedSearch->id : null
                    ]);
                } else {
                    return $this->jsonError('Failed to save search');
                }
            }
            
            // Fallback to direct database operations
            // Check if this search is already saved
            $existingSearch = SavedSearch::where('user_id', $userID)
                                        ->where('search_query', $query)
                                        ->where('search_type', $type)
                                        ->first();
            
            if ($existingSearch) {
                // Search already saved
                return $this->jsonSuccess('Search already saved');
            }
            
            // Save new search
            $savedSearch = new SavedSearch();
            $savedSearch->user_id = $userID;
            $savedSearch->search_query = $query;
            $savedSearch->search_type = $type;
            $savedSearch->save();
            
            return $this->jsonSuccess('Search saved successfully', ['search_id' => $savedSearch->id]);
        } catch (\Exception $e) {
            return $this->jsonError('Failed to save search: ' . $e->getMessage(), [], 500);
        }
    }
    
    /**
     * Clear search history for a user
     * 
     * @param int $userID User ID
     * @return void
     */
    private function clearUserSearchHistory($userID)
    {
        try {
            // Use search service if available
            if ($this->searchService) {
                $result = $this->searchService->clearSearchHistory();
                
                if ($result) {
                    return $this->jsonSuccess('Search history cleared');
                } else {
                    return $this->jsonSuccess('No search history to clear');
                }
            }
            
            // Fallback to direct database operations
            SearchHistory::where('user_id', $userID)->delete();
            
            return $this->jsonSuccess('Search history cleared');
        } catch (\Exception $e) {
            return $this->jsonError('Failed to clear search history: ' . $e->getMessage(), [], 500);
        }
    }
    
    /**
     * Remove a saved search
     * 
     * @param int $userID User ID
     * @return void
     */
    private function removeSavedUserSearch($userID)
    {
        $searchId = (int)$this->input('search_id', 0);
        
        if ($searchId <= 0) {
            return $this->jsonError('Invalid search ID', [], 400);
        }
        
        try {
            // Use search service if available
            if ($this->searchService) {
                $result = $this->searchService->removeSavedSearch($searchId);
                
                if ($result) {
                    return $this->jsonSuccess('Saved search removed');
                } else {
                    return $this->jsonError('Failed to remove saved search: Search not found');
                }
            }
            
            // Fallback to direct database operations
            SavedSearch::where('id', $searchId)
                      ->where('user_id', $userID)
                      ->delete();
            
            return $this->jsonSuccess('Saved search removed');
        } catch (\Exception $e) {
            return $this->jsonError('Failed to remove saved search: ' . $e->getMessage(), [], 500);
        }
    }
    
    /**
     * Handle stock search API endpoint
     * 
     * @return void
     */
    public function stocks()
    {
        $searchTerm = $this->input('term', '');
        
        if (empty($searchTerm)) {
            return $this->json([
                'success' => false,
                'message' => 'Search term is required',
                'data' => []
            ]);
        }
        
        try {
            // Search for stocks using Eloquent
            $stocks = Stock::where('symbol', 'LIKE', "%$searchTerm%")
                ->orWhere('company_name', 'LIKE', "%$searchTerm%")
                ->orderByRaw("CASE 
                    WHEN symbol = ? THEN 1
                    WHEN symbol LIKE ? THEN 2
                    WHEN company_name LIKE ? THEN 3
                    ELSE 4
                  END, symbol ASC", [$searchTerm, "$searchTerm%", "$searchTerm%"])
                ->limit(10)
                ->get(['stock_id', 'symbol', 'company_name']);
            
            $formattedStocks = [];
            foreach ($stocks as $stock) {
                $formattedStocks[] = [
                    'id' => $stock->stock_id,
                    'symbol' => $stock->symbol,
                    'name' => $stock->company_name,
                    'display' => "{$stock->symbol} - {$stock->company_name}"
                ];
            }
            
            // Return results
            return $this->json([
                'success' => true,
                'message' => '',
                'data' => $formattedStocks
            ]);
        } catch (\Exception $e) {
            return $this->json([
                'success' => false,
                'message' => 'Error searching for stocks: ' . $e->getMessage(),
                'data' => []
            ]);
        }
    }
}