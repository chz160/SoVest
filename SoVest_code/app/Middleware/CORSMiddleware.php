<?php

namespace App\Middleware;

/**
 * CORSMiddleware
 * 
 * Middleware for Cross-Origin Resource Sharing (CORS).
 * Handles CORS headers to allow cross-origin requests from specified domains.
 */
class CORSMiddleware implements MiddlewareInterface
{
    /**
     * @var array Allowed origins
     */
    protected $allowedOrigins;
    
    /**
     * @var array Allowed methods
     */
    protected $allowedMethods;
    
    /**
     * @var array Allowed headers
     */
    protected $allowedHeaders;
    
    /**
     * @var bool Whether to allow credentials
     */
    protected $allowCredentials;
    
    /**
     * @var int Max age for preflight requests
     */
    protected $maxAge;
    
    /**
     * Constructor
     *
     * @param array $allowedOrigins Allowed origins (domains)
     * @param array $allowedMethods Allowed HTTP methods
     * @param array $allowedHeaders Allowed headers
     * @param bool $allowCredentials Whether to allow credentials
     * @param int $maxAge Max age for preflight requests in seconds
     */
    public function __construct(
        $allowedOrigins = ['*'],
        $allowedMethods = ['GET', 'POST', 'PUT', 'DELETE', 'OPTIONS'],
        $allowedHeaders = ['Content-Type', 'Authorization', 'X-Requested-With'],
        $allowCredentials = true,
        $maxAge = 86400
    ) {
        $this->allowedOrigins = $allowedOrigins;
        $this->allowedMethods = $allowedMethods;
        $this->allowedHeaders = $allowedHeaders;
        $this->allowCredentials = $allowCredentials;
        $this->maxAge = $maxAge;
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
        $origin = $_SERVER['HTTP_ORIGIN'] ?? null;
        
        // Check if origin is allowed
        if ($origin && $this->isOriginAllowed($origin)) {
            header("Access-Control-Allow-Origin: {$origin}");
            
            // Set other CORS headers
            header('Access-Control-Allow-Methods: ' . implode(', ', $this->allowedMethods));
            header('Access-Control-Allow-Headers: ' . implode(', ', $this->allowedHeaders));
            
            if ($this->allowCredentials) {
                header('Access-Control-Allow-Credentials: true');
            }
            
            header("Access-Control-Max-Age: {$this->maxAge}");
        }
        
        // Handle preflight requests (OPTIONS)
        if ($_SERVER['REQUEST_METHOD'] === 'OPTIONS') {
            http_response_code(204);
            exit;
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
     * Check if origin is allowed
     *
     * @param string $origin Origin to check
     * @return bool True if origin is allowed, false otherwise
     */
    protected function isOriginAllowed($origin)
    {
        // If wildcard is allowed, allow any origin
        if (in_array('*', $this->allowedOrigins)) {
            return true;
        }
        
        // Check if origin is in allowed origins list
        return in_array($origin, $this->allowedOrigins);
    }
}