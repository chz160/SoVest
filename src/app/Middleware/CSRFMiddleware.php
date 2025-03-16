<?php

namespace App\Middleware;

/**
 * CSRF Protection Middleware
 * 
 * This middleware provides protection against Cross-Site Request Forgery (CSRF) attacks
 * by generating and validating CSRF tokens for non-GET requests.
 */
class CSRFMiddleware implements MiddlewareInterface
{
    /**
     * @var string CSRF token session key
     */
    protected $tokenName = 'csrf_token';
    
    /**
     * @var array HTTP methods that don't require CSRF verification
     */
    protected $excludedMethods = ['GET', 'HEAD', 'OPTIONS'];
    
    /**
     * Constructor
     *
     * @param string $tokenName Name of the CSRF token
     * @param array|null $excludedMethods HTTP methods that don't require CSRF verification
     */
    public function __construct($tokenName = 'csrf_token', array $excludedMethods = null)
    {
        $this->tokenName = $tokenName;
        
        if ($excludedMethods !== null) {
            $this->excludedMethods = $excludedMethods;
        }
        
        // Ensure sessions are started
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
        // Generate token if it doesn't exist
        if (!isset($_SESSION[$this->tokenName])) {
            $this->generateToken();
        }
        
        // Validate token for non-excluded requests
        $method = $_SERVER['REQUEST_METHOD'] ?? 'GET';
        
        if (!in_array($method, $this->excludedMethods)) {
            $token = $_POST[$this->tokenName] ?? $_SERVER['HTTP_X_CSRF_TOKEN'] ?? null;
            
            if (!$token || $token !== $_SESSION[$this->tokenName]) {
                // CSRF token validation failed
                if ($this->isApiRequest()) {
                    // JSON response for API requests
                    http_response_code(403);
                    header('Content-Type: application/json');
                    echo json_encode([
                        'success' => false,
                        'message' => 'CSRF token validation failed'
                    ]);
                    return false;
                } else {
                    // HTML response for web requests
                    http_response_code(403);
                    
                    // Check if a custom CSRF error view exists
                    $errorView = dirname(__DIR__) . '/Views/error/403.php';
                    if (file_exists($errorView)) {
                        include $errorView;
                    } else {
                        echo 'CSRF token validation failed';
                    }
                    exit;
                }
            }
        }
        
        // Token is valid or not required, proceed to next middleware
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
        // No post-processing needed for CSRF middleware
    }
    
    /**
     * Generate a new CSRF token
     *
     * @return string The generated token
     */
    public function generateToken()
    {
        $token = bin2hex(random_bytes(32));
        $_SESSION[$this->tokenName] = $token;
        return $token;
    }
    
    /**
     * Get the current CSRF token
     *
     * @return string|null The current token or null if not set
     */
    public function getToken()
    {
        return $_SESSION[$this->tokenName] ?? null;
    }
    
    /**
     * Generate an HTML input field containing the CSRF token
     *
     * @return string HTML input field
     */
    public function tokenField()
    {
        $token = $this->getToken() ?? $this->generateToken();
        return '<input type="hidden" name="' . htmlspecialchars($this->tokenName) . '" value="' . htmlspecialchars($token) . '">';
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