<?php

namespace App\Middleware;

/**
 * MaintenanceMiddleware
 * 
 * Middleware for maintenance mode detection.
 * Checks if the application is in maintenance mode and redirects
 * users to a maintenance page, except for allowed IPs.
 */
class MaintenanceMiddleware implements MiddlewareInterface
{
    /**
     * @var string Path to maintenance file
     */
    protected $maintenanceFile;
    
    /**
     * @var array IP addresses to allow during maintenance
     */
    protected $allowedIps;
    
    /**
     * @var bool Whether to allow API requests during maintenance
     */
    protected $allowApiRequests;
    
    /**
     * Constructor
     *
     * @param string|null $maintenanceFile Path to maintenance file
     * @param array $allowedIps IP addresses to allow during maintenance
     * @param bool $allowApiRequests Whether to allow API requests during maintenance
     */
    public function __construct(
        $maintenanceFile = null, 
        array $allowedIps = [],
        $allowApiRequests = false
    ) {
        $this->maintenanceFile = $maintenanceFile ?? dirname(__DIR__, 2) . '/storage/maintenance.php';
        $this->allowedIps = $allowedIps;
        $this->allowApiRequests = $allowApiRequests;
    }
    
    /**
     * Handle an incoming request
     *
     * @param array $request The request data
     * @param callable $next The next middleware to be called
     * @return mixed
     */
    public function handle(array $request, callable $next)
    {
        // Check if maintenance mode is active
        if ($this->isDownForMaintenance()) {
            // Get client IP address
            $ip = $_SERVER['REMOTE_ADDR'] ?? '0.0.0.0';
            
            // Allow access for specified IPs
            if (in_array($ip, $this->allowedIps)) {
                return $next($request);
            }
            
            // Check if it's an API request
            $isApiRequest = $this->isApiRequest();
            
            // Allow API requests if configured
            if ($isApiRequest && $this->allowApiRequests) {
                return $next($request);
            }
            
            // Return appropriate response for the request type
            if ($isApiRequest) {
                http_response_code(503);
                header('Content-Type: application/json');
                echo json_encode([
                    'success' => false,
                    'message' => 'The application is currently down for maintenance. Please try again later.'
                ]);
                return false;
            } else {
                // Show maintenance page for web requests
                http_response_code(503);
                header('Content-Type: text/html');
                
                // Check if a custom maintenance view exists
                $maintenanceView = dirname(__DIR__) . '/Views/error/503.php';
                if (file_exists($maintenanceView)) {
                    include $maintenanceView;
                } else {
                    echo '<html><head><title>Maintenance</title></head><body>';
                    echo '<h1>Under Maintenance</h1>';
                    echo '<p>The application is currently down for maintenance. Please try again later.</p>';
                    echo '</body></html>';
                }
                return false;
            }
        }
        
        // Not in maintenance mode, proceed to next middleware
        return $next($request);
    }
    
    /**
     * Perform any final actions after the response has been sent to the browser
     *
     * @param array $request The request data
     * @param mixed $response The response data
     * @return void
     */
    public function terminate(array $request, $response)
    {
        // No termination actions needed
    }
    
    /**
     * Check if the application is down for maintenance
     *
     * @return bool True if in maintenance mode, false otherwise
     */
    protected function isDownForMaintenance()
    {
        return file_exists($this->maintenanceFile);
    }
    
    /**
     * Check if the current request is an API request
     *
     * @return bool True if API request, false otherwise
     */
    protected function isApiRequest()
    {
        return (
            isset($_SERVER['HTTP_X_REQUESTED_WITH']) && 
            strtolower($_SERVER['HTTP_X_REQUESTED_WITH']) === 'xmlhttprequest'
        ) || (
            isset($_SERVER['HTTP_ACCEPT']) && 
            strpos($_SERVER['HTTP_ACCEPT'], 'application/json') !== false
        ) || (
            isset($_GET['format']) && $_GET['format'] === 'json'
        );
    }
}