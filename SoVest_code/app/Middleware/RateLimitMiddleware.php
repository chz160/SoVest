<?php

namespace App\Middleware;

/**
 * RateLimitMiddleware
 * 
 * Middleware for API rate limiting based on user ID or IP address.
 * Limits the number of requests a user or IP can make within a specified time window.
 */
class RateLimitMiddleware implements MiddlewareInterface
{
    /**
     * @var int Maximum requests allowed per time window
     */
    protected $maxRequests;
    
    /**
     * @var int Time window in seconds
     */
    protected $timeWindow;
    
    /**
     * @var string Storage key prefix
     */
    protected $prefix = 'rate_limit_';
    
    /**
     * Constructor
     *
     * @param int $maxRequests Maximum requests allowed per time window
     * @param int $timeWindow Time window in seconds
     */
    public function __construct($maxRequests = 60, $timeWindow = 60)
    {
        $this->maxRequests = $maxRequests;
        $this->timeWindow = $timeWindow;
        
        // Ensure session is started for rate limit storage
        if (session_status() === PHP_SESSION_NONE) {
            session_start();
        }
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
        $ip = $_SERVER['REMOTE_ADDR'] ?? 'unknown';
        $userId = $_SESSION['user_id'] ?? 0;
        
        // Use user ID if available, otherwise use IP
        $identifier = $userId > 0 ? "user_{$userId}" : "ip_{$ip}";
        $key = $this->prefix . $identifier;
        
        // Check rate limit
        if ($this->isRateLimited($key)) {
            // Rate limit exceeded
            http_response_code(429);
            header('Content-Type: application/json');
            echo json_encode([
                'success' => false,
                'message' => 'Rate limit exceeded. Please try again later.'
            ]);
            return false;
        }
        
        // Proceed to next middleware
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
     * Check if the request is rate limited
     *
     * @param string $key Storage key
     * @return bool True if rate limited, false otherwise
     */
    protected function isRateLimited($key)
    {
        // Get current count and time
        $rateLimitData = $this->getRateLimitData($key);
        $count = $rateLimitData['count'];
        $timestamp = $rateLimitData['timestamp'];
        
        // Check if time window has expired
        if (time() - $timestamp > $this->timeWindow) {
            // Reset counter for new time window
            $this->saveRateLimitData($key, 1, time());
            return false;
        }
        
        // Increment counter for current request
        $count++;
        $this->saveRateLimitData($key, $count, $timestamp);
        
        // Check if rate limit exceeded
        return $count > $this->maxRequests;
    }
    
    /**
     * Get rate limit data from storage
     *
     * @param string $key Storage key
     * @return array Rate limit data
     */
    protected function getRateLimitData($key)
    {
        if (!isset($_SESSION[$key])) {
            return ['count' => 0, 'timestamp' => time()];
        }
        
        return $_SESSION[$key];
    }
    
    /**
     * Save rate limit data to storage
     *
     * @param string $key Storage key
     * @param int $count Request count
     * @param int $timestamp Timestamp
     * @return void
     */
    protected function saveRateLimitData($key, $count, $timestamp)
    {
        $_SESSION[$key] = [
            'count' => $count,
            'timestamp' => $timestamp
        ];
    }
}