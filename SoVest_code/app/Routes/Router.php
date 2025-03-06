<?php

namespace App\Routes;

/**
 * Router Class
 * 
 * This class is responsible for parsing the URL, finding the matching route,
 * and dispatching the request to the appropriate controller and action.
 */
class Router
{
    /**
     * Routes configuration
     *
     * @var array
     */
    protected $routes = [];
    
    /**
     * Base path for the application
     *
     * @var array
     */
    protected $basePath = '';
    
    /**
     * URL parameters extracted from the route
     *
     * @var array
     */
    protected $params = [];
    
    /**
     * Constructor
     *
     * @param array $routes Routes configuration
     * @param string $basePath Base path for the application
     */
    public function __construct(array $routes = [], string $basePath = '')
    {
        $this->routes = $routes;
        $this->basePath = rtrim($basePath, '/');
    }
    
    /**
     * Dispatch the request to the appropriate controller and action
     *
     * @param string|null $uri The URI to dispatch (or use current URI if null)
     * @return mixed The result of the controller action
     */
    public function dispatch($uri = null)
    {
        // Get the URI if not provided
        if ($uri === null) {
            $uri = $this->getCurrentUri();
        }
        
        // Find the matching route
        $routeInfo = $this->findRoute($uri);
        
        if (!$routeInfo) {
            return $this->handleNotFound();
        }
        
        $route = $routeInfo['route'];
        $this->params = $routeInfo['params'];
        
        // Get the controller and action
        $controllerName = $route['controller'];
        $actionName = $route['action'];
        
        // Check if it's a fully qualified controller name
        if (strpos($controllerName, '\\') === false) {
            $controllerName = "\\App\\Controllers\\{$controllerName}";
        }
        
        // Check if the controller exists
        if (!class_exists($controllerName)) {
            // For backward compatibility, try to dispatch to an existing file
            if ($this->dispatchLegacy($uri)) {
                return true;
            }
            
            return $this->handleNotFound();
        }
        
        // Create the controller
        $controller = new $controllerName();
        
        // Check if the action exists
        if (!method_exists($controller, $actionName)) {
            return $this->handleNotFound();
        }
        
        // Set request parameters from route parameters
        $_REQUEST = array_merge($_REQUEST, $this->params);
        
        // Special handling for ID parameter
        if (isset($this->params['id'])) {
            $_GET['id'] = $this->params['id'];
            $_REQUEST['id'] = $this->params['id'];
        }
        
        // Call the action
        return $controller->$actionName();
    }
    
    /**
     * Find a route that matches the given URI
     *
     * @param string $uri The URI to match
     * @return array|null The route info or null if not found
     */
    protected function findRoute($uri)
    {
        // Remove query string from URI
        if (($pos = strpos($uri, '?')) !== false) {
            $uri = substr($uri, 0, $pos);
        }
        
        // Normalize the URI
        $uri = rtrim($uri, '/');
        if (empty($uri)) {
            $uri = '/';
        }
        
        // Direct match
        if (isset($this->routes[$uri])) {
            return [
                'route' => $this->routes[$uri],
                'params' => []
            ];
        }
        
        // Check for routes with parameters
        foreach ($this->routes as $route => $target) {
            // Skip non-string routes (like 404)
            if (!is_string($route)) {
                continue;
            }
            
            // Check if this is a parameterized route
            if (strpos($route, ':') !== false) {
                $pattern = preg_replace('/:([^\/]+)/', '(?P<$1>[^/]+)', $route);
                $pattern = "#^{$pattern}$#";
                
                if (preg_match($pattern, $uri, $matches)) {
                    // Extract the parameters
                    $params = [];
                    foreach ($matches as $key => $value) {
                        if (is_string($key)) {
                            $params[$key] = $value;
                        }
                    }
                    
                    return [
                        'route' => $target,
                        'params' => $params
                    ];
                }
            }
        }
        
        // No match found
        return null;
    }
    
    /**
     * Get the current URI
     *
     * @return string The current URI
     */
    protected function getCurrentUri()
    {
        $uri = $_SERVER['REQUEST_URI'] ?? '/';
        
        // Remove the base path from the URI
        if (!empty($this->basePath) && strpos($uri, $this->basePath) === 0) {
            $uri = substr($uri, strlen($this->basePath));
        }
        
        return $uri;
    }
    
    /**
     * Handle 404 Not Found
     *
     * @return mixed The result of the 404 handler
     */
    protected function handleNotFound()
    {
        // If we have a custom 404 route, use it
        if (isset($this->routes['404'])) {
            $route = $this->routes['404'];
            $controllerName = "\\App\\Controllers\\{$route['controller']}";
            $actionName = $route['action'];
            
            if (class_exists($controllerName) && method_exists($controllerName, $actionName)) {
                $controller = new $controllerName();
                return $controller->$actionName();
            }
        }
        
        // Default 404 response
        http_response_code(404);
        echo "404 Not Found: The requested page was not found.";
        return false;
    }
    
    /**
     * Attempt to dispatch the request to a legacy PHP file for backward compatibility
     *
     * @param string $uri The URI to dispatch
     * @return bool True if dispatched successfully, false otherwise
     */
    protected function dispatchLegacy($uri)
    {
        // Normalize the URI to a file path
        $path = trim($uri, '/');
        
        // If empty, use index.php
        if (empty($path)) {
            $path = 'index.php';
        }
        // If no extension, add .php
        elseif (pathinfo($path, PATHINFO_EXTENSION) === '') {
            $path .= '.php';
        }
        
        $filepath = __DIR__ . '/../../' . $path;
        
        // Check if the file exists and is a PHP file
        if (file_exists($filepath) && pathinfo($filepath, PATHINFO_EXTENSION) === 'php') {
            // Include the file for execution
            include $filepath;
            return true;
        }
        
        return false;
    }
}