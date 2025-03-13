<?php

namespace App\Controllers;

use App\Middleware\MiddlewareInterface;
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
class Controller
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
     * View data
     */
    protected $viewData = [];
    
    /**
     * Layout to use for rendering
     */
    protected $layout = 'app';
    
    /**
     * Authentication service
     */
    protected $authService = null;
    
    /**
     * Response formatter service
     */
    protected $responseFormatter = null;
    
    /**
     * Middleware stack
     */
    protected $middleware = [];
    
    /**
     * Service registry for injected services
     */
    protected $services = [];
    
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
        
        // Initialize view data with common variables
        $this->viewData['errors'] = [];
        $this->viewData['success'] = false;
        $this->viewData['message'] = '';
        $this->viewData['layout'] = $this->layout;
        
        // Initialize the auth service with dependency injection
        $this->authService = $authService;
        
        // Initialize the response formatter with dependency injection
        $this->responseFormatter = $responseFormatter;
        
        // Store the provided services in the services registry
        $this->services = $services;
        
        // Process service injections
        foreach ($services as $name => $service) {
            if (is_string($name)) {
                // Named services are assigned directly to properties
                $this->$name = $service;
            } elseif (is_object($service)) {
                // For unnamed services, use the class/interface name
                $className = $this->getServiceClassName($service);
                if ($className) {
                    $propertyName = $this->getPropertyNameFromClass($className);
                    $this->$propertyName = $service;
                    $this->services[$className] = $service;
                }
            }
        }
        
        // Fallback for response formatter if not injected
        if ($this->responseFormatter === null) {
            // Try to get the response formatter from services
            $this->responseFormatter = $this->getService('App\\Services\\Interfaces\\ResponseFormatterInterface');
            
            // If still null, try ServiceFactory
            if ($this->responseFormatter === null && class_exists(ServiceFactory::class)) {
                try {
                    if (method_exists(ServiceFactory::class, 'createResponseFormatter')) {
                        $this->responseFormatter = ServiceFactory::createResponseFormatter();
                    }
                } catch (\Exception $e) {
                    // Log error and continue
                    error_log("Error creating response formatter: " . $e->getMessage());
                }
            }
        }
        
        // Fallback for auth service if not injected
        if ($this->authService === null) {
            // Try ServiceFactory first
            if (class_exists(ServiceFactory::class)) {
                try {
                    $this->authService = ServiceFactory::createAuthService();
                } catch (\Exception $e) {
                    // Log error and continue
                    error_log("Error creating auth service: " . $e->getMessage());
                }
            }
            
            // If still null, try singleton pattern for backward compatibility
            if ($this->authService === null && class_exists('Services\\AuthService')) {
                $this->authService = \Services\AuthService::getInstance();
            }
        }
    }
    
    /**
     * Get a service by class or interface name
     * 
     * @param string $className The class or interface name
     * @return object|null The service instance or null if not found
     */
    protected function getService($className)
    {
        // Check if service is in the registry
        if (isset($this->services[$className])) {
            return $this->services[$className];
        }
        
        // Look for services that implement the interface or extend the class
        foreach ($this->services as $service) {
            if (is_object($service) && ($service instanceof $className || is_a($service, $className))) {
                return $service;
            }
        }
        
        // Try ServiceFactory as fallback
        if (class_exists(ServiceFactory::class)) {
            $methodName = 'create' . $this->getServiceNameFromClass($className);
            if (method_exists(ServiceFactory::class, $methodName)) {
                try {
                    return call_user_func([ServiceFactory::class, $methodName]);
                } catch (\Exception $e) {
                    error_log("Error creating service {$className}: " . $e->getMessage());
                }
            }
        }
        
        return null;
    }
    
    /**
     * Get service class or interface name
     * 
     * @param object $service Service instance
     * @return string|null Class name or null if not determinable
     */
    private function getServiceClassName($service)
    {
        // Get the interfaces the service implements
        $interfaces = class_implements($service);
        
        // Prefer interfaces from App\Services\Interfaces namespace
        foreach ($interfaces as $interface) {
            if (strpos($interface, 'App\\Services\\Interfaces\\') === 0) {
                return $interface;
            }
        }
        
        // If no App\Services\Interfaces found, use the first interface
        if (!empty($interfaces)) {
            return reset($interfaces);
        }
        
        // Fall back to class name
        return get_class($service);
    }
    
    /**
     * Convert class name to property name
     * 
     * @param string $className Full class name
     * @return string Property name
     */
    private function getPropertyNameFromClass($className)
    {
        // Extract the class/interface name without namespace
        $parts = explode('\\', $className);
        $name = end($parts);
        
        // Remove 'Interface' suffix if present
        $name = str_replace('Interface', '', $name);
        
        // Convert to camelCase
        return lcfirst($name);
    }
    
    /**
     * Convert class name to service name for factory method
     * 
     * @param string $className Full class name
     * @return string Service name
     */
    private function getServiceNameFromClass($className)
    {
        // Extract the class/interface name without namespace
        $parts = explode('\\', $className);
        $name = end($parts);
        
        // Remove 'Interface' suffix if present
        return str_replace('Interface', '', $name);
    }
    
    /**
     * Apply middleware to the controller
     * 
     * @param MiddlewareInterface $middleware Middleware to apply
     * @return $this For method chaining
     */
    public function middleware(MiddlewareInterface $middleware)
    {
        $this->middleware[] = $middleware;
        return $this;
    }
    
    /**
     * Apply multiple middleware at once
     * 
     * @param array $middlewareStack Array of middleware instances
     * @return $this For method chaining
     */
    public function withMiddleware(array $middlewareStack)
    {
        foreach ($middlewareStack as $middleware) {
            if ($middleware instanceof MiddlewareInterface) {
                $this->middleware[] = $middleware;
            }
        }
        return $this;
    }
    
    /**
     * Execute middleware stack
     * 
     * @param string $action The action being executed
     * @return bool True if middleware allows action, false otherwise
     */
    protected function executeMiddleware($action)
    {
        $request = $this->request;
        $next = function ($request) {
            return true;
        };
        
        // Execute middleware in reverse order (last added, first executed)
        for ($i = count($this->middleware) - 1; $i >= 0; $i--) {
            $current = $this->middleware[$i];
            $nextMiddleware = $next;
            
            $next = function ($req) use ($current, $nextMiddleware) {
                return $current->handle($req, $nextMiddleware);
            };
        }
        
        // Execute the middleware stack
        return $next($request);
    }
    
    /**
     * Render a view with data
     * 
     * @param string $view View path relative to app/Views
     * @param array $data Additional data to pass to the view
     * @param bool $return Whether to return the view content or output it
     * @return mixed View content if $return is true, void otherwise
     */
    protected function render($view, $data = [], $return = false)
    {
        // Combine controller view data with the data passed to this method
        $viewData = array_merge($this->viewData, $data);
        
        // Extract variables from the data array
        extract($viewData);
        
        // Define the full path to the view file
        $viewPath = __DIR__ . '/../Views/' . $view . '.php';
        
        // For backward compatibility, if view doesn't exist in the new location,
        // try to find it in the old location
        if (!file_exists($viewPath)) {
            $viewPath = __DIR__ . '/../../' . $view . '.php';
        }
        
        // If the view still doesn't exist, throw an exception
        if (!file_exists($viewPath)) {
            throw new \Exception("View {$view} not found");
        }
        
        // Start output buffering
        ob_start();
        
        // Include the view
        include $viewPath;
        
        // Get the contents of the output buffer
        $content = ob_get_clean();
        
        // Return or output the content
        if ($return) {
            return $content;
        }
        
        echo $content;
    }
    
    /**
     * Get a value from the request
     * 
     * @param string $key The key to get
     * @param mixed $default The default value if the key doesn't exist
     * @return mixed The value
     */
    protected function input($key, $default = null)
    {
        return $this->request[$key] ?? $default;
    }
    
    /**
     * Check if the request has a key
     * 
     * @param string $key The key to check
     * @return bool True if the key exists, false otherwise
     */
    protected function has($key)
    {
        return isset($this->request[$key]);
    }
    
    /**
     * Validate request data against validation rules
     * 
     * @param array $rules Validation rules
     * @param array $data Data to validate (defaults to request data)
     * @return array|bool Array of errors or true if valid
     */
    protected function validateRequest(array $rules, array $data = null)
    {
        // If no data provided, use request data
        if ($data === null) {
            $data = $this->request;
        }
        
        $errors = [];
        
        foreach ($rules as $field => $fieldRules) {
            $fieldRules = explode('|', $fieldRules);
            
            foreach ($fieldRules as $rule) {
                $params = [];
                
                // Check if rule has parameters
                if (strpos($rule, ':') !== false) {
                    list($rule, $paramStr) = explode(':', $rule, 2);
                    $params = explode(',', $paramStr);
                }
                
                // Apply validation rule
                $error = $this->applyValidationRule($rule, $field, $data[$field] ?? null, $params);
                
                if ($error !== true) {
                    if (!isset($errors[$field])) {
                        $errors[$field] = [];
                    }
                    $errors[$field][] = $error;
                }
            }
        }
        
        return empty($errors) ? true : $errors;
    }
    
    /**
     * Apply a validation rule to a field
     * 
     * @param string $rule Validation rule
     * @param string $field Field name
     * @param mixed $value Field value
     * @param array $params Rule parameters
     * @return string|bool True if valid, error message otherwise
     */
    protected function applyValidationRule($rule, $field, $value, array $params = [])
    {
        switch ($rule) {
            case 'required':
                return $value !== null && $value !== '' ? true : "The {$field} field is required.";
                
            case 'email':
                return filter_var($value, FILTER_VALIDATE_EMAIL) !== false ? true : "The {$field} must be a valid email address.";
                
            case 'numeric':
                return is_numeric($value) ? true : "The {$field} must be a number.";
                
            case 'min':
                $min = $params[0] ?? 0;
                return strlen($value) >= $min ? true : "The {$field} must be at least {$min} characters.";
                
            case 'max':
                $max = $params[0] ?? 255;
                return strlen($value) <= $max ? true : "The {$field} may not be greater than {$max} characters.";
                
            default:
                return true;
        }
    }
    
    /**
     * Validate request and return JSON response if validation fails
     * 
     * @param array $rules Validation rules
     * @param array $data Data to validate (defaults to request data)
     * @return bool True if validation passes, exits with JSON response otherwise
     */
    protected function validateJsonRequest(array $rules, array $data = null)
    {
        $result = $this->validateRequest($rules, $data);
        
        if ($result !== true) {
            $this->jsonError('Validation failed', $result, self::HTTP_UNPROCESSABLE_ENTITY);
            return false;
        }
        
        return true;
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
     * Set a value in the view data
     * 
     * @param string $key The key to set
     * @param mixed $value The value to set
     * @return $this
     */
    protected function with($key, $value)
    {
        $this->viewData[$key] = $value;
        return $this;
    }
    
    /**
     * Set an error message in the view data
     * 
     * @param string $error The error message
     * @return $this
     */
    protected function withError($error)
    {
        $this->viewData['errors'][] = $error;
        return $this;
    }
    
    /**
     * Set errors from a model in the view data
     *
     * @param object $model Model with getErrors() method
     * @return $this
     */
    protected function withModelErrors($model)
    {
        if (method_exists($model, 'getErrors')) {
            $formattedErrors = $this->formatModelErrors($model);
            
            foreach ($formattedErrors as $field => $errors) {
                foreach ($errors as $error) {
                    $this->withError($error);
                }
            }
        }
        return $this;
    }
    
    /**
     * Set a success message in the view data
     * 
     * @param string $message The success message
     * @return $this
     */
    protected function withSuccess($message)
    {
        $this->viewData['success'] = true;
        $this->viewData['message'] = $message;
        return $this;
    }
    
    /**
     * Return a JSON response
     * 
     * @param array $data The data to return
     * @param int $statusCode HTTP status code
     * @return void
     */
    protected function json($data, $statusCode = self::HTTP_OK)
    {
        // Use ResponseFormatter if available
        if ($this->responseFormatter !== null) {
            $this->responseFormatter->json($data, $statusCode);
        } else {
            // Legacy implementation for backward compatibility
            http_response_code($statusCode);
            header('Content-Type: application/json');
            echo json_encode($data);
            exit;
        }
    }
    
    /**
     * Return a JSON success response
     * 
     * @param string $message Success message
     * @param array $data Additional data
     * @param string|null $redirect Redirect URL
     * @param int $statusCode HTTP status code (default: 200)
     * @return void
     */
    protected function jsonSuccess($message, $data = [], $redirect = null, $statusCode = self::HTTP_OK)
    {
        // Use ResponseFormatter if available
        if ($this->responseFormatter !== null) {
            $this->responseFormatter->jsonSuccess($message, $data, $redirect);
        } else {
            // Legacy implementation for backward compatibility
            $response = [
                'success' => true,
                'message' => $message,
                'data' => $data
            ];
            
            if ($redirect) {
                $response['redirect'] = $redirect;
            }
            
            $this->json($response, $statusCode);
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
    
    /**
     * Return a created response (HTTP 201)
     * 
     * @param string $message Success message
     * @param array $data The created resource data
     * @param string|null $location Resource location URI
     * @return void
     */
    protected function jsonCreated($message, $data = [], $location = null)
    {
        // Add Location header if provided
        if ($location !== null) {
            header('Location: ' . $location);
        }
        
        $this->jsonSuccess($message, $data, null, self::HTTP_CREATED);
    }
    
    /**
     * Return a no content response (HTTP 204)
     * 
     * @return void
     */
    protected function jsonNoContent()
    {
        if ($this->responseFormatter !== null) {
            $this->responseFormatter->json([], self::HTTP_NO_CONTENT);
        } else {
            http_response_code(self::HTTP_NO_CONTENT);
            exit;
        }
    }
    
    /**
     * Return an accepted response (HTTP 202)
     * 
     * @param string $message Success message
     * @param array $data Additional data
     * @return void
     */
    protected function jsonAccepted($message, $data = [])
    {
        $this->jsonSuccess($message, $data, null, self::HTTP_ACCEPTED);
    }
    
    /**
     * Return a forbidden response (HTTP 403)
     * 
     * @param string $message Error message
     * @param array $errors Additional errors
     * @return void
     */
    protected function jsonForbidden($message, $errors = [])
    {
        $this->jsonError($message, $errors, self::HTTP_FORBIDDEN, self::ERROR_AUTHORIZATION);
    }
    
    /**
     * Return a not found response (HTTP 404)
     * 
     * @param string $message Error message
     * @param array $errors Additional errors
     * @return void
     */
    protected function jsonNotFound($message, $errors = [])
    {
        $this->jsonError($message, $errors, self::HTTP_NOT_FOUND, self::ERROR_NOT_FOUND);
    }
    
    /**
     * Return a validation error response (HTTP 422)
     * 
     * @param string $message Error message
     * @param array $errors Validation errors
     * @return void
     */
    protected function jsonValidationError($message, $errors = [])
    {
        $this->jsonError($message, $errors, self::HTTP_UNPROCESSABLE_ENTITY, self::ERROR_VALIDATION);
    }
    
    /**
     * Create a unified response that works for both API and web requests
     * 
     * @param string $view View to render for web requests
     * @param array $data Data for both API and web responses
     * @param string $successMessage Success message
     * @param string|null $redirectUrl URL to redirect to after web request
     * @return void
     */
    protected function respond($view, array $data = [], $successMessage = '', $redirectUrl = null)
    {
        // Use ResponseFormatter if available
        if ($this->responseFormatter !== null) {
            $this->responseFormatter->respond($view, $data, $successMessage, $redirectUrl);
        } else {
            // Legacy implementation for backward compatibility
            if ($this->isApiRequest()) {
                $this->jsonSuccess($successMessage, $data, $redirectUrl);
            } else {
                if (!empty($successMessage)) {
                    $this->withSuccess($successMessage);
                }
                
                if ($redirectUrl) {
                    $this->redirect($redirectUrl);
                } else {
                    $this->render($view, $data);
                }
            }
        }
    }
    
    /**
     * Create a unified error response that works for both API and web requests
     * 
     * @param string $view View to render for web requests
     * @param array $data Data for both API and web responses
     * @param string $errorMessage Error message
     * @param string|null $redirectUrl URL to redirect to after web request
     * @param int $statusCode HTTP status code for API requests
     * @param string|null $errorCode API error code
     * @param string $category Error category
     * @return void
     */
    protected function respondWithError(
        $view, 
        array $data = [], 
        $errorMessage = '', 
        $redirectUrl = null, 
        $statusCode = self::HTTP_BAD_REQUEST,
        $errorCode = null,
        $category = self::ERROR_CATEGORY_SYSTEM
    ) {
        // Use ResponseFormatter if available
        if ($this->responseFormatter !== null) {
            // Add error code to the data if provided
            $responseData = $data;
            if (!empty($errorCode)) {
                $responseData = array_merge(['error_code' => $errorCode], $data);
            }
            $this->responseFormatter->respondWithError($view, $responseData, $errorMessage, $redirectUrl, $statusCode);
        } else {
            // If there's a redirect URL, handle it directly for backwards compatibility
            if ($redirectUrl) {
                if (!empty($errorMessage)) {
                    $this->withError($errorMessage);
                }
                $this->redirect($redirectUrl);
                return;
            }
            
            // Otherwise use the standardized error handling approach
            $this->handleError(
                $errorMessage,
                $data,
                $statusCode,
                $category,
                $view
            );
        }
    }
    
    /**
     * Respond with a resource created response
     * 
     * @param string $view View to render for web requests
     * @param array $data Resource data
     * @param string $successMessage Success message
     * @param string|null $redirectUrl URL to redirect to after web request
     * @param string|null $resourceLocation Resource location URI (for API responses)
     * @return void
     */
    protected function respondWithCreated(
        $view, 
        array $data = [], 
        $successMessage = 'Resource created successfully', 
        $redirectUrl = null,
        $resourceLocation = null
    ) {
        if ($this->isApiRequest()) {
            $this->jsonCreated($successMessage, $data, $resourceLocation);
        } else {
            $this->withSuccess($successMessage);
            
            if ($redirectUrl) {
                $this->redirect($redirectUrl);
            } else {
                $this->render($view, $data);
            }
        }
    }
    
    /**
     * Respond with a resource updated response
     * 
     * @param string $view View to render for web requests
     * @param array $data Resource data
     * @param string $successMessage Success message
     * @param string|null $redirectUrl URL to redirect to after web request
     * @return void
     */
    protected function respondWithUpdated(
        $view, 
        array $data = [], 
        $successMessage = 'Resource updated successfully', 
        $redirectUrl = null
    ) {
        $this->respond($view, $data, $successMessage, $redirectUrl);
    }
    
    /**
     * Respond with a resource deleted response
     * 
     * @param string $successMessage Success message
     * @param string|null $redirectUrl URL to redirect to after web request
     * @return void
     */
    protected function respondWithDeleted(
        $successMessage = 'Resource deleted successfully', 
        $redirectUrl = null
    ) {
        if ($this->isApiRequest()) {
            $this->jsonNoContent();
        } else {
            $this->withSuccess($successMessage);
            
            if ($redirectUrl) {
                $this->redirect($redirectUrl);
            } else {
                // If no redirect URL provided, go back to previous page
                $this->redirect($_SERVER['HTTP_REFERER'] ?? '/');
            }
        }
    }
    
    /**
     * Respond with a validation error
     * 
     * @param array $errors Validation errors
     * @param string $view View to render for web requests
     * @param array $viewData Additional view data
     * @param string $errorMessage Error message
     * @return void
     */
    protected function respondWithValidationError(
        array $errors, 
        $view, 
        array $viewData = [], 
        $errorMessage = 'Validation failed'
    ) {
        if ($this->isApiRequest()) {
            $this->jsonValidationError($errorMessage, $errors);
        } else {
            foreach ($errors as $field => $fieldErrors) {
                if (is_array($fieldErrors)) {
                    foreach ($fieldErrors as $error) {
                        $this->withError($error);
                    }
                } else {
                    $this->withError($fieldErrors);
                }
            }
            
            $this->render($view, $viewData);
        }
    }
    
    /**
     * Handle validation result with appropriate response
     * 
     * @param array|bool $validationResult Result from validateRequest()
     * @param callable $successCallback Callback to execute on success
     * @param string $errorView View to render on error (for web requests)
     * @param array $errorViewData Additional data for error view
     * @return mixed Result of success callback or redirects/exits
     */
    /**
     * Handle validation result with appropriate response
     * 
     * @param array|bool $validationResult Result from validateRequest()
     * @param callable $successCallback Callback to execute on success
     * @param string $errorView View to render on error (for web requests)
     * @param array $errorViewData Additional data for error view
     * @return mixed Result of success callback or redirects/exits
     */
    protected function handleValidationResult($validationResult, callable $successCallback, $errorView = null, array $errorViewData = [])
    {
        if ($validationResult === true) {
            return $successCallback();
        }
        
        // Use the standardized error handling approach
        return $this->handleError(
            'Validation failed',
            $validationResult,
            self::HTTP_UNPROCESSABLE_ENTITY,
            self::ERROR_CATEGORY_VALIDATION,
            $errorView,
            $errorViewData
        );
    }
    
    /**
     * Handle a general error with consistent formatting and responses
     * 
     * @param string $message Error message
     * @param array $errors Additional error details
     * @param int $statusCode HTTP status code
     * @param string $category Error category
     * @param string|null $view View to render for web requests
     * @param array $viewData Additional data for the view
     * @return mixed
     */
    protected function handleError($message, array $errors = [], $statusCode = self::HTTP_BAD_REQUEST, $category = self::ERROR_CATEGORY_SYSTEM, $view = null, array $viewData = [])
    {
        // Create error data with consistent structure
        $errorData = [
            'category' => $category,
            'errors' => $errors
        ];
        
        // Determine appropriate error code
        $errorCode = $this->getErrorCodeFromStatus($statusCode);
        
        if ($this->isApiRequest()) {
            return $this->jsonError($message, $errorData, $statusCode, $errorCode);
        }
        
        // Add error message to view data
        $this->withError($message);
        
        // Add detailed errors if provided
        if (!empty($errors)) {
            foreach ($errors as $key => $error) {
                if (is_string($error)) {
                    $this->withError($error);
                } elseif (is_array($error)) {
                    foreach ($error as $err) {
                        if (is_string($err)) {
                            $this->withError($err);
                        }
                    }
                }
            }
        }
        
        // If view is provided, render it, otherwise just return
        if ($view) {
            $this->render($view, array_merge($this->viewData, $viewData));
            exit;
        }
        
        return false;
    }
    
    /**
     * Handle a resource not found error (404)
     * 
     * @param string $resource Type of resource that was not found
     * @param string|null $identifier Identifier that was used to look up the resource
     * @param string|null $view View to render for web requests
     * @param array $viewData Additional data for the view
     * @return mixed
     */
    protected function handleNotFoundException($resource = 'Resource', $identifier = null, $view = 'error/404', array $viewData = [])
    {
        $message = $identifier 
                ? "{$resource} with identifier '{$identifier}' not found" 
                : "{$resource} not found";
        
        return $this->handleError(
            $message, 
            [], 
            self::HTTP_NOT_FOUND, 
            self::ERROR_CATEGORY_RESOURCE,
            $view,
            $viewData
        );
    }
    
    /**
     * Handle an access denied error (403)
     * 
     * @param string $message Custom error message
     * @param string|null $view View to render for web requests
     * @param array $viewData Additional data for the view
     * @return mixed
     */
    protected function handleAccessDeniedException($message = 'Access denied', $view = 'error/403', array $viewData = [])
    {
        return $this->handleError(
            $message,
            [],
            self::HTTP_FORBIDDEN,
            self::ERROR_CATEGORY_AUTHORIZATION,
            $view,
            $viewData
        );
    }
    
    /**
     * Handle a server error (500)
     * 
     * @param string $message Error message
     * @param \Exception|null $exception The exception that was thrown
     * @param string|null $view View to render for web requests
     * @param array $viewData Additional data for the view
     * @return mixed
     */
    protected function handleServerException($message = 'Internal server error', \Exception $exception = null, $view = 'error/500', array $viewData = [])
    {
        // Log the error
        if ($exception) {
            error_log($exception->getMessage() . ' in ' . $exception->getFile() . ' on line ' . $exception->getLine());
            error_log($exception->getTraceAsString());
        } else {
            error_log($message);
        }
        
        $errors = [];
        if ($exception && defined('DEBUG_MODE') && DEBUG_MODE === true) {
            $errors['exception'] = [
                'message' => $exception->getMessage(),
                'file' => $exception->getFile(),
                'line' => $exception->getLine(),
                'trace' => $exception->getTraceAsString()
            ];
        }
        
        return $this->handleError(
            $message,
            $errors,
            self::HTTP_INTERNAL_SERVER_ERROR,
            self::ERROR_CATEGORY_SERVER,
            $view,
            $viewData
        );
    }
    
    /**
     * Handle validation errors from models
     *
     * @param object $model Model with validation errors
     * @param string|null $view View to render for web requests
     * @param array $viewData Additional data for the view
     * @return mixed
     */
    protected function handleModelValidationErrors($model, $view = null, array $viewData = [])
    {
        $errors = [];
        
        // Check if the model has a getErrors method
        if (method_exists($model, 'getErrors')) {
            $errors = $this->formatModelErrors($model);
        }
        
        return $this->handleError(
            'Validation failed',
            $errors,
            self::HTTP_UNPROCESSABLE_ENTITY,
            self::ERROR_CATEGORY_VALIDATION,
            $view,
            $viewData
        );
    }
    
    /**
     * Validate a model instance
     * 
     * @param mixed $model Model instance implementing ValidationTrait
     * @return bool True if valid, false otherwise
     * @throws \InvalidArgumentException If model doesn't have validate() method
     */
    protected function validateModel($model)
    {
        if (!method_exists($model, 'validate')) {
            throw new \InvalidArgumentException('Model must implement ValidationTrait or have a validate() method');
        }
        
        return $model->validate();
    }
    
    /**
     * Format model validation errors into a standardized format
     * 
     * @param mixed $model Model instance with validation errors
     * @return array Standardized error array
     * @throws \InvalidArgumentException If model doesn't have getErrors() method
     */
    protected function formatModelErrors($model)
    {
        if (!method_exists($model, 'getErrors')) {
            throw new \InvalidArgumentException('Model must implement ValidationTrait or have a getErrors() method');
        }
        
        $modelErrors = $model->getErrors();
        $formattedErrors = [];
        
        foreach ($modelErrors as $field => $errors) {
            if (!is_array($errors)) {
                $errors = [$errors];
            }
            
            if (!isset($formattedErrors[$field])) {
                $formattedErrors[$field] = [];
            }
            
            $formattedErrors[$field] = array_merge($formattedErrors[$field], $errors);
        }
        
        return $formattedErrors;
    }
    
    /**
     * Handle model validation with appropriate response
     * 
     * @param mixed $model Model instance to validate
     * @param callable $successCallback Callback to execute on successful validation
     * @param string $errorView View to render on error (for web requests)
     * @param array $errorViewData Additional data for error view
     * @return mixed Result of success callback or redirects/exits
     */
    protected function handleModelValidation($model, callable $successCallback, $errorView = null, array $errorViewData = [])
    {
        if ($this->validateModel($model)) {
            return $successCallback();
        }
        
        // Use the standardized model validation error handling
        return $this->handleModelValidationErrors($model, $errorView, $errorViewData);
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
        
        // For backward compatibility, use the existing auth functions
        if (function_exists('getCurrentUser')) {
            return getCurrentUser();
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
        
        // For backward compatibility, use the existing auth functions
        if (function_exists('isAuthenticated')) {
            return isAuthenticated();
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
     * Set the layout for rendering
     * 
     * @param string $layout Layout name
     * @return $this
     */
    protected function setLayout($layout)
    {
        $this->layout = $layout;
        $this->viewData['layout'] = $layout;
        return $this;
    }
    
    /**
     * Method to allow prototyping and extending methods from the bootstrap file
     * 
     * @param string $method Method name
     * @param callable $callback Callback function
     * @return void
     */
    public static function prototype($method, $callback)
    {
        // This is a placeholder for the bootstrap file to extend methods
        // The actual implementation will be handled in the bootstrap.php file
    }
}