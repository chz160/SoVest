<?php
/**
 * SoVest - Error Handler
 *
 * This class implements the ErrorHandlerInterface to provide centralized error
 * handling throughout the application. It handles PHP errors, exceptions, and
 * fatal errors while providing consistent logging and appropriate user interfaces.
 * 
 * The class supports both dependency injection and singleton pattern for 
 * backward compatibility.
 */

namespace App\Handlers;

use App\Handlers\Interfaces\ErrorHandlerInterface;
use App\Controllers\ErrorController;
use App\Services\ServiceProvider;
use Throwable;

class ErrorHandler implements ErrorHandlerInterface
{
    /**
     * @var ErrorHandler|null Singleton instance
     */
    private static $instance = null;
    
    /**
     * @var ErrorController Controller for rendering error pages
     */
    private $errorController;
    
    /**
     * @var string Log file path
     */
    private $logFile;
    
    /**
     * @var array Map PHP error constants to log severity levels
     */
    private $errorLevelMap = [
        E_ERROR             => 'error',
        E_WARNING           => 'warning',
        E_PARSE             => 'error',
        E_NOTICE            => 'notice',
        E_CORE_ERROR        => 'error',
        E_CORE_WARNING      => 'warning',
        E_COMPILE_ERROR     => 'error',
        E_COMPILE_WARNING   => 'warning',
        E_USER_ERROR        => 'error',
        E_USER_WARNING      => 'warning',
        E_USER_NOTICE       => 'notice',
        E_STRICT            => 'notice',
        E_RECOVERABLE_ERROR => 'error',
        E_DEPRECATED        => 'notice',
        E_USER_DEPRECATED   => 'notice',
        E_ALL               => 'error'
    ];
    
    /**
     * Get singleton instance of ErrorHandler
     *
     * @return ErrorHandlerInterface
     */
    public static function getInstance()
    {
        if (self::$instance === null) {
            self::$instance = new self();
        }
        
        return self::$instance;
    }
    
    /**
     * Constructor
     * 
     * Public to support dependency injection while maintaining backward compatibility
     * with singleton pattern.
     * 
     * @param ErrorController|null $errorController Error controller instance
     */
    public function __construct(ErrorController $errorController = null)
    {
        // Initialize error controller with dependency injection
        $this->errorController = $errorController;
        
        // Fallback to ServiceProvider if error controller is not provided
        if ($this->errorController === null) {
            try {
                $this->errorController = ServiceProvider::getController(ErrorController::class);
            } catch (\Exception $e) {
                // If all else fails, create a new instance directly
                $this->errorController = new ErrorController();
            }
        }
        
        // Set log file path
        $this->logFile = dirname(dirname(__DIR__)) . '/logs/app_errors.log';
    }
    
    /**
     * Register global error and exception handlers
     *
     * @return void
     */
    public function register()
    {
        // Set error handler
        set_error_handler([$this, 'handleError']);
        
        // Set exception handler
        set_exception_handler([$this, 'handleException']);
        
        // Register shutdown function for fatal errors
        register_shutdown_function([$this, 'handleShutdown']);
        
        // Ensure log directory exists
        $this->ensureLogDirectoryExists();
    }
    
    /**
     * Handle PHP errors
     *
     * @param int $level Error level
     * @param string $message Error message
     * @param string $file File where the error occurred
     * @param int $line Line number where the error occurred
     * @param array $context Error context
     * @return bool Whether the error was handled
     */
    public function handleError($level, $message, $file, $line, $context = [])
    {
        // Map the PHP error level to a log level
        $logLevel = $this->mapErrorLevel($level);
        
        // Log the error
        $this->logError(
            $message,
            $logLevel,
            [
                'file' => $file,
                'line' => $line,
                'level' => $level,
                'context' => $context
            ]
        );
        
        // For fatal errors, display an error page
        if (in_array($level, [E_ERROR, E_CORE_ERROR, E_COMPILE_ERROR, E_USER_ERROR])) {
            $this->displayError($message, 500, $this->isApiRequest());
            return false; // Don't execute PHP's internal error handler
        }
        
        // Continue execution for non-fatal errors
        return true;
    }
    
    /**
     * Handle exceptions
     *
     * @param \Throwable $exception The exception to handle
     * @return void
     */
    public function handleException($exception)
    {
        // Extract exception information
        $message = $exception->getMessage();
        $file = $exception->getFile();
        $line = $exception->getLine();
        $trace = $exception->getTraceAsString();
        
        // Log the exception
        $this->logError(
            $message,
            'error',
            [
                'file' => $file,
                'line' => $line,
                'trace' => $trace,
                'exception_class' => get_class($exception)
            ]
        );
        
        // Determine HTTP status code based on exception type
        $statusCode = 500; // Default to Internal Server Error
        
        if ($exception instanceof \InvalidArgumentException || 
            $exception instanceof \UnexpectedValueException) {
            $statusCode = 400; // Bad Request
        } elseif ($exception instanceof \UnauthorizedException || 
                  strpos(get_class($exception), 'Auth') !== false) {
            $statusCode = 401; // Unauthorized
        } elseif ($exception instanceof \ForbiddenException || 
                  strpos(get_class($exception), 'Permission') !== false) {
            $statusCode = 403; // Forbidden
        } elseif ($exception instanceof \NotFoundException || 
                  strpos(get_class($exception), 'NotFound') !== false) {
            $statusCode = 404; // Not Found
        }
        
        // Display an appropriate error message
        $this->displayError($message, $statusCode, $this->isApiRequest());
    }
    
    /**
     * Handle application shutdown and fatal errors
     *
     * @return void
     */
    public function handleShutdown()
    {
        // Get the last error
        $error = error_get_last();
        
        // Check if there was a fatal error
        if ($error && in_array($error['type'], [E_ERROR, E_CORE_ERROR, E_COMPILE_ERROR, E_USER_ERROR])) {
            // Format the error message
            $message = "{$error['message']} in {$error['file']} on line {$error['line']}";
            
            // Log the error
            $this->logError(
                $error['message'],
                'error',
                [
                    'file' => $error['file'],
                    'line' => $error['line'],
                    'type' => $error['type']
                ]
            );
            
            // Display an error page
            $this->displayError($message, 500, $this->isApiRequest());
        }
    }
    
    /**
     * Log errors with different levels of severity
     *
     * @param string $message Error message
     * @param string $level Error level (e.g., 'error', 'warning', 'info')
     * @param array $context Additional context for the error
     * @return void
     */
    public function logError($message, $level = 'error', array $context = [])
    {
        // Format the log message
        $formattedMessage = $this->formatLogMessage($message, $level, $context);
        
        // Determine log destination
        if (file_exists($this->logFile) && is_writable($this->logFile)) {
            // Write to application log file
            file_put_contents($this->logFile, $formattedMessage, FILE_APPEND);
        } else {
            // Fallback to PHP's default error log
            error_log($formattedMessage);
        }
    }
    
    /**
     * Display appropriate error messages to users
     *
     * @param string $message Error message
     * @param int $statusCode HTTP status code
     * @param bool $isApiRequest Whether this is an API request
     * @return void
     */
    public function displayError($message, $statusCode = 500, $isApiRequest = false)
    {
        if ($isApiRequest) {
            // For API requests, return a JSON response
            $this->sendJsonError($message, $statusCode);
        } else {
            // For web requests, use the ErrorController to display an appropriate error page
            $this->displayErrorPage($message, $statusCode);
        }
    }
    
    /**
     * Map PHP error level to a log level
     *
     * @param int $level PHP error level
     * @return string Log level
     */
    private function mapErrorLevel($level)
    {
        return isset($this->errorLevelMap[$level]) ? $this->errorLevelMap[$level] : 'error';
    }
    
    /**
     * Format a log message with timestamp, level, and context
     *
     * @param string $message Error message
     * @param string $level Error level
     * @param array $context Additional context
     * @return string Formatted log message
     */
    private function formatLogMessage($message, $level, array $context = [])
    {
        // Get current timestamp
        $timestamp = date('Y-m-d H:i:s');
        
        // Format base message
        $logMessage = "[$timestamp] [$level] $message";
        
        // Add context if available
        if (!empty($context)) {
            // Format file and line information if available
            if (isset($context['file']) && isset($context['line'])) {
                $logMessage .= " in {$context['file']} on line {$context['line']}";
            }
            
            // Add trace if available
            if (isset($context['trace'])) {
                $logMessage .= "\nStack trace:\n{$context['trace']}";
            }
            
            // Add other context as JSON
            $contextWithoutTrace = $context;
            unset($contextWithoutTrace['trace']);
            
            if (!empty($contextWithoutTrace)) {
                $logMessage .= "\nContext: " . json_encode($contextWithoutTrace);
            }
        }
        
        return $logMessage . "\n\n";
    }
    
    /**
     * Send a JSON error response
     *
     * @param string $message Error message
     * @param int $statusCode HTTP status code
     * @return void
     */
    private function sendJsonError($message, $statusCode)
    {
        // Set HTTP status code
        http_response_code($statusCode);
        
        // Create JSON response
        $response = [
            'success' => false,
            'message' => $message,
            'error_code' => $statusCode
        ];
        
        // Set content type header
        header('Content-Type: application/json');
        
        // Output JSON response
        echo json_encode($response);
        exit;
    }
    
    /**
     * Display an error page using the ErrorController
     *
     * @param string $message Error message
     * @param int $statusCode HTTP status code
     * @return void
     */
    private function displayErrorPage($message, $statusCode)
    {
        switch ($statusCode) {
            case 400:
                $this->errorController->badRequest($message);
                break;
            case 401:
                $this->errorController->unauthorized($message);
                break;
            case 403:
                $this->errorController->forbidden($message);
                break;
            case 404:
                $this->errorController->notFound();
                break;
            default:
                $this->errorController->serverError($message);
                break;
        }
    }
    
    /**
     * Check if the current request is an API request
     *
     * @return bool Whether the current request is an API request
     */
    private function isApiRequest()
    {
        return 
            (isset($_SERVER['HTTP_X_REQUESTED_WITH']) && 
             strtolower($_SERVER['HTTP_X_REQUESTED_WITH']) === 'xmlhttprequest') ||
            (isset($_GET['format']) && $_GET['format'] === 'json') ||
            (isset($_POST['format']) && $_POST['format'] === 'json') ||
            (strpos($_SERVER['REQUEST_URI'] ?? '', '/api/') !== false);
    }
    
    /**
     * Ensure the log directory exists and is writable
     *
     * @return void
     */
    private function ensureLogDirectoryExists()
    {
        $logDir = dirname($this->logFile);
        
        if (!file_exists($logDir)) {
            mkdir($logDir, 0755, true);
        }
    }
}