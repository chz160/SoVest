<?php

namespace App\Routes;

use App\Services\ServiceProvider;

/**
 * Router Class
 * 
 * This class is responsible for parsing the URL, finding the matching route,
 * and dispatching the request to the appropriate controller and action.
 * 
 * Enhanced with:
 * - HTTP method constraints
 * - Middleware support
 * - Route grouping
 * - Named routes and URL generation
 * - Parameter validation and type casting
 * - Route caching for improved performance
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
     * Named routes for URL generation
     *
     * @var array
     */
    protected $namedRoutes = [];
    
    /**
     * Global middleware to apply to all routes
     *
     * @var array
     */
    protected $middleware = [];
    
    /**
     * Current route group attributes
     *
     * @var array|null
     */
    protected $currentGroup = null;
    
    /**
     * Base path for the application
     *
     * @var string
     */
    protected $basePath = '';
    
    /**
     * URL parameters extracted from the route
     *
     * @var array
     */
    protected $params = [];
    
    /**
     * Dependency injection container
     *
     * @var mixed
     */
    protected $container = null;
    
    /**
     * Route cache status
     *
     * @var bool
     */
    protected $routesLoadedFromCache = false;
    
    /**
     * Performance benchmarking data
     *
     * @var array
     */
    protected $benchmark = [];
    
    /**
     * @var \App\Middleware\MiddlewareRegistry|null
     */
    protected $middlewareRegistry = null;
    
    /**
     * Constructor
     *
     * @param array $routes Routes configuration
     * @param string $basePath Base path for the application
     * @param mixed $container Dependency injection container
     * @param bool $useCache Whether to attempt loading routes from cache
     * @param \App\Middleware\MiddlewareRegistry|null $middlewareRegistry Middleware registry
     */
    public function __construct(
        array $routes = [], 
        string $basePath = '', 
        $container = null, 
        bool $useCache = true,
        \App\Middleware\MiddlewareRegistry $middlewareRegistry = null
    ) {
        $this->startBenchmark('constructor');
        
        $this->basePath = rtrim($basePath, '/');
        $this->container = $container;
        $this->middlewareRegistry = $middlewareRegistry;
        
        // Try to load routes from cache if enabled
        if ($useCache && $this->loadRoutesFromCache()) {
            $this->routesLoadedFromCache = true;
        } else {
            // Use provided routes
            $this->routes = $routes;
            
            // Convert legacy routes to new format for backward compatibility
            $this->convertLegacyRoutes();
        }
        
        $this->endBenchmark('constructor');
    }
    
    /**
     * Load routes from cache file
     *
     * @return bool True if routes were loaded from cache, false otherwise
     */
    protected function loadRoutesFromCache()
    {
        $this->startBenchmark('loadFromCache');
        
        $basePath = defined('APP_BASE_PATH') ? APP_BASE_PATH : dirname(dirname(__DIR__));
        $cacheFile = $basePath . '/bootstrap/cache/routes.php';
        
        if (!file_exists($cacheFile)) {
            $this->endBenchmark('loadFromCache', false);
            return false;
        }
        
        try {
            $cache = require $cacheFile;
            
            // Check if cache contains required data
            if (!isset($cache['routes']) || !is_array($cache['routes'])) {
                $this->endBenchmark('loadFromCache', false);
                return false;
            }
            
            // Check if source file has been modified
            if (isset($cache['_source_file'], $cache['_source_last_modified']) && file_exists($cache['_source_file'])) {
                $sourceModified = filemtime($cache['_source_file']);
                
                // If source file has been modified, don't use cache
                if ($sourceModified > $cache['_source_last_modified']) {
                    $this->endBenchmark('loadFromCache', false);
                    return false;
                }
            }
            
            // Extract named routes if available
            if (isset($cache['routes']['_named_routes'])) {
                $namedRoutes = $cache['routes']['_named_routes'];
                unset($cache['routes']['_named_routes']);
                
                // Build the named routes array
                foreach ($namedRoutes as $name => $pattern) {
                    if (isset($cache['routes'][$pattern])) {
                        $this->namedRoutes[$name] = $cache['routes'][$pattern];
                        // Add URI to the route for easier URL generation
                        $this->namedRoutes[$name]['uri'] = $pattern;
                    }
                }
            }
            
            // Load routes from cache
            $this->routes = $cache['routes'];
            
            $this->endBenchmark('loadFromCache', true);
            return true;
            
        } catch (\Exception $e) {
            error_log("Error loading routes from cache: " . $e->getMessage());
            $this->endBenchmark('loadFromCache', false);
            return false;
        }
    }
    
    /**
     * Find a route that matches the URI and HTTP method
     *
     * @param string $uri URI to match
     * @return array|null Route information or null if not found
     */
    
    /**
     * Start a benchmark timer
     *
     * @param string $key Benchmark identifier
     * @return void
     */
    protected function startBenchmark($key)
    {
        $this->benchmark[$key] = [
            'start' => microtime(true),
            'end' => null,
            'duration' => null,
            'result' => null
        ];
    }
    
    /**
     * End a benchmark timer
     *
     * @param string $key Benchmark identifier
     * @param mixed $result Optional result information
     * @return float Duration in milliseconds
     */
    protected function endBenchmark($key, $result = null)
    {
        if (isset($this->benchmark[$key])) {
            $this->benchmark[$key]['end'] = microtime(true);
            $this->benchmark[$key]['duration'] = ($this->benchmark[$key]['end'] - $this->benchmark[$key]['start']) * 1000;
            $this->benchmark[$key]['result'] = $result;
            return $this->benchmark[$key]['duration'];
        }
        return 0;
    }
    
    /**
     * Get benchmark data
     * 
     * @return array Benchmark information
     */
    public function getBenchmarkData()
    {
        return $this->benchmark;
    }
    
    /**
     * Check if routes were loaded from cache
     *
     * @return bool True if routes were loaded from cache
     */
    public function isUsingCache()
    {
        return $this->routesLoadedFromCache;
    }
    
    /**
     * Convert legacy routes to the new format with HTTP method
     *
     * @return void
     */
    protected function convertLegacyRoutes()
    {
        if (empty($this->routes)) {
            return;
        }
        
        $newRoutes = [];
        
        foreach ($this->routes as $uri => $target) {
            // Skip special routes like 404
            if (!is_string($uri)) {
                $newRoutes[$uri] = $target;
                continue;
            }
            
            // Convert legacy route to new format with HTTP method
            // For backward compatibility, assume all routes support any method
            $newRoute = [
                'uri' => $uri,
                'controller' => $target['controller'] ?? null,
                'action' => $target['action'] ?? null,
                'methods' => ['GET', 'POST', 'PUT', 'DELETE'], // Support all methods by default
                'middleware' => [],
                'where' => []
            ];
            
            // Store the route with its URI as key (for backward compatibility)
            $newRoutes[$uri] = $newRoute;
        }
        
        $this->routes = $newRoutes;
    }
    
    /**
     * Add global middleware
     *
     * @param string|array $middleware Middleware class name(s)
     * @return $this
     */
    public function middleware($middleware)
    {
        if (is_array($middleware)) {
            $this->middleware = array_merge($this->middleware, $middleware);
        } else {
            $this->middleware[] = $middleware;
        }
        
        return $this;
    }
    
    /**
     * Create a route group with shared attributes
     *
     * @param array $attributes Group attributes (prefix, middleware, where)
     * @param callable $callback Function that defines routes within the group
     * @return $this
     */
    public function group(array $attributes, callable $callback)
    {
        // Save current group
        $previousGroup = $this->currentGroup;
        
        // Merge with previous group if it exists
        if ($previousGroup !== null) {
            // Merge prefixes
            if (isset($previousGroup['prefix']) && isset($attributes['prefix'])) {
                $attributes['prefix'] = $previousGroup['prefix'] . '/' . trim($attributes['prefix'], '/');
            } elseif (isset($previousGroup['prefix'])) {
                $attributes['prefix'] = $previousGroup['prefix'];
            }
            
            // Merge middleware
            if (isset($previousGroup['middleware'])) {
                $attributes['middleware'] = array_merge(
                    $previousGroup['middleware'],
                    $attributes['middleware'] ?? []
                );
            }
            
            // Merge parameter constraints
            if (isset($previousGroup['where'])) {
                $attributes['where'] = array_merge(
                    $previousGroup['where'],
                    $attributes['where'] ?? []
                );
            }
        }
        
        // Set current group
        $this->currentGroup = $attributes;
        
        // Execute callback to define routes in this group
        call_user_func($callback, $this);
        
        // Restore previous group
        $this->currentGroup = $previousGroup;
        
        return $this;
    }
    
    /**
     * Define a GET route
     *
     * @param string $uri URI pattern
     * @param array|string $target Controller and action
     * @param string|null $name Optional route name for URL generation
     * @return $this
     */
    public function get($uri, $target, $name = null)
    {
        return $this->addRoute(['GET'], $uri, $target, $name);
    }
    
    /**
     * Define a POST route
     *
     * @param string $uri URI pattern
     * @param array|string $target Controller and action
     * @param string|null $name Optional route name for URL generation
     * @return $this
     */
    public function post($uri, $target, $name = null)
    {
        return $this->addRoute(['POST'], $uri, $target, $name);
    }
    
    /**
     * Define a PUT route
     *
     * @param string $uri URI pattern
     * @param array|string $target Controller and action
     * @param string|null $name Optional route name for URL generation
     * @return $this
     */
    public function put($uri, $target, $name = null)
    {
        return $this->addRoute(['PUT'], $uri, $target, $name);
    }
    
    /**
     * Define a DELETE route
     *
     * @param string $uri URI pattern
     * @param array|string $target Controller and action
     * @param string|null $name Optional route name for URL generation
     * @return $this
     */
    public function delete($uri, $target, $name = null)
    {
        return $this->addRoute(['DELETE'], $uri, $target, $name);
    }
    
    /**
     * Define a route that responds to multiple HTTP methods
     *
     * @param array $methods HTTP methods (GET, POST, etc.)
     * @param string $uri URI pattern
     * @param array|string $target Controller and action
     * @param string|null $name Optional route name for URL generation
     * @return $this
     */
    public function match(array $methods, $uri, $target, $name = null)
    {
        return $this->addRoute($methods, $uri, $target, $name);
    }
    
    /**
     * Define a route that responds to any HTTP method
     *
     * @param string $uri URI pattern
     * @param array|string $target Controller and action
     * @param string|null $name Optional route name for URL generation
     * @return $this
     */
    public function any($uri, $target, $name = null)
    {
        return $this->addRoute(['GET', 'POST', 'PUT', 'DELETE'], $uri, $target, $name);
    }
    
    /**
     * Add a route with specified HTTP methods
     *
     * @param array $methods HTTP methods
     * @param string $uri URI pattern
     * @param array|string $target Controller and action
     * @param string|null $name Optional route name
     * @return $this
     */
    protected function addRoute(array $methods, $uri, $target, $name = null)
    {
        // Apply group prefix if set
        if ($this->currentGroup !== null && isset($this->currentGroup['prefix'])) {
            $uri = rtrim($this->currentGroup['prefix'], '/') . '/' . ltrim($uri, '/');
        }
        
        // Parse target if it's a string with Controller@action format
        if (is_string($target) && strpos($target, '@') !== false) {
            list($controller, $action) = explode('@', $target, 2);
            $target = [
                'controller' => $controller,
                'action' => $action
            ];
        }
        
        // Create route definition
        $route = [
            'uri' => $uri,
            'controller' => is_array($target) ? $target['controller'] : $target,
            'action' => is_array($target) ? $target['action'] : 'index',
            'methods' => $methods,
            'middleware' => $this->currentGroup['middleware'] ?? [],
            'where' => $this->currentGroup['where'] ?? []
        ];
        
        // Store the route with URI for direct matching
        $this->routes[$uri] = $route;
        
        // Store named route if a name is provided
        if ($name !== null) {
            $this->namedRoutes[$name] = $route;
        }
        
        return $this;
    }
    
    /**
     * Add parameter constraints to the last defined route
     *
     * @param array $patterns Parameter pattern constraints
     * @return $this
     */
    public function where(array $patterns)
    {
        $lastRoute = end($this->routes);
        if ($lastRoute) {
            $key = key($this->routes);
            $this->routes[$key]['where'] = array_merge(
                $this->routes[$key]['where'] ?? [],
                $patterns
            );
            
            // Update named route if exists
            foreach ($this->namedRoutes as $name => $route) {
                if ($route['uri'] === $this->routes[$key]['uri']) {
                    $this->namedRoutes[$name]['where'] = $this->routes[$key]['where'];
                }
            }
        }
        
        return $this;
    }
    
    /**
     * Add middleware to the last defined route
     *
     * @param string|array $middleware Middleware class name(s)
     * @return $this
     */
    public function addMiddleware($middleware)
    {
        $lastRoute = end($this->routes);
        if ($lastRoute) {
            $key = key($this->routes);
            
            if (is_array($middleware)) {
                $this->routes[$key]['middleware'] = array_merge(
                    $this->routes[$key]['middleware'] ?? [],
                    $middleware
                );
            } else {
                $this->routes[$key]['middleware'][] = $middleware;
            }
            
            // Update named route if exists
            foreach ($this->namedRoutes as $name => $route) {
                if ($route['uri'] === $this->routes[$key]['uri']) {
                    $this->namedRoutes[$name]['middleware'] = $this->routes[$key]['middleware'];
                }
            }
        }
        
        return $this;
    }
    
    /**
     * Generate URL for a named route
     *
     * @param string $name Route name
     * @param array $params Parameters to substitute in the URI
     * @return string Generated URL
     * @throws \Exception If named route doesn't exist
     */
    public function url($name, array $params = [])
    {
        if (!isset($this->namedRoutes[$name])) {
            throw new \Exception("Named route '{$name}' not found");
        }
        
        $route = $this->namedRoutes[$name];
        $uri = $route['uri'] ?? $route;
        
        // Replace named parameters in the URI
        foreach ($params as $key => $value) {
            $uri = str_replace(":{$key}", $value, $uri);
        }
        
        // Check if all parameters were replaced
        if (strpos($uri, ':') !== false) {
            throw new \Exception("Missing parameters for URL generation");
        }
        
        return $this->basePath . $uri;
    }
    
    /**
     * Dispatch the request to the appropriate controller
     *
     * @param string|null $uri URI to dispatch
     * @return mixed The result of the controller action
     */
    public function dispatch($uri = null)
    {
        $this->startBenchmark('dispatch');
        
        // Get the URI if not provided
        if ($uri === null) {
            $uri = $this->getCurrentUri();
        }
        
        // Find the matching route
        $this->startBenchmark('findRoute');
        $routeInfo = $this->findRoute($uri);
        $this->endBenchmark('findRoute');
        
        if (!$routeInfo) {
            $this->endBenchmark('dispatch', 'notFound');
            return $this->handleNotFound();
        }
        
        $route = $routeInfo['route'];
        $this->params = $routeInfo['params'];
        
        // Type cast parameters based on route constraints
        $this->castParameters($route);
        
        // Process global middleware
        $this->processMiddleware($this->middleware);
        
        // Process route middleware
        if (isset($route['middleware']) && !empty($route['middleware'])) {
            $this->processMiddleware($route['middleware']);
        }
        
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
                $this->endBenchmark('dispatch', 'legacy');
                return true;
            }
            
            $this->endBenchmark('dispatch', 'notFound');
            return $this->handleNotFound();
        }
        
        // Create the controller using the ServiceProvider
        try {
            $this->startBenchmark('controllerCreate');
            
            // Pass the container to the ServiceProvider if available
            if ($this->container !== null) {
                $controller = \App\Services\ServiceProvider::getController($controllerName);
            } else {
                // For backward compatibility, instantiate directly if no container
                $controller = new $controllerName();
            }
            
            $this->endBenchmark('controllerCreate');
        } catch (\Exception $e) {
            error_log("Controller instantiation error: " . $e->getMessage());
            $this->endBenchmark('dispatch', 'error');
            return $this->handleNotFound();
        }
        
        // Check if the action exists
        if (!method_exists($controller, $actionName)) {
            $this->endBenchmark('dispatch', 'notFound');
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
        $this->startBenchmark('actionExecution');
        $result = $controller->$actionName();
        $this->endBenchmark('actionExecution');
        
        $this->endBenchmark('dispatch', 'success');
        return $result;
    }
    
    /**
     * Process middleware
     *
     * @param array $middlewareList List of middleware to process
     * @return bool True if all middleware passed, false otherwise
     */
    protected function processMiddleware(array $middlewareList)
    {
        // Use middleware registry from instance or try to get from container
        $middlewareRegistry = $this->middlewareRegistry;
        if ($middlewareRegistry === null && $this->container !== null) {
            try {
                $middlewareRegistry = $this->container->get('App\\Middleware\\MiddlewareRegistry');
            } catch (\Exception $e) {
                // Registry not available, use legacy processing
            }
        }
        
        // If we have a middleware registry, use it
        if ($middlewareRegistry !== null) {
            $finalHandler = function ($request) {
                return true;
            };
            
            // Check if middlewareList contains aliases or objects
            if (!empty($middlewareList) && is_string($middlewareList[0]) && !class_exists($middlewareList[0])) {
                // Middleware list contains aliases
                return $middlewareRegistry->apply($_REQUEST, $middlewareList, $finalHandler);
            } else {
                // Middleware list contains objects or class names, process legacy way
                // but use the registry to build the chain
                $middlewareInstances = [];
                
                foreach ($middlewareList as $middleware) {
                    // Convert class name string to object
                    if (is_string($middleware) && class_exists($middleware)) {
                        $middleware = new $middleware();
                    }
                    
                    // Add middleware interface instances to list
                    if ($middleware instanceof \App\Middleware\MiddlewareInterface) {
                        $middlewareInstances[] = $middleware;
                    }
                }
                
                if (!empty($middlewareInstances)) {
                    return $middlewareRegistry->apply($_REQUEST, $middlewareInstances, $finalHandler);
                }
            }
        }
        
        // Legacy middleware processing
        foreach ($middlewareList as $middleware) {
            // Support for class name string
            if (is_string($middleware) && class_exists($middleware)) {
                $middleware = new $middleware();
            }
            
            // Support for closure
            if (is_callable($middleware)) {
                $result = call_user_func($middleware);
                if ($result === false) {
                    return false;
                }
                continue;
            }
            
            // Support for middleware objects with handle method
            if (is_object($middleware) && method_exists($middleware, 'handle')) {
                $next = function ($request) {
                    return true;
                };
                
                $result = $middleware->handle($_REQUEST, $next);
                if ($result === false) {
                    return false;
                }
            }
        }
        
        return true;
    }
    
    /**
     * Cast route parameters based on constraints
     *
     * @param array $route Route definition
     * @return void
     */
    protected function castParameters(array $route)
    {
        if (empty($route['where']) || empty($this->params)) {
            return;
        }
        
        foreach ($route['where'] as $param => $pattern) {
            if (isset($this->params[$param])) {
                // Cast parameter based on constraint type
                switch ($pattern) {
                    case 'int':
                    case 'integer':
                        $this->params[$param] = (int) $this->params[$param];
                        break;
                        
                    case 'float':
                    case 'double':
                        $this->params[$param] = (float) $this->params[$param];
                        break;
                        
                    case 'bool':
                    case 'boolean':
                        $this->params[$param] = (bool) $this->params[$param];
                        break;
                        
                    case 'string':
                        $this->params[$param] = (string) $this->params[$param];
                        break;
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
        
        // First try direct match
        if (isset($this->routes[$uri])) {
            $route = $this->routes[$uri];
            
            // Check if the route supports the current HTTP method
            if (in_array($method, $route['methods'])) {
                return [
                    'route' => $route,
                    'params' => []
                ];
            }
        }
        
        // Check for routes with parameters
        foreach ($this->routes as $pattern => $route) {
            // Skip non-string patterns or special routes
            if (!is_string($pattern)) {
                continue;
            }
            
            // Skip if method doesn't match
            if (isset($route['methods']) && !in_array($method, $route['methods'])) {
                continue;
            }
            
            // Check if this is a parameterized route
            if (strpos($pattern, ':') !== false) {
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
                try {
                    // Pass the container to the ServiceProvider if available
                    if ($this->container !== null) {
                        $controller = \App\Services\ServiceProvider::getController($controllerName);
                    } else {
                        // For backward compatibility, instantiate directly if no container
                        $controller = new $controllerName();
                    }
                    return $controller->$actionName();
                } catch (\Exception $e) {
                    error_log("404 controller instantiation error: " . $e->getMessage());
                }
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
    
    /**
     * Get all route parameters
     *
     * @return array Parameters extracted from the route
     */
    public function getParams()
    {
        return $this->params;
    }
    
    /**
     * Get a specific route parameter
     *
     * @param string $name Parameter name
     * @param mixed $default Default value if parameter doesn't exist
     * @return mixed Parameter value or default
     */
    public function getParam($name, $default = null)
    {
        return $this->params[$name] ?? $default;
    }
}