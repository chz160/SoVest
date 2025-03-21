<?php

namespace App\Controllers;

use Database\Models\SearchHistory;
use Database\Models\SavedSearch;
use Database\Models\User;
use Database\Models\Stock;
use Database\Models\Prediction;
use Illuminate\Database\Capsule\Manager as DB;
use Exception;
use App\Services\Interfaces\SearchServiceInterface;
use App\Services\Interfaces\AuthServiceInterface;
use App\Services\ServiceFactory;

/**
 * Search Controller
 * 
 * Handles search functionality including form display, search results,
 * suggestions, saving searches, and tracking search history.
 */
class SearchController extends Controller
{
    /**
     * @var SearchServiceInterface Search service instance
     */
    protected $searchService;
    
    /**
     * Constructor
     * 
     * @param AuthServiceInterface|null $authService Authentication service (optional)
     * @param SearchServiceInterface|null $searchService Search service (optional)
     * @param array $services Additional services to inject (optional)
     */
    public function __construct(?AuthServiceInterface $authService, ?SearchServiceInterface $searchService, array $services = [])
    {
        parent::__construct($authService, null, $services);
        
        // Initialize search service with dependency injection
        $this->searchService = $searchService;
        
        // Fallback to ServiceFactory for backward compatibility
        if ($this->searchService === null) {
            $this->searchService = ServiceFactory::createSearchService();
        }
    }
    
    /**
     * Display search form and results
     * 
     * @return void
     */
    public function index()
    {
        // Require authentication
        $this->requireAuth();
        $userID = $_COOKIE["userID"];
        
        // Get search parameters
        $query = $this->input('query', '');
        $type = $this->input('type', 'all');
        $prediction = $this->input('prediction', '');
        $sort = $this->input('sort', 'relevance');
        $page = (int)$this->input('page', 1);
        $limit = 10;
        $offset = ($page - 1) * $limit;
        
        // Load user's saved searches
        $savedSearches = [];
        try {
            $savedSearches = SavedSearch::where('user_id', $userID)
                ->orderBy('created_at', 'desc')
                ->limit(5)
                ->get(['search_query', 'search_type', 'created_at', 'id'])
                ->toArray();
        } catch (Exception $e) {
            $this->withError('Error loading saved searches: ' . $e->getMessage());
        }
        
        // Perform search if query is provided
        $searchResults = [];
        $totalResults = 0;
        
        if (!empty($query)) {
            try {
                // Perform search using SearchService
                $searchResults = $this->searchService->performSearch(
                    $query, $type, $prediction, $sort, $limit, $offset
                );
                $totalResults = count($searchResults);
            } catch (Exception $e) {
                $this->withError('Search error: ' . $e->getMessage());
            }
        }
        
        // Get user's search history
        $searchHistory = [];
        try {
            $searchHistory = $this->searchService->getSearchHistory(10);
        } catch (Exception $e) {
            $this->withError('Error loading search history: ' . $e->getMessage());
        }
        
        // If AJAX request, return JSON
        if ($this->isAjaxRequest()) {
            return $this->json([
                'success' => true,
                'results' => $searchResults,
                'total' => $totalResults
            ]);
        }
        
        // Set page title and render view
        $pageTitle = "Search";
        
        // Render the view
        return $this->render('search/index', [
            'query' => $query,
            'type' => $type,
            'prediction' => $prediction,
            'sort' => $sort,
            'page' => $page,
            'results' => $searchResults,
            'totalResults' => $totalResults,
            'savedSearches' => $savedSearches,
            'searchHistory' => $searchHistory,
            'pageTitle' => $pageTitle
        ]);
    }
    
    /**
     * Get search suggestions for autocomplete
     * 
     * @return void
     */
    public function suggestions()
    {
        // Require authentication
        if (!$this->isAuthenticated()) {
            return $this->jsonError('Authentication required', [], 401);
        }
        
        $query = $this->input('query', '');
        $type = $this->input('type', 'all');
        
        // Return empty suggestions if query is too short
        if (empty($query) || strlen($query) < 2) {
            return $this->json(['suggestions' => []]);
        }
        
        try {
            // Get suggestions using SearchService
            $suggestions = $this->searchService->getSuggestions($query, $type);
            return $this->json(['suggestions' => $suggestions]);
        } catch (Exception $e) {
            return $this->jsonError('Failed to get suggestions: ' . $e->getMessage(), [], 500);
        }
    }
    
    /**
     * Save a search to favorites
     * 
     * @return void
     */
    public function saveSearch()
    {
        // Require authentication
        if (!$this->isAuthenticated()) {
            return $this->jsonError('Authentication required', [], 401);
        }
        
        $query = $this->input('query', '');
        $type = $this->input('type', 'all');
        
        if (empty($query)) {
            return $this->jsonError('Search query is required');
        }
        
        try {
            // Use SearchService to save the search
            $result = $this->searchService->saveSearch($query, $type);
            
            if ($result) {
                // Successfully saved
                $savedSearch = SavedSearch::where('user_id', $this->authService->getCurrentUserId())
                    ->where('search_query', $query)
                    ->where('search_type', $type)
                    ->first();
                
                return $this->jsonSuccess('Search saved successfully', [
                    'search_id' => $savedSearch ? $savedSearch->id : null
                ]);
            } else {
                return $this->jsonError('Failed to save search');
            }
        } catch (Exception $e) {
            return $this->jsonError('Failed to save search: ' . $e->getMessage(), [], 500);
        }
    }
    
    /**
     * Remove a saved search
     * 
     * @return void
     */
    public function removeSavedSearch()
    {
        // Require authentication
        if (!$this->isAuthenticated()) {
            return $this->jsonError('Authentication required', [], 401);
        }
        
        $searchId = (int)$this->input('search_id', 0);
        
        if ($searchId <= 0) {
            return $this->jsonError('Invalid search ID');
        }
        
        try {
            // Use SearchService to remove the saved search
            $result = $this->searchService->removeSavedSearch($searchId);
            
            if ($result) {
                return $this->jsonSuccess('Saved search removed');
            } else {
                return $this->jsonError('Failed to remove saved search: Search not found');
            }
        } catch (Exception $e) {
            return $this->jsonError('Failed to remove saved search: ' . $e->getMessage(), [], 500);
        }
    }
    
    /**
     * Display search history
     * 
     * @return void
     */
    public function history()
    {
        // Require authentication
        if (!$this->isAuthenticated()) {
            return $this->jsonError('Authentication required', [], 401);
        }
        
        try {
            // Use SearchService to get search history
            $history = $this->searchService->getSearchHistory(20);
            
            return $this->json([
                'success' => true,
                'history' => $history
            ]);
        } catch (Exception $e) {
            return $this->jsonError('Failed to get search history: ' . $e->getMessage(), [], 500);
        }
    }
    
    /**
     * Clear search history
     * 
     * @return void
     */
    public function clearHistory()
    {
        // Require authentication
        if (!$this->isAuthenticated()) {
            return $this->jsonError('Authentication required', [], 401);
        }
        
        try {
            // Use SearchService to clear search history
            $result = $this->searchService->clearSearchHistory();
            
            if ($result) {
                return $this->jsonSuccess('Search history cleared');
            } else {
                return $this->jsonSuccess('No search history to clear');
            }
        } catch (Exception $e) {
            return $this->jsonError('Failed to clear search history: ' . $e->getMessage(), [], 500);
        }
    }
    
    /**
     * Check if request is AJAX
     * 
     * @return bool True if request is AJAX
     */
    private function isAjaxRequest()
    {
        return !empty($_SERVER['HTTP_X_REQUESTED_WITH']) && 
            strtolower($_SERVER['HTTP_X_REQUESTED_WITH']) == 'xmlhttprequest';
    }
}