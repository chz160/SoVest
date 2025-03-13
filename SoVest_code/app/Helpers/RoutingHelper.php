<?php

namespace App\Helpers;

/**
 * RoutingHelper Class
 * 
 * Provides methods for URL generation from named routes.
 * Works with the enhanced Router implementation that supports named routes,
 * route groups, and other advanced features.
 */
class RoutingHelper
{
    /**
     * Base URL for absolute URL generation
     *
     * @var string
     */
    protected $baseUrl;
    
    /**
     * Routes configuration
     *
     * @var array
     */
    protected $routes;
    
    /**
     * Named route mapping
     *
     * @var array
     */
    protected $namedRoutes = [];
    
    /**
     * Constructor
     *
     * @param string $baseUrl Base URL for absolute URL generation
     */
    public function __construct(string $baseUrl = '')
    {
        $this->baseUrl = $baseUrl ?: $this->detectBaseUrl();
        $this->routes = $this->loadRoutes();
        $this->processRoutes();
    }
    
    /**
     * Detect the base URL
     *
     * @return string
     */
    protected function detectBaseUrl()
    {
        if (isset($_SERVER['HTTP_HOST'])) {
            $protocol = isset($_SERVER['HTTPS']) && $_SERVER['HTTPS'] !== 'off' ? 'https' : 'http';
            return $protocol . '://' . $_SERVER['HTTP_HOST'];
        }
        
        return '';
    }
    
    /**
     * Load routes configuration
     *
     * @return array
     */
    protected function loadRoutes()
    {
        // Load the routes directly from the file
        if (defined('APP_BASE_PATH')) {
            $routesPath = APP_BASE_PATH . '/app/Routes/routes.php';
        } else {
            $routesPath = __DIR__ . '/../../Routes/routes.php';
        }
        
        if (file_exists($routesPath)) {
            return require $routesPath;
        }
        
        return [];
    }
    
    /**
     * Process routes to build named route mapping
     * 
     * @param array $routes Routes to process
     * @param string $prefix Current prefix for the route group
     * @param array $middlewares Current middlewares for the route group
     * @param string $namespace Current namespace for the route group
     * @return void
     */
    protected function processRoutes($routes = null, $prefix = '', $middlewares = [], $namespace = '')
    {
        if ($routes === null) {
            $routes = $this->routes;
        }
        
        foreach ($routes as $path => $route) {
            // Skip non-route entries (like 404, etc.)
            if (!is_string($path) && !isset($route['type'])) {
                continue;
            }
            
            // Handle route groups
            if (isset($route['type']) && $route['type'] === 'group') {
                $newPrefix = $prefix . ($route['prefix'] ?? '');
                $newMiddlewares = array_merge($middlewares, $route['middleware'] ?? []);
                $newNamespace = $namespace . ($route['namespace'] ?? '');
                
                if (isset($route['routes'])) {
                    $this->processRoutes($route['routes'], $newPrefix, $newMiddlewares, $newNamespace);
                }
                
                continue;
            }
            
            // Handle regular routes
            if (is_string($path)) {
                // Skip error routes like 404, 500, etc.
                if (is_numeric($path)) {
                    continue;
                }
                
                $fullPath = $prefix . $path;
                
                // Handle named routes
                if (is_array($route) && isset($route['name'])) {
                    $this->namedRoutes[$route['name']] = $fullPath;
                }
                
                // Also map controller/action combinations for legacy support
                if (isset($route['controller'], $route['action'])) {
                    $controllerName = $namespace . $route['controller'];
                    $actionName = $route['action'];
                    
                    // Create a controller.action style key
                    $key = strtolower(str_replace('Controller', '', $controllerName)) . '.' . $actionName;
                    
                    // Only set if it doesn't already exist (to avoid overwriting more specific routes)
                    if (!isset($this->namedRoutes[$key])) {
                        $this->namedRoutes[$key] = $fullPath;
                    }
                }
            }
        }
    }
    
    /**
     * Generate a URL from a named route
     *
     * @param string $name Route name
     * @param array $parameters Route parameters
     * @param bool $absolute Whether to generate an absolute URL
     * @return string
     */
    public function url($name, array $parameters = [], bool $absolute = false)
    {
        // Check if route exists by name
        if (!isset($this->namedRoutes[$name])) {
            // Legacy fallback: try controller.action format
            $parts = explode('.', $name);
            
            if (count($parts) === 2) {
                $controllerName = ucfirst($parts[0]) . 'Controller';
                $actionName = $parts[1];
                
                return $this->action($controllerName, $actionName, $parameters, $absolute);
            }
            
            return '';
        }
        
        $pattern = $this->namedRoutes[$name];
        
        // Replace the parameters in the route pattern
        $url = $this->replaceParameters($pattern, $parameters);
        
        // Generate the full URL if absolute is set
        if ($absolute) {
            return $this->baseUrl . $url;
        }
        
        return $url;
    }
    
    /**
     * Generate a URL for a controller and action
     *
     * @param string $controller Controller name
     * @param string $action Action name
     * @param array $parameters Route parameters
     * @param bool $absolute Whether to generate an absolute URL
     * @return string
     */
    public function action($controller, $action, array $parameters = [], bool $absolute = false)
    {
        // Try to find the route by controller and action
        $controllerKey = strtolower(str_replace('Controller', '', $controller)) . '.' . $action;
        
        if (isset($this->namedRoutes[$controllerKey])) {
            return $this->url($controllerKey, $parameters, $absolute);
        }
        
        // Legacy fallback - look for direct pattern in routes array
        foreach ($this->routes as $pattern => $route) {
            if (is_string($pattern) && 
                isset($route['controller'], $route['action']) &&
                $route['controller'] === $controller &&
                $route['action'] === $action) {
                
                $url = $this->replaceParameters($pattern, $parameters);
                
                if ($absolute) {
                    return $this->baseUrl . $url;
                }
                
                return $url;
            }
        }
        
        return '';
    }
    
    /**
     * Replace parameters in a route pattern
     *
     * @param string $pattern Route pattern
     * @param array $parameters Route parameters
     * @return string
     */
    protected function replaceParameters($pattern, array $parameters)
    {
        // Replace each parameter in the pattern
        foreach ($parameters as $name => $value) {
            $pattern = str_replace(':' . $name, $value, $pattern);
        }
        
        return $pattern;
    }
    
    /**
     * Get all named routes
     *
     * @return array
     */
    public function getNamedRoutes()
    {
        return $this->namedRoutes;
    }
}