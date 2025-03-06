<?php

namespace App\Controllers;

use Database\Models\Prediction;
use Database\Models\Stock;
use Database\Models\User;
use Database\Models\PredictionVote;
use Illuminate\Database\Capsule\Manager as DB;

/**
 * PredictionController
 * 
 * Handles all prediction-related actions including creating, viewing,
 * managing predictions, and trending functionality.
 */
class PredictionController extends Controller
{
    /**
     * Display a list of the user's predictions
     */
    public function index()
    {
        // Check if user is authenticated
        $this->requireAuth();
        
        // Get user data
        $user = $this->getAuthUser();
        $userId = $user['id'];
        
        try {
            // Get user's predictions with related stock data using Eloquent
            $predictions = Prediction::with('stock')
                ->where('user_id', $userId)
                ->orderBy('prediction_date', 'desc')
                ->get();
            
            // Format data for the view
            $formattedPredictions = $predictions->map(function($prediction) {
                $predictionData = $prediction->toArray();
                $predictionData['symbol'] = $prediction->stock->symbol;
                $predictionData['company_name'] = $prediction->stock->company_name;
                return $predictionData;
            })->toArray();
            
            // Render the view with the predictions data
            $this->render('my_predictions', [
                'predictions' => $formattedPredictions,
                'pageTitle' => 'My Predictions'
            ]);
        } catch (\Exception $e) {
            $this->withError("Error retrieving predictions: " . $e->getMessage());
            $this->render('my_predictions', [
                'predictions' => [],
                'pageTitle' => 'My Predictions'
            ]);
        }
    }
    
    /**
     * Show the prediction creation form
     */
    public function create()
    {
        // Check if user is authenticated
        $this->requireAuth();
        
        try {
            // Include the StockDataService
            require_once __DIR__ . '/../../services/StockDataService.php';
            $stockService = new \StockDataService();
            
            // Get all active stocks for the dropdown
            $stocks = $stockService->getStocks(true);
            
            // Render the create prediction form
            $this->render('prediction/create', [
                'stocks' => $stocks,
                'isEditing' => false,
                'prediction' => null,
                'pageTitle' => 'Create Prediction'
            ]);
        } catch (\Exception $e) {
            $this->withError("Error loading stock data: " . $e->getMessage());
            $this->redirect('home.php');
        }
    }
    
    /**
     * Handle prediction creation form submission
     */
    public function store()
    {
        // Check if user is authenticated
        $this->requireAuth();
        
        // Get user data
        $user = $this->getAuthUser();
        $userId = $user['id'];
        
        try {
            // Create a new Prediction model instance
            $prediction = new Prediction([
                'user_id' => $userId,
                'stock_id' => $this->input('stock_id'),
                'prediction_type' => $this->input('prediction_type'),
                'target_price' => !empty($this->input('target_price')) ? 
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
                
                // Determine if this is an API request or a form submission
                if ($this->isApiRequest()) {
                    $this->jsonSuccess("Prediction created successfully", 
                        ['prediction_id' => $prediction->prediction_id], 
                        'my_predictions.php');
                } else {
                    $this->withSuccess("Prediction created successfully");
                    $this->redirect('my_predictions.php');
                }
            } else {
                // Get validation errors
                $errors = $prediction->getErrors();
                
                // Determine if this is an API request or a form submission
                if ($this->isApiRequest()) {
                    $errorMessage = "Validation failed: ";
                    foreach ($errors as $field => $fieldErrors) {
                        foreach ($fieldErrors as $error) {
                            $errorMessage .= $error . " ";
                        }
                    }
                    $this->jsonError(trim($errorMessage));
                } else {
                    $this->withModelErrors($prediction);
                    
                    // Include the StockDataService for the form
                    require_once __DIR__ . '/../../services/StockDataService.php';
                    $stockService = new \StockDataService();
                    $stocks = $stockService->getStocks(true);
                    
                    // Re-render the form with errors
                    $this->render('prediction/create', [
                        'stocks' => $stocks,
                        'isEditing' => false,
                        'prediction' => $_POST,
                        'pageTitle' => 'Create Prediction'
                    ]);
                }
            }
        } catch (\Exception $e) {
            if ($this->isApiRequest()) {
                $this->jsonError("Error creating prediction: " . $e->getMessage());
            } else {
                $this->withError("Error creating prediction: " . $e->getMessage());
                $this->redirect('prediction/create');
            }
        }
    }
    
    /**
     * Show the prediction edit form
     */
    public function edit()
    {
        // Check if user is authenticated
        $this->requireAuth();
        
        // Get user data
        $user = $this->getAuthUser();
        $userId = $user['id'];
        
        // Get the prediction ID from the request
        $predictionId = $this->input('id');
        
        if (!$predictionId) {
            $this->withError("Missing prediction ID");
            $this->redirect('my_predictions.php');
            return;
        }
        
        try {
            // Fetch the prediction with its related stock using Eloquent
            $predictionModel = Prediction::with('stock')
                ->where('prediction_id', $predictionId)
                ->where('user_id', $userId)
                ->first();
            
            if (!$predictionModel) {
                $this->withError("Prediction not found or you don't have permission to edit it");
                $this->redirect('my_predictions.php');
                return;
            }
            
            // Check if prediction is still active
            if (!$predictionModel->is_active) {
                $this->withError("Cannot edit inactive predictions");
                $this->redirect('my_predictions.php');
                return;
            }
            
            // Convert to array format to maintain compatibility with the view
            $prediction = $predictionModel->toArray();
            // Add stock attributes that were previously fetched by JOIN
            $prediction['symbol'] = $predictionModel->stock->symbol;
            $prediction['company_name'] = $predictionModel->stock->company_name;
            
            // Include the StockDataService
            require_once __DIR__ . '/../../services/StockDataService.php';
            $stockService = new \StockDataService();
            
            // Get all active stocks for the dropdown
            $stocks = $stockService->getStocks(true);
            
            // Render the edit form
            $this->render('prediction/create', [
                'stocks' => $stocks,
                'isEditing' => true,
                'prediction' => $prediction,
                'pageTitle' => 'Edit Prediction'
            ]);
        } catch (\Exception $e) {
            $this->withError("Error loading prediction: " . $e->getMessage());
            $this->redirect('my_predictions.php');
        }
    }
    
    /**
     * Handle prediction update form submission
     */
    public function update()
    {
        // Check if user is authenticated
        $this->requireAuth();
        
        // Get user data
        $user = $this->getAuthUser();
        $userId = $user['id'];
        
        // Get the prediction ID from the request
        $predictionId = $this->input('prediction_id');
        
        if (!$predictionId) {
            if ($this->isApiRequest()) {
                $this->jsonError("Missing prediction ID");
            } else {
                $this->withError("Missing prediction ID");
                $this->redirect('my_predictions.php');
            }
            return;
        }
        
        try {
            // Check if prediction exists and belongs to user using Eloquent
            $prediction = Prediction::where('prediction_id', $predictionId)
                                  ->where('user_id', $userId)
                                  ->first();
            
            if (!$prediction) {
                if ($this->isApiRequest()) {
                    $this->jsonError("Prediction not found or you don't have permission to edit it");
                } else {
                    $this->withError("Prediction not found or you don't have permission to edit it");
                    $this->redirect('my_predictions.php');
                }
                return;
            }
            
            // Check if prediction can be edited (is still active)
            if (!$prediction->is_active) {
                if ($this->isApiRequest()) {
                    $this->jsonError("Cannot edit inactive predictions");
                } else {
                    $this->withError("Cannot edit inactive predictions");
                    $this->redirect('my_predictions.php');
                }
                return;
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
                
                if ($this->isApiRequest()) {
                    $this->jsonSuccess("Prediction updated successfully", [], 'my_predictions.php');
                } else {
                    $this->withSuccess("Prediction updated successfully");
                    $this->redirect('my_predictions.php');
                }
            } else {
                // Get validation errors
                $errors = $prediction->getErrors();
                
                if ($this->isApiRequest()) {
                    $errorMessage = "Validation failed: ";
                    foreach ($errors as $field => $fieldErrors) {
                        foreach ($fieldErrors as $error) {
                            $errorMessage .= $error . " ";
                        }
                    }
                    $this->jsonError(trim($errorMessage));
                } else {
                    $this->withModelErrors($prediction);
                    
                    // Re-render the edit form with errors
                    $this->redirect('prediction/edit?id=' . $predictionId);
                }
            }
        } catch (\Exception $e) {
            if ($this->isApiRequest()) {
                $this->jsonError("Error updating prediction: " . $e->getMessage());
            } else {
                $this->withError("Error updating prediction: " . $e->getMessage());
                $this->redirect('my_predictions.php');
            }
        }
    }
    
    /**
     * Handle prediction deletion
     */
    public function delete()
    {
        // Check if user is authenticated
        $this->requireAuth();
        
        // Get user data
        $user = $this->getAuthUser();
        $userId = $user['id'];
        
        // Get the prediction ID from the request
        $predictionId = $this->input('prediction_id');
        
        if (!$predictionId) {
            if ($this->isApiRequest()) {
                $this->jsonError("Missing prediction ID");
            } else {
                $this->withError("Missing prediction ID");
                $this->redirect('my_predictions.php');
            }
            return;
        }
        
        try {
            // Check if prediction exists and belongs to user using Eloquent
            $prediction = Prediction::where('prediction_id', $predictionId)
                                  ->where('user_id', $userId)
                                  ->first();
            
            if (!$prediction) {
                if ($this->isApiRequest()) {
                    $this->jsonError("Prediction not found or you don't have permission to delete it");
                } else {
                    $this->withError("Prediction not found or you don't have permission to delete it");
                    $this->redirect('my_predictions.php');
                }
                return;
            }
            
            // Delete prediction using Eloquent
            $prediction->delete();
            
            if ($this->isApiRequest()) {
                $this->jsonSuccess("Prediction deleted successfully");
            } else {
                $this->withSuccess("Prediction deleted successfully");
                $this->redirect('my_predictions.php');
            }
        } catch (\Exception $e) {
            if ($this->isApiRequest()) {
                $this->jsonError("Error deleting prediction: " . $e->getMessage());
            } else {
                $this->withError("Error deleting prediction: " . $e->getMessage());
                $this->redirect('my_predictions.php');
            }
        }
    }
    
    /**
     * Show a specific prediction
     */
    public function view()
    {
        // Check if user is authenticated
        $this->requireAuth();
        
        // Get the prediction ID from the request
        $predictionId = $this->input('id');
        
        if (!$predictionId) {
            $this->withError("Missing prediction ID");
            $this->redirect('trending.php');
            return;
        }
        
        try {
            // Use Eloquent with eager loading to get prediction with related data
            $prediction = Prediction::with(['stock', 'user', 'votes'])
                                  ->where('prediction_id', $predictionId)
                                  ->first();
            
            if (!$prediction) {
                $this->withError("Prediction not found");
                $this->redirect('trending.php');
                return;
            }
            
            // Format data for the view
            $predictionData = $prediction->toArray();
            $predictionData['username'] = $prediction->user->first_name . ' ' . $prediction->user->last_name;
            $predictionData['upvotes'] = $prediction->votes->where('vote_type', 'upvote')->count();
            $predictionData['downvotes'] = $prediction->votes->where('vote_type', 'downvote')->count();
            
            // Include prediction score display component
            require_once __DIR__ . '/../../includes/prediction_score_display.php';
            
            // Render the view with the prediction data
            $this->render('prediction/view', [
                'prediction' => $predictionData,
                'pageTitle' => $prediction->stock->symbol . ' ' . $prediction->prediction_type . ' Prediction'
            ]);
        } catch (\Exception $e) {
            $this->withError("Error retrieving prediction: " . $e->getMessage());
            $this->redirect('trending.php');
        }
    }
    
    /**
     * Show trending predictions
     */
    public function trending()
    {
        // Check if user is authenticated
        $this->requireAuth();
        
        try {
            // Get trending predictions using Eloquent ORM
            $trending_predictions = Prediction::select([
                    'predictions.prediction_id',
                    'users.id as user_id',
                    'users.reputation_score',
                    'stocks.symbol',
                    'predictions.prediction_type as prediction',
                    'predictions.accuracy',
                    'predictions.target_price',
                    'predictions.end_date',
                    'predictions.is_active'
                ])
                ->join('users', 'predictions.user_id', '=', 'users.id')
                ->join('stocks', 'predictions.stock_id', '=', 'stocks.stock_id')
                ->withCount(['votes as votes' => function($query) {
                    $query->where('vote_type', 'upvote');
                }])
                ->addSelect([
                    'users.first_name', 
                    'users.last_name'
                ])
                ->where(function($query) {
                    $query->where('predictions.is_active', 1)
                          ->orWhere(function($query) {
                              $query->whereNotNull('predictions.accuracy')
                                    ->where('predictions.accuracy', '>=', 70);
                          });
                })
                ->orderBy('votes', 'desc')
                ->orderBy('predictions.accuracy', 'desc')
                ->orderBy('predictions.prediction_date', 'desc')
                ->limit(15)
                ->get();
            
            // Map the results to include the full name as username
            $trending_predictions = $trending_predictions->map(function($prediction) {
                $prediction = $prediction->toArray();
                $prediction['username'] = $prediction['first_name'] . ' ' . $prediction['last_name'];
                return $prediction;
            })->toArray();
            
            // If no predictions found, use dummy data
            if (empty($trending_predictions)) {
                $trending_predictions = [
                    ['username' => 'Investor123', 'symbol' => 'AAPL', 'prediction' => 'Bullish', 'votes' => 120, 'accuracy' => 92],
                    ['username' => 'MarketGuru', 'symbol' => 'TSLA', 'prediction' => 'Bearish', 'votes' => 95, 'accuracy' => 85],
                    ['username' => 'StockSavvy', 'symbol' => 'AMZN', 'prediction' => 'Bullish', 'votes' => 75, 'accuracy' => null],
                ];
            }
            
            // Include prediction score display component
            require_once __DIR__ . '/../../includes/prediction_score_display.php';
            
            // Render the view with the trending predictions data
            $this->render('prediction/trending', [
                'trending_predictions' => $trending_predictions,
                'pageTitle' => 'Trending Predictions'
            ]);
        } catch (\Exception $e) {
            // Fallback to dummy data if an error occurs
            $trending_predictions = [
                ['username' => 'Investor123', 'symbol' => 'AAPL', 'prediction' => 'Bullish', 'votes' => 120, 'accuracy' => 92],
                ['username' => 'MarketGuru', 'symbol' => 'TSLA', 'prediction' => 'Bearish', 'votes' => 95, 'accuracy' => 85],
                ['username' => 'StockSavvy', 'symbol' => 'AMZN', 'prediction' => 'Bullish', 'votes' => 75, 'accuracy' => null],
            ];
            
            $this->withError("Error retrieving trending predictions: " . $e->getMessage());
            
            // Include prediction score display component
            require_once __DIR__ . '/../../includes/prediction_score_display.php';
            
            // Render the view with the fallback data
            $this->render('prediction/trending', [
                'trending_predictions' => $trending_predictions,
                'pageTitle' => 'Trending Predictions'
            ]);
        }
    }
    
    /**
     * Handle voting on predictions
     */
    public function vote()
    {
        // Check if user is authenticated
        $this->requireAuth();
        
        // Get user data
        $user = $this->getAuthUser();
        $userId = $user['id'];
        
        // Get prediction ID and vote type from the request
        $predictionId = $this->input('prediction_id');
        $voteType = $this->input('vote_type', 'upvote'); // Default to upvote
        
        if (!$predictionId) {
            $this->jsonError("Missing prediction ID");
            return;
        }
        
        try {
            // Check if prediction exists
            $prediction = Prediction::find($predictionId);
            
            if (!$prediction) {
                $this->jsonError("Prediction not found");
                return;
            }
            
            // Check if user has already voted on this prediction
            $existingVote = PredictionVote::where('prediction_id', $predictionId)
                                        ->where('user_id', $userId)
                                        ->first();
            
            if ($existingVote) {
                // Update existing vote if vote type is different
                if ($existingVote->vote_type !== $voteType) {
                    $existingVote->vote_type = $voteType;
                    $existingVote->vote_date = date('Y-m-d H:i:s');
                    $existingVote->save();
                    $this->jsonSuccess("Vote updated successfully");
                } else {
                    // Remove vote if same type (toggle functionality)
                    $existingVote->delete();
                    $this->jsonSuccess("Vote removed successfully");
                }
            } else {
                // Create new vote
                $vote = new PredictionVote([
                    'prediction_id' => $predictionId,
                    'user_id' => $userId,
                    'vote_type' => $voteType,
                    'vote_date' => date('Y-m-d H:i:s')
                ]);
                
                $vote->save();
                $this->jsonSuccess("Vote recorded successfully");
            }
        } catch (\Exception $e) {
            $this->jsonError("Error processing vote: " . $e->getMessage());
        }
    }
    
    /**
     * Handle API requests for backward compatibility
     */
    public function apiHandler()
    {
        // Check if user is authenticated - replicating the old behavior
        if (!isset($_COOKIE["userID"])) {
            $this->json([
                'success' => false,
                'message' => 'User not logged in',
                'redirect' => 'login.php'
            ]);
            return;
        }
        
        $userId = $_COOKIE["userID"];
        
        try {
            // Verify user exists using Eloquent
            $user = User::find($userId);
            if (!$user) {
                $this->jsonError('User not found');
                return;
            }
        } catch (\Exception $e) {
            $this->jsonError('Database connection failed: ' . $e->getMessage());
            return;
        }
        
        // Determine action from the request
        $action = $this->input('action', '');
        
        switch ($action) {
            case 'create':
                $this->store();
                break;
            case 'update':
                $this->update();
                break;
            case 'delete':
                $this->delete();
                break;
            case 'get':
                $this->apiGetPrediction($userId);
                break;
            default:
                $this->jsonError('Invalid action specified');
                break;
        }
    }
    
    /**
     * API method to get a single prediction
     * 
     * Separated for backward compatibility with the old API structure
     */
    private function apiGetPrediction($userId)
    {
        try {
            if (!$this->has('prediction_id') || empty($this->input('prediction_id'))) {
                $this->jsonError("Missing prediction ID");
                return;
            }
            
            $predictionId = $this->input('prediction_id');
            
            // Use Eloquent with eager loading to get prediction with related stock data
            $prediction = Prediction::with('stock')
                                  ->where('prediction_id', $predictionId)
                                  ->where('user_id', $userId)
                                  ->first();
            
            if ($prediction) {
                // Format data to match the old response structure
                $predictionData = $prediction->toArray();
                $predictionData['symbol'] = $prediction->stock->symbol;
                $predictionData['company_name'] = $prediction->stock->company_name;
                
                $this->jsonSuccess("Prediction retrieved successfully", $predictionData);
            } else {
                $this->jsonError("Prediction not found or you don't have permission to view it");
            }
        } catch (\Exception $e) {
            $this->jsonError("Error retrieving prediction: " . $e->getMessage());
        }
    }
    
    /**
     * Check if the current request is an API request
     */
    private function isApiRequest()
    {
        // Check if request is AJAX or explicitly wants JSON
        return isset($_SERVER['HTTP_X_REQUESTED_WITH']) && 
               strtolower($_SERVER['HTTP_X_REQUESTED_WITH']) === 'xmlhttprequest' || 
               isset($_GET['format']) && $_GET['format'] === 'json' ||
               isset($_POST['format']) && $_POST['format'] === 'json';
    }
}