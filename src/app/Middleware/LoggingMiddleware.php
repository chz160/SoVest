<?php

namespace App\Middleware;

/**
 * LoggingMiddleware
 * 
 * Middleware for request/response logging.
 * Logs information about incoming requests and outgoing responses.
 */
class LoggingMiddleware implements MiddlewareInterface
{
    /**
     * @var string Path to log file
     */
    protected $logFile;
    
    /**
     * @var bool Whether to log request body
     */
    protected $logRequestBody;
    
    /**
     * @var array Request fields to mask in logs
     */
    protected $maskedFields;
    
    /**
     * Constructor
     *
     * @param string|null $logFile Path to log file (null for default)
     * @param bool $logRequestBody Whether to log request body
     * @param array $maskedFields Sensitive fields to mask in logs
     */
    public function __construct(
        $logFile = null, 
        $logRequestBody = false, 
        $maskedFields = ['password', 'token', 'credit_card']
    ) {
        $this->logFile = $logFile ?? dirname(__DIR__, 2) . '/logs/requests.log';
        $this->logRequestBody = $logRequestBody;
        $this->maskedFields = $maskedFields;
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
        // Log request information
        $this->logRequest($request);
        
        // Call the next middleware
        $result = $next($request);
        
        // Return the result
        return $result;
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
        // Log response information
        $this->logResponse($response);
    }
    
    /**
     * Log request information
     *
     * @param array $request Request data
     * @return void
     */
    protected function logRequest(array $request)
    {
        $method = $_SERVER['REQUEST_METHOD'] ?? 'UNKNOWN';
        $uri = $_SERVER['REQUEST_URI'] ?? '/';
        $ip = $_SERVER['REMOTE_ADDR'] ?? 'unknown';
        $userAgent = $_SERVER['HTTP_USER_AGENT'] ?? 'unknown';
        $timestamp = date('Y-m-d H:i:s');
        $userId = $_SESSION['user_id'] ?? 0;
        
        $logEntry = "[{$timestamp}] REQUEST: {$method} {$uri} - IP: {$ip} - User ID: {$userId} - User Agent: {$userAgent}";
        
        // Log request body if enabled
        if ($this->logRequestBody && !empty($request)) {
            $sanitizedRequest = $this->maskSensitiveData($request);
            $logEntry .= "\nRequest Body: " . json_encode($sanitizedRequest);
        }
        
        // Write to log file
        $this->writeLog($logEntry);
    }
    
    /**
     * Log response information
     *
     * @param mixed $response Response data
     * @return void
     */
    protected function logResponse($response)
    {
        $statusCode = http_response_code();
        $timestamp = date('Y-m-d H:i:s');
        
        $logEntry = "[{$timestamp}] RESPONSE: Status Code: {$statusCode}";
        
        // Write to log file
        $this->writeLog($logEntry);
    }
    
    /**
     * Mask sensitive data in request
     *
     * @param array $data Request data
     * @return array Sanitized data
     */
    protected function maskSensitiveData(array $data)
    {
        $sanitized = [];
        
        foreach ($data as $key => $value) {
            if (in_array(strtolower($key), $this->maskedFields)) {
                $sanitized[$key] = '******';
            } elseif (is_array($value)) {
                $sanitized[$key] = $this->maskSensitiveData($value);
            } else {
                $sanitized[$key] = $value;
            }
        }
        
        return $sanitized;
    }
    
    /**
     * Write to log file
     *
     * @param string $message Message to log
     * @return void
     */
    protected function writeLog($message)
    {
        // Create log directory if it doesn't exist
        $logDir = dirname($this->logFile);
        if (!is_dir($logDir) && !mkdir($logDir, 0755, true) && !is_dir($logDir)) {
            error_log("Failed to create log directory: {$logDir}");
            return;
        }
        
        // Append to log file
        file_put_contents($this->logFile, $message . PHP_EOL, FILE_APPEND);
    }
}