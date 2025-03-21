<?php
namespace App\Controllers\Admin;

use App\Controllers\Controller;
use App\Services\Interfaces\AuthServiceInterface;
use App\Services\Interfaces\StockDataServiceInterface;
use App\Services\Interfaces\PredictionScoringServiceInterface;
use App\Services\ServiceFactory;
use App\Models\User;
use App\Models\Stock;
use App\Models\Prediction;
use App\Models\StockPrice;
use Illuminate\Database\Capsule\Manager as DB;

/**
 * Admin DashboardController
 * 
 * Handles the admin dashboard functionality for displaying system statistics,
 * warnings, and status information.
 */
class DashboardController extends Controller
{
    /**
     * @var StockDataServiceInterface Stock data service instance
     */
    protected $stockService;
    
    /**
     * @var PredictionScoringServiceInterface Prediction scoring service instance
     */
    protected $scoringService;
    
    /**
     * Constructor
     * 
     * @param AuthServiceInterface|null $authService Authentication service (optional)
     * @param StockDataServiceInterface|null $stockService Stock data service (optional)
     * @param PredictionScoringServiceInterface|null $scoringService Prediction scoring service (optional)
     * @param array $services Additional services to inject (optional)
     */
    public function __construct(
        ?AuthServiceInterface $authService, 
        ?StockDataServiceInterface $stockService,
        ?PredictionScoringServiceInterface $scoringService,
        array $services = []
    ) {
        parent::__construct($authService, $services);
        
        // Initialize stock service with dependency injection
        $this->stockService = $stockService;
        
        // Initialize scoring service with dependency injection
        $this->scoringService = $scoringService;
        
        // Fallback to ServiceFactory for backward compatibility
        if ($this->stockService === null) {
            $this->stockService = ServiceFactory::createStockDataService();
        }
        
        if ($this->scoringService === null) {
            $this->scoringService = ServiceFactory::createPredictionScoringService($this->stockService);
        }
    }
    
    /**
     * Check if the current user is an admin
     * 
     * @return bool True if the user is an admin, false otherwise
     */
    protected function isAdmin()
    {
        if (!$this->isAuthenticated()) {
            return false;
        }
        
        $user = $this->authService->getCurrentUser();
        return $user && isset($user->is_admin) && $user->is_admin == 1;
    }
    
    /**
     * Admin dashboard index page
     * 
     * @return mixed Dashboard view with statistics or redirect to login
     */
    public function index()
    {
        // Check if the user is an admin
        if (!$this->isAdmin()) {
            return $this->redirect('/login', [
                'error' => 'You must be an admin to access this page'
            ]);
        }
        
        // Gather all required statistics and information for the dashboard
        $data = [
            'user_stats' => $this->getUserStatistics(),
            'prediction_stats' => $this->getPredictionStatistics(),
            'stock_stats' => $this->getStockStatistics(),
            'system_warnings' => $this->getSystemWarnings(),
            'server_info' => $this->getServerInfo(),
            'error_logs' => $this->getErrorLogs(),
        ];
        
        // Render the admin dashboard view with data
        return $this->view('admin.dashboard', $data);
    }
    
    /**
     * Get user statistics
     * 
     * @return array User statistics
     */
    protected function getUserStatistics()
    {
        $totalUsers = User::count();
        $activeUsers = User::where('last_login', '>=', date('Y-m-d H:i:s', strtotime('-30 days')))->count();
        $todayUsers = User::where('last_login', '>=', date('Y-m-d'))->count();
        $adminUsers = User::where('is_admin', 1)->count();
        
        return [
            'total' => $totalUsers,
            'active' => $activeUsers,
            'today' => $todayUsers,
            'admin' => $adminUsers
        ];
    }
    
    /**
     * Get prediction statistics
     * 
     * @return array Prediction statistics
     */
    protected function getPredictionStatistics()
    {
        $totalPredictions = Prediction::count();
        $activePredictions = Prediction::where('is_active', 1)->count();
        $completedPredictions = Prediction::where('is_completed', 1)->count();
        $accuratePredictions = Prediction::where('is_completed', 1)
            ->where('is_accurate', 1)
            ->count();
        
        // Calculate accuracy percentage
        $accuracyRate = $completedPredictions > 0 
            ? round(($accuratePredictions / $completedPredictions) * 100, 2) 
            : 0;
        
        return [
            'total' => $totalPredictions,
            'active' => $activePredictions,
            'completed' => $completedPredictions,
            'accurate' => $accuratePredictions,
            'accuracy_rate' => $accuracyRate
        ];
    }
    
    /**
     * Get stock statistics
     * 
     * @return array Stock statistics
     */
    protected function getStockStatistics()
    {
        $totalStocks = Stock::count();
        $activeStocks = Stock::where('is_active', 1)->count();
        
        // Get the latest stock price update time
        $latestUpdate = StockPrice::max('updated_at');
        
        // Get stocks with the most predictions
        $popularStocks = Stock::select('stocks.*', DB::raw('COUNT(predictions.id) as prediction_count'))
            ->leftJoin('predictions', 'stocks.id', '=', 'predictions.stock_id')
            ->groupBy('stocks.id')
            ->orderBy('prediction_count', 'desc')
            ->limit(5)
            ->get();
            
        // Get stocks with missing data or issues
        $problemStocks = Stock::whereDoesntHave('prices', function($query) {
                $query->where('date', '>=', date('Y-m-d', strtotime('-7 days')));
            })
            ->where('is_active', 1)
            ->get();
            
        return [
            'total' => $totalStocks,
            'active' => $activeStocks,
            'latest_update' => $latestUpdate,
            'popular_stocks' => $popularStocks,
            'problem_stocks' => $problemStocks
        ];
    }
    
    /**
     * Get system warnings
     * 
     * @return array System warnings
     */
    protected function getSystemWarnings()
    {
        $warnings = [];
        
        // Check for stocks without price data in the last 7 days
        $stocksWithoutPrices = Stock::whereDoesntHave('prices', function($query) {
                $query->where('date', '>=', date('Y-m-d', strtotime('-7 days')));
            })
            ->where('is_active', 1)
            ->count();
            
        if ($stocksWithoutPrices > 0) {
            $warnings[] = 'There are ' . $stocksWithoutPrices . ' active stocks without recent price data.';
        }
        
        // Check for unprocessed predictions
        $unprocessedPredictions = Prediction::where('is_active', 1)
            ->where('end_date', '<', date('Y-m-d'))
            ->where('is_completed', 0)
            ->count();
            
        if ($unprocessedPredictions > 0) {
            $warnings[] = 'There are ' . $unprocessedPredictions . ' predictions that have ended but haven\'t been processed.';
        }
        
        // Check disk space
        $diskFree = disk_free_space('/');
        $diskTotal = disk_total_space('/');
        $diskUsagePercentage = round(($diskTotal - $diskFree) / $diskTotal * 100, 2);
        
        if ($diskUsagePercentage > 85) {
            $warnings[] = 'Disk usage is at ' . $diskUsagePercentage . '%. Consider freeing up space.';
        }
        
        return $warnings;
    }
    
    /**
     * Get server information
     * 
     * @return array Server information
     */
    protected function getServerInfo()
    {
        return [
            'php_version' => PHP_VERSION,
            'server_software' => $_SERVER['SERVER_SOFTWARE'] ?? 'Unknown',
            'database_driver' => DB::connection()->getPdo()->getAttribute(\PDO::ATTR_DRIVER_NAME),
            'memory_limit' => ini_get('memory_limit'),
            'max_execution_time' => ini_get('max_execution_time'),
            'upload_max_filesize' => ini_get('upload_max_filesize'),
            'post_max_size' => ini_get('post_max_size'),
            'environment' => getenv('APP_ENV') ?: 'production'
        ];
    }
    
    /**
     * Get recent error logs
     * 
     * @return array Recent error logs
     */
    protected function getErrorLogs()
    {
        $errorLogs = [];
        $errorLogPath = ini_get('error_log');
        
        if ($errorLogPath && file_exists($errorLogPath)) {
            // Get the last 50 lines of the error log
            $errorLogContent = @file($errorLogPath);
            if ($errorLogContent) {
                $errorLogs = array_slice($errorLogContent, -50);
            }
        }
        
        // Fall back to application error log if available
        if (empty($errorLogs) && file_exists(__DIR__ . '/../../../../error_log')) {
            $appErrorLogContent = @file(__DIR__ . '/../../../../error_log');
            if ($appErrorLogContent) {
                $errorLogs = array_slice($appErrorLogContent, -50);
            }
        }
        
        return $errorLogs;
    }
}