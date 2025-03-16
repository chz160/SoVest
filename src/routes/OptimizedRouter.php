<?php

namespace App\Routes;

/**
 * OptimizedRouter Class
 * 
 * This class extends the Router with optimized route lookup capabilities
 * for better performance with cached routes.
 */
class OptimizedRouter extends Router
{
    /**
     * Static routes (no parameters)
     *
     * @var array
     */
    protected $staticRoutes = [];
    
    /**
     * Dynamic routes (with parameters)
     *
     * @var array
     */
    protected $dynamicRoutes = [];
    
    /**
     * Constructor
     *
     * @param array $routes Routes configuration
     * @param string $basePath Base path for the application
     * @param mixed $container Dependency injection container
     * @param bool $useCache Whether to attempt loading routes from cache
     */
    public function __construct(array $routes = [], string $basePath = '', $container = null, bool $useCache = true)
    {
        parent::__construct($routes, $basePath, $container, $useCache);
        
        // Pre-process routes for faster lookup if not loaded from cache
        if (!$this->routesLoadedFromCache) {
            $this->optimizeRoutes();
        }
    }
    
    /**
     * Load routes from cache file
     *
     * @return bool True if routes were loaded from cache, false otherwise
     */
    protected function loadRoutesFromCache()
    {
        $result = parent::loadRoutesFromCache();
        
        if ($result) {
            // Optimize the loaded routes
            $this->optimizeRoutes();
        }
        
        return $result;
    }
    
    /**
     * Optimize routes for faster lookup
     *
     * @return void
     */
    protected function optimizeRoutes()
    {
        $this->staticRoutes = [];
        $this->dynamicRoutes = [];
        
        // Categorize routes as static or dynamic based on whether they have parameters
        foreach ($this->routes as $pattern => $route) {
            // Skip non-route entries
            if (!is_string($pattern)) {
                continue;
            }
            
            // Check if route has parameters
            if (strpos($pattern, ':') !== false) {
                $this->dynamicRoutes[$pattern] = $route;
            } else {
                // Index static routes by HTTP method for faster lookup
                foreach ($route['methods'] as $method) {
                    if (!isset($this->staticRoutes[$method])) {
                        $this->staticRoutes[$method] = [];
                    }
                    $this->staticRoutes[$method][$pattern] = $route;
                }
            }
        }
    }
    
    /**
     * Find a route that matches the URI and HTTP method
     *
     * @param string $uri URI to match
     * @return array|null Route information or null if not found
     */
    protected function findRoute($uri)
    {
        // Get the current HTTP method
        $method = $_SERVER['REQUEST_METHOD'] ?? 'GET';
        
        // Remove query string from URI
        if (($pos = strpos($uri, '?')) !== false) {
            $uri = substr($uri, 0, $pos);
        }
        
        // Normalize the URI
        $uri = rtrim($uri, '/');
        if (empty($uri)) {
            $uri = '/';
        }
        
        // Fast path: Check for exact match in static routes for the current method
        if (isset($this->staticRoutes[$method]) && isset($this->staticRoutes[$method][$uri])) {
            $route = $this->staticRoutes[$method][$uri];
            return [
                'route' => $route,
                'params' => []
            ];
        }
        
        // Check for dynamic routes
        foreach ($this->dynamicRoutes as $pattern => $route) {
            // Skip if method doesn't match
            if (!in_array($method, $route['methods'])) {
                continue;
            }
            
            // Apply parameter constraints if defined
            $regexPattern = $pattern;
            
            if (!empty($route['where'])) {
                foreach ($route['where'] as $param => $constraint) {
                    // Convert type constraints to regex patterns
                    switch ($constraint) {
                        case 'int':
                        case 'integer':
                            $constraint = '[0-9]+';
                            break;
                            
                        case 'alpha':
                            $constraint = '[a-zA-Z]+';
                            break;
                            
                        case 'alphanumeric':
                        case 'alphanum':
                            $constraint = '[a-zA-Z0-9]+';
                            break;
                            
                        case 'slug':
                            $constraint = '[a-zA-Z0-9-_]+';
                            break;
                    }
                    
                    // Apply constraint to parameter
                    $regexPattern = str_replace(
                        ":{$param}",
                        "(?P<{$param}>{$constraint})",
                        $regexPattern
                    );
                }
            }
            
            // Replace remaining parameters with default pattern
            $regexPattern = preg_replace('/:([^\/]+)/', '(?P<$1>[^/]+)', $regexPattern);
            $regexPattern = "#^{$regexPattern}$#";
            
            if (preg_match($regexPattern, $uri, $matches)) {
                // Extract named parameters
                $params = [];
                foreach ($matches as $key => $value) {
                    if (is_string($key)) {
                        $params[$key] = $value;
                    }
                }
                
                
                return [
                    'route' => $route,
                    'params' => $params
                ];
            }
        }
        
        
        return null;
    }
}