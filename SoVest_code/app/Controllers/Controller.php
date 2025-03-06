<?php

namespace App\Controllers;

use Services\AuthService;

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
     * Constructor
     */
    public function __construct()
    {
        // Combine request data from different sources
        $this->request = array_merge($_GET, $_POST, $_FILES);
        
        // Initialize view data with common variables
        $this->viewData['errors'] = [];
        $this->viewData['success'] = false;
        $this->viewData['message'] = '';
        $this->viewData['layout'] = $this->layout;
        
        // Initialize AuthService if it exists
        if (class_exists('Services\AuthService')) {
            $this->authService = AuthService::getInstance();
        }
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
     * Redirect to a URL
     * 
     * @param string $url The URL to redirect to
     * @param array $params Additional query parameters
     * @return void
     */
    protected function redirect($url, $params = [])
    {
        // If there are parameters, add them to the URL
        if (!empty($params)) {
            $query = http_build_query($params);
            $url .= (strpos($url, '?') === false) ? '?' . $query : '&' . $query;
        }
        
        // Perform the redirect
        header("Location: {$url}");
        exit;
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
            $errors = $model->getErrors();
            foreach ($errors as $field => $fieldErrors) {
                foreach ($fieldErrors as $error) {
                    $this->viewData['errors'][] = $error;
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
    protected function json($data, $statusCode = 200)
    {
        http_response_code($statusCode);
        header('Content-Type: application/json');
        echo json_encode($data);
        exit;
    }
    
    /**
     * Return a JSON success response
     * 
     * @param string $message Success message
     * @param array $data Additional data
     * @param string|null $redirect Redirect URL
     * @return void
     */
    protected function jsonSuccess($message, $data = [], $redirect = null)
    {
        $response = [
            'success' => true,
            'message' => $message,
            'data' => $data
        ];
        
        if ($redirect) {
            $response['redirect'] = $redirect;
        }
        
        $this->json($response);
    }
    
    /**
     * Return a JSON error response
     * 
     * @param string $message Error message
     * @param array $errors Additional errors
     * @param int $statusCode HTTP status code
     * @return void
     */
    protected function jsonError($message, $errors = [], $statusCode = 400)
    {
        $this->json([
            'success' => false,
            'message' => $message,
            'errors' => $errors
        ], $statusCode);
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
        // If AuthService is available, use it
        if ($this->authService !== null) {
            $this->authService->requireAuthentication($redirect);
            return;
        }
        
        // Fallback to the original implementation
        if (!$this->isAuthenticated()) {
            $this->redirect($redirect);
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