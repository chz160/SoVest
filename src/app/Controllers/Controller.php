<?php

namespace App\Controllers;

use Illuminate\Support\Facades\Auth;

use App\Middleware\MiddlewareInterface;
use App\Services\AuthService;
use App\Services\Interfaces\AuthServiceInterface;
use App\Services\Interfaces\ResponseFormatterInterface;
use App\Services\ServiceFactory;

/**
 * Base Controller Class
 * 
 * This class serves as the foundation for all controllers in the SoVest application.
 * It provides common functionality for rendering views, accessing request data,
 * validating input, and interacting with models.
 */
abstract class Controller
{
    /**
     * Standard HTTP status codes
     */
    const HTTP_OK = 200;
    const HTTP_CREATED = 201;
    const HTTP_ACCEPTED = 202;
    const HTTP_NO_CONTENT = 204;
    const HTTP_MOVED_PERMANENTLY = 301;
    const HTTP_FOUND = 302;
    const HTTP_SEE_OTHER = 303;
    const HTTP_NOT_MODIFIED = 304;
    const HTTP_TEMPORARY_REDIRECT = 307;
    const HTTP_PERMANENT_REDIRECT = 308;
    const HTTP_BAD_REQUEST = 400;
    const HTTP_UNAUTHORIZED = 401;
    const HTTP_PAYMENT_REQUIRED = 402;
    const HTTP_FORBIDDEN = 403;
    const HTTP_NOT_FOUND = 404;
    const HTTP_METHOD_NOT_ALLOWED = 405;
    const HTTP_NOT_ACCEPTABLE = 406;
    const HTTP_REQUEST_TIMEOUT = 408;
    const HTTP_CONFLICT = 409;
    const HTTP_GONE = 410;
    const HTTP_UNPROCESSABLE_ENTITY = 422;
    const HTTP_TOO_MANY_REQUESTS = 429;
    const HTTP_INTERNAL_SERVER_ERROR = 500;
    const HTTP_NOT_IMPLEMENTED = 501;
    const HTTP_BAD_GATEWAY = 502;
    const HTTP_SERVICE_UNAVAILABLE = 503;
    
    /**
     * Standard API error codes
     */
    const ERROR_VALIDATION = 'validation_error';
    const ERROR_AUTHENTICATION = 'authentication_error';
    const ERROR_AUTHORIZATION = 'authorization_error';
    const ERROR_NOT_FOUND = 'resource_not_found';
    const ERROR_SERVER = 'server_error';
    const ERROR_INVALID_REQUEST = 'invalid_request';
    const ERROR_RATE_LIMIT = 'rate_limit_exceeded';
    
    /**
     * Error categories
     */
    const ERROR_CATEGORY_VALIDATION = 'validation';
    const ERROR_CATEGORY_AUTHENTICATION = 'authentication';
    const ERROR_CATEGORY_AUTHORIZATION = 'authorization';
    const ERROR_CATEGORY_RESOURCE = 'resource';
    const ERROR_CATEGORY_SERVER = 'server';
    const ERROR_CATEGORY_DATABASE = 'database';
    const ERROR_CATEGORY_SYSTEM = 'system';
    
    /**
     * Request data
     */
    protected $request;
    
    /**
     * Authentication service
     */
    protected $authService = null;
    
    /**
     * Response formatter service
     */
    protected $responseFormatter = null;
    
    /**
     * Constructor
     * 
     * @param AuthServiceInterface|null $authService Authentication service (optional)
     * @param ResponseFormatterInterface|null $responseFormatter Response formatter service (optional)
     * @param array $services Additional services to inject
     */
    public function __construct(
        AuthServiceInterface $authService = null, 
        ResponseFormatterInterface $responseFormatter = null,
        array $services = []
    ) {
        // Combine request data from different sources
        $this->request = array_merge($_GET, $_POST, $_FILES);
               
        // Initialize the auth service with dependency injection
        $this->authService = $authService;
        
        // Initialize the response formatter with dependency injection
        $this->responseFormatter = $responseFormatter;
    }
    
    /**
     * Get the current authenticated user
     * 
     * @return array|null User data or null if not authenticated
     */
    protected function getAuthUser()
    {
        // If AuthService is available, use it
        if ($this->authService !== null) {
            return $this->authService->getCurrentUser();
        }
        
        // If the auth functions don't exist, use a simplified version
        $userId = $_COOKIE["userID"] ?? null;
        
        if (!$userId) {
            return null;
        }
        
        try {
            $user = \Database\Models\User::find($userId);
            return $user ? $user->toArray() : null;
        } catch (\Exception $e) {
            return null;
        }
    }
    
    /**
     * Check if the user is authenticated
     * 
     * @return bool True if authenticated, false otherwise
     */
    protected function isAuthenticated()
    {
        // If AuthService is available, use it
        if ($this->authService !== null) {
            return $this->authService->isAuthenticated();
        }
        
        // If the auth functions don't exist, use a simplified version
        return isset($_COOKIE["userID"]) || isset($_SESSION['user_id']);
    }
    
    /**
     * Require authentication to access a route
     * 
     * @param string $redirect URL to redirect to if not authenticated
     * @return void
     */
    protected function requireAuth($redirect = 'login.php')
    {
        // If user is not authenticated, handle accordingly
        if (!$this->isAuthenticated()) {
            if ($this->isApiRequest()) {
                $this->jsonError('Authentication required', [], self::HTTP_UNAUTHORIZED, self::ERROR_AUTHENTICATION);
            } else {
                $this->redirect($redirect);
            }
        }
    }

    /**
     * Check if the current request is an API request
     * 
     * @return bool True if the request is an API request
     */
    protected function isApiRequest()
    {
        // Use ResponseFormatter if available
        if ($this->responseFormatter !== null) {
            return $this->responseFormatter->isApiRequest();
        } else {
            // Legacy implementation for backward compatibility
            return isset($_SERVER['HTTP_X_REQUESTED_WITH']) && 
                   strtolower($_SERVER['HTTP_X_REQUESTED_WITH']) === 'xmlhttprequest' || 
                   isset($_GET['format']) && $_GET['format'] === 'json' ||
                   isset($_POST['format']) && $_POST['format'] === 'json';
        }
    }

    /**
     * Redirect to a URL
     * 
     * @param string $url The URL to redirect to
     * @param array $params Additional query parameters
     * @param int $statusCode HTTP status code (default: 302)
     * @return void
     */
    protected function redirect($url, $params = [], $statusCode = self::HTTP_FOUND)
    {
        // If ResponseFormatter is available, use it
        if ($this->responseFormatter !== null) {
            $flash = [
                'success' => $this->viewData['success'],
                'message' => $this->viewData['message'],
                'errors' => $this->viewData['errors']
            ];
            $this->responseFormatter->redirect($url, $statusCode, $flash);
        } else {
            // Legacy implementation for backward compatibility
            // If there are parameters, add them to the URL
            if (!empty($params)) {
                $query = http_build_query($params);
                $url .= (strpos($url, '?') === false) ? '?' . $query : '&' . $query;
            }
            
            // Set the status code
            http_response_code($statusCode);
            
            // Perform the redirect
            header("Location: {$url}");
            exit;
        }
    }

    /**
     * Return a JSON error response
     * 
     * @param string $message Error message
     * @param array $errors Additional errors or categorized error data
     * @param int $statusCode HTTP status code
     * @param string $errorCode API error code
     * @return void
     */
    protected function jsonError($message, $errors = [], $statusCode = self::HTTP_BAD_REQUEST, $errorCode = null)
    {
        // Determine error code if not provided
        if ($errorCode === null) {
            $errorCode = $this->getErrorCodeFromStatus($statusCode);
        }

        // Use ResponseFormatter if available
        if ($this->responseFormatter !== null) {
            // Add error code to the errors array
            $formattedErrors = $errors;
            if (!empty($errorCode)) {
                $formattedErrors = array_merge(['code' => $errorCode], $errors);
            }
            $this->responseFormatter->jsonError($message, $formattedErrors, $statusCode);
        } else {
            // Legacy implementation for backward compatibility
            // Structure the error response in a standardized format
            $response = [
                'success' => false,
                'message' => $message,
            ];
            
            // Add error code to response if provided
            if (!empty($errorCode)) {
                $response['code'] = $errorCode;
            }
            
            // Handle standardized error structure if provided
            if (isset($errors['category']) && isset($errors['errors'])) {
                $response['error'] = $errors;
            } else {
                // For backward compatibility
                $response['errors'] = $errors;
            }
            
            $this->json($response, $statusCode);
        }
    }

    
    /**
     * Get appropriate error code from HTTP status code
     * 
     * @param int $statusCode HTTP status code
     * @return string Error code
     */
    protected function getErrorCodeFromStatus($statusCode)
    {
        switch ($statusCode) {
            case self::HTTP_UNAUTHORIZED:
                return self::ERROR_AUTHENTICATION;
            case self::HTTP_FORBIDDEN:
                return self::ERROR_AUTHORIZATION;
            case self::HTTP_NOT_FOUND:
                return self::ERROR_NOT_FOUND;
            case self::HTTP_UNPROCESSABLE_ENTITY:
                return self::ERROR_VALIDATION;
            case self::HTTP_TOO_MANY_REQUESTS:
                return self::ERROR_RATE_LIMIT;
            case self::HTTP_INTERNAL_SERVER_ERROR:
            case self::HTTP_BAD_GATEWAY:
            case self::HTTP_SERVICE_UNAVAILABLE:
                return self::ERROR_SERVER;
            default:
                return self::ERROR_INVALID_REQUEST;
        }
    }
}