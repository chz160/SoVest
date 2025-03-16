#!/usr/bin/env php
<?php
/**
 * SoVest Route Tool
 * 
 * A command line tool for managing and debugging routes in the SoVest application.
 * 
 * Features:
 * - List all defined routes
 * - Display details about a specific route
 * - Test route resolution (check if a URL resolves to the correct controller/action)
 * - Generate URLs for named routes
 * - Generate and manage route cache
 * - Benchmark routing performance
 * 
 * Usage:
 *   php route_tool.php list [--verbose] [--format=table|json]
 *   php route_tool.php show <route-name>
 *   php route_tool.php resolve <url-path>
 *   php route_tool.php generate <route-name> [param1=value1 param2=value2 ...]
 *   php route_tool.php cache:generate [--force]
 *   php route_tool.php cache:clear
 *   php route_tool.php cache:status
 *   php route_tool.php benchmark [--iterations=1000] [--cached]
 *   php route_tool.php help
 * 
 * Examples:
 *   php route_tool.php list
 *   php route_tool.php list --verbose
 *   php route_tool.php show login.form
 *   php route_tool.php resolve /predictions/view/123
 *   php route_tool.php generate predictions.view id=123
 *   php route_tool.php cache:generate
 *   php route_tool.php cache:clear
 *   php route_tool.php benchmark --iterations=5000
 */

// Include bootstrap file for environment setup
require_once __DIR__ . '/bootstrap.php';

/**
 * RouteToolCommand Class
 * 
 * Handles the logic for the route tool command line interface.
 */
class RouteToolCommand
{
    /**
     * ANSI color codes for terminal output
     */
    const COLOR_RESET = "\033[0m";
    const COLOR_GREEN = "\033[32m";
    const COLOR_YELLOW = "\033[33m";
    const COLOR_BLUE = "\033[34m";
    const COLOR_MAGENTA = "\033[35m";
    const COLOR_CYAN = "\033[36m";
    const COLOR_WHITE = "\033[37m";
    const COLOR_BOLD = "\033[1m";
    
    /**
     * Router instance
     *
     * @var \App\Routes\Router
     */
    protected $router;
    
    /**
     * RoutingHelper instance
     *
     * @var \App\Helpers\RoutingHelper
     */
    protected $routingHelper;
    
    /**
     * RouteCacheGenerator instance
     *
     * @var \App\Routes\RouteCacheGenerator
     */
    protected $cacheGenerator;
    
    /**
     * Routes configuration
     *
     * @var array
     */
    protected $routes = [];
    
    /**
     * Named routes
     *
     * @var array
     */
    protected $namedRoutes = [];
    
    /**
     * Constructor
     * 
     * Sets up the router and routing helper.
     */
    public function __construct()
    {
        $this->initializeCacheGenerator();
        $this->initializeRouter();
        $this->initializeRoutingHelper();
    }
    
    /**
     * Initialize the router
     *
     * @param bool $useCache Whether to use cached routes
     * @return void
     */
    protected function initializeRouter($useCache = true)
    {
        try {
            // Get routes using the helper function from bootstrap
            $this->routes = getAppRoutes();
            
            if (!is_array($this->routes)) {
                $this->error("Routes configuration is not an array");
                exit(1);
            }
            
            if (!class_exists('\\App\\Routes\\Router')) {
                $this->error("Router class not found. Check that the bootstrap file loaded it correctly.");
                exit(1);
            }
            
            $this->router = new \App\Routes\OptimizedRouter($this->routes, '', null, $useCache);
        } catch (\Exception $e) {
            $this->error('Error initializing router: ' . $e->getMessage());
            exit(1);
        }
    }
    
    /**
     * Initialize the routing helper
     *
     * @return void
     */
    protected function initializeRoutingHelper()
    {
        try {
            if (!class_exists('\\App\\Helpers\\RoutingHelper')) {
                $this->error("RoutingHelper class not found. Check your autoloading configuration.");
                exit(1);
            }
            
            $this->routingHelper = new \App\Helpers\RoutingHelper();
            $this->namedRoutes = $this->routingHelper->getNamedRoutes();
        } catch (\Exception $e) {
            $this->error('Error initializing routing helper: ' . $e->getMessage());
            exit(1);
        }
    }
    
    /**
     * Initialize the cache generator
     *
     * @return void
     */
    protected function initializeCacheGenerator()
    {
        try {
            if (!class_exists('\\App\\Routes\\RouteCacheGenerator')) {
                require_once APP_BASE_PATH . '/app/Routes/RouteCacheGenerator.php';
            }
            
            $this->cacheGenerator = new \App\Routes\RouteCacheGenerator();
        } catch (\Exception $e) {
            $this->error('Error initializing cache generator: ' . $e->getMessage());
            exit(1);
        }
    }
    
    /**
     * Print an error message to stderr
     *
     * @param string $message Error message
     * @return void
     */
    protected function error($message)
    {
        fwrite(STDERR, "\033[31mError: " . $message . self::COLOR_RESET . PHP_EOL);
    }
    
    /**
     * Print a success message
     *
     * @param string $message Success message
     * @return void
     */
    protected function success($message)
    {
        echo self::COLOR_GREEN . $message . self::COLOR_RESET . PHP_EOL;
    }
    
    /**
     * Print an info message
     *
     * @param string $message Info message
     * @return void
     */
    protected function info($message)
    {
        echo self::COLOR_BLUE . $message . self::COLOR_RESET . PHP_EOL;
    }
    
    /**
     * Print a warning message
     *
     * @param string $message Warning message
     * @return void
     */
    protected function warning($message)
    {
        echo self::COLOR_YELLOW . $message . self::COLOR_RESET . PHP_EOL;
    }
    
    /**
     * Format a value for display
     *
     * @param mixed $value Value to format
     * @return string Formatted value
     */
    protected function formatValue($value)
    {
        if (is_array($value)) {
            return json_encode($value, JSON_PRETTY_PRINT);
        }
        
        return (string) $value;
    }
    
    /**
     * Format file size for human readability
     *
     * @param int $bytes Size in bytes
     * @return string Formatted size
     */
    protected function formatFileSize($bytes)
    {
        $units = ['B', 'KB', 'MB', 'GB', 'TB'];
        
        $bytes = max($bytes, 0);
        $pow = floor(($bytes ? log($bytes) : 0) / log(1024));
        $pow = min($pow, count($units) - 1);
        
        $bytes /= pow(1024, $pow);
        
        return round($bytes, 2) . ' ' . $units[$pow];
    }
    
    /**
     * Format duration in milliseconds
     *
     * @param float $ms Duration in milliseconds
     * @return string Formatted duration
     */
    protected function formatDuration($ms)
    {
        if ($ms < 1) {
            return round($ms * 1000, 2) . ' μs';
        } elseif ($ms < 1000) {
            return round($ms, 2) . ' ms';
        } else {
            return round($ms / 1000, 2) . ' s';
        }
    }
    
    /**
     * List all routes
     *
     * @param bool $verbose Whether to show verbose output
     * @param string $format Output format (table or json)
     * @return void
     */
    public function listRoutes($verbose = false, $format = 'table')
    {
        $routeList = [];
        
        // Process routes to build a flat list
        $this->processRoutes($this->routes, $routeList, '', [], '', $verbose);
        
        // Sort routes by URI
        usort($routeList, function ($a, $b) {
            return strcmp($a['uri'], $b['uri']);
        });
        
        if ($format === 'json') {
            echo json_encode($routeList, JSON_PRETTY_PRINT);
            return;
        }
        
        // Print table header
        echo self::COLOR_BOLD . "URI                  | METHOD | CONTROLLER      | ACTION     | NAME" . self::COLOR_RESET . PHP_EOL;
        echo "---------------------+--------+-----------------+------------+------------------" . PHP_EOL;
        
        // Print each route
        foreach ($routeList as $route) {
            // Convert methods array to string
            $methodStr = 'ANY';
            if (isset($route['methods'])) {
                if (is_array($route['methods'])) {
                    $methodStr = implode('|', $route['methods']);
                } else {
                    $methodStr = (string)$route['methods'];
                }
            }
            
            // Get controller, action, and name as strings
            $controllerStr = isset($route['controller']) && !is_array($route['controller']) ? (string)$route['controller'] : '';
            $actionStr = isset($route['action']) && !is_array($route['action']) ? (string)$route['action'] : '';
            $nameStr = isset($route['name']) && !is_array($route['name']) ? (string)$route['name'] : '';
            
            // Truncate long values for display
            $uri = substr($route['uri'], 0, 19);
            $uri = str_pad($uri, 19, ' ');
            
            $methodStr = substr($methodStr, 0, 6);
            $methodStr = str_pad($methodStr, 6, ' ');
            
            $controllerStr = substr($controllerStr, 0, 15);
            $controllerStr = str_pad($controllerStr, 15, ' ');
            
            $actionStr = substr($actionStr, 0, 10);
            $actionStr = str_pad($actionStr, 10, ' ');
            
            $nameStr = substr($nameStr, 0, 18);
            
            echo self::COLOR_CYAN . $uri . self::COLOR_RESET . " | " .
                 self::COLOR_YELLOW . $methodStr . self::COLOR_RESET . " | " .
                 self::COLOR_GREEN . $controllerStr . self::COLOR_RESET . " | " .
                 self::COLOR_MAGENTA . $actionStr . self::COLOR_RESET . " | " .
                 self::COLOR_BLUE . $nameStr . self::COLOR_RESET . PHP_EOL;
        }
        
        // Print total count
        echo PHP_EOL . "Total routes: " . count($routeList) . PHP_EOL;
        
        // Print cache status
        if ($this->router->isUsingCache()) {
            echo self::COLOR_GREEN . "Routes loaded from cache." . self::COLOR_RESET . PHP_EOL;
        } else {
            echo self::COLOR_YELLOW . "Routes loaded from source file." . self::COLOR_RESET . PHP_EOL;
        }
    }
    
    /**
     * Process routes recursively to build a flat list
     *
     * @param array $routes Routes to process
     * @param array &$routeList Reference to route list being built
     * @param string $prefix Current prefix
     * @param array $middleware Current middleware
     * @param string $namespace Current namespace
     * @param bool $verbose Whether to include additional information
     * @return void
     */
    protected function processRoutes($routes, &$routeList, $prefix = '', $middleware = [], $namespace = '', $verbose = false)
    {
        foreach ($routes as $path => $route) {
            // Handle route groups
            if (isset($route['type']) && $route['type'] === 'group') {
                $newPrefix = $prefix . ($route['prefix'] ?? '');
                $newMiddleware = array_merge($middleware, $route['middleware'] ?? []);
                $newNamespace = $namespace . ($route['namespace'] ?? '');
                
                if (isset($route['routes'])) {
                    $this->processRoutes($route['routes'], $routeList, $newPrefix, $newMiddleware, $newNamespace, $verbose);
                }
                
                continue;
            }
            
            // Skip non-route entries like error handlers (404, etc.)
            if (!is_string($path)) {
                continue;
            }
            
            // Skip numeric keys (error routes)
            if (is_numeric($path)) {
                continue;
            }
            
            $fullPath = $prefix . $path;
            
            $routeInfo = [
                'uri' => $fullPath,
                'controller' => isset($route['controller']) ? $namespace . $route['controller'] : null,
                'action' => $route['action'] ?? null,
                'methods' => isset($route['method']) ? explode('|', $route['method']) : ['GET', 'POST', 'PUT', 'DELETE'],
                'name' => $route['name'] ?? null,
            ];
            
            // Add middleware if verbose
            if ($verbose) {
                $routeInfo['middleware'] = array_merge($middleware, $route['middleware'] ?? []);
                
                if (isset($route['where'])) {
                    $routeInfo['constraints'] = $route['where'];
                }
                
                if (isset($route['params'])) {
                    $routeInfo['params'] = $route['params'];
                }
            }
            
            $routeList[] = $routeInfo;
        }
    }
    
    /**
     * Show details about a specific route
     *
     * @param string $name Route name
     * @return void
     */
    public function showRoute($name)
    {
        // Try to find the route by name
        if (!isset($this->namedRoutes[$name])) {
            $this->error("Route with name '{$name}' not found.");
            return;
        }
        
        $pattern = $this->namedRoutes[$name];
        $route = null;
        
        // Find the route details in the routes array
        foreach ($this->routes as $path => $routeInfo) {
            if (is_string($path) && $path === $pattern) {
                $route = $routeInfo;
                break;
            }
        }
        
        // If not found in top level, search within groups
        if ($route === null) {
            $route = $this->findRouteInGroups($this->routes, $pattern, $name);
        }
        
        if ($route === null) {
            $this->error("Route details for '{$name}' not found.");
            return;
        }
        
        // Display route information
        echo self::COLOR_BOLD . "Route: " . self::COLOR_RESET . self::COLOR_GREEN . $name . self::COLOR_RESET . PHP_EOL;
        echo self::COLOR_BOLD . "URI Pattern: " . self::COLOR_RESET . self::COLOR_CYAN . $pattern . self::COLOR_RESET . PHP_EOL;
        
        if (isset($route['controller'])) {
            echo self::COLOR_BOLD . "Controller: " . self::COLOR_RESET . self::COLOR_GREEN . $route['controller'] . self::COLOR_RESET . PHP_EOL;
        }
        
        if (isset($route['action'])) {
            echo self::COLOR_BOLD . "Action: " . self::COLOR_RESET . self::COLOR_MAGENTA . $route['action'] . self::COLOR_RESET . PHP_EOL;
        }
        
        if (isset($route['method'])) {
            echo self::COLOR_BOLD . "HTTP Methods: " . self::COLOR_RESET . self::COLOR_YELLOW . $route['method'] . self::COLOR_RESET . PHP_EOL;
        } elseif (isset($route['methods'])) {
            echo self::COLOR_BOLD . "HTTP Methods: " . self::COLOR_RESET . self::COLOR_YELLOW . implode(', ', $route['methods']) . self::COLOR_RESET . PHP_EOL;
        }
        
        if (isset($route['middleware']) && !empty($route['middleware'])) {
            echo self::COLOR_BOLD . "Middleware: " . self::COLOR_RESET . self::COLOR_BLUE . implode(', ', $route['middleware']) . self::COLOR_RESET . PHP_EOL;
        }
        
        if (isset($route['where']) && !empty($route['where'])) {
            echo self::COLOR_BOLD . "Parameter Constraints: " . self::COLOR_RESET . PHP_EOL;
            foreach ($route['where'] as $param => $constraint) {
                echo "  " . self::COLOR_CYAN . $param . self::COLOR_RESET . ": " . self::COLOR_YELLOW . $constraint . self::COLOR_RESET . PHP_EOL;
            }
        }
        
        if (isset($route['params']) && !empty($route['params'])) {
            echo self::COLOR_BOLD . "Parameters: " . self::COLOR_RESET . PHP_EOL;
            foreach ($route['params'] as $param => $rules) {
                echo "  " . self::COLOR_CYAN . $param . self::COLOR_RESET . ": " . self::COLOR_YELLOW . $this->formatValue($rules) . self::COLOR_RESET . PHP_EOL;
            }
        }
    }
    
    /**
     * Find a route by pattern in route groups
     *
     * @param array $routes Routes to search
     * @param string $pattern URI pattern to find
     * @param string $name Route name to find
     * @param string $prefix Current prefix
     * @return array|null Route information or null if not found
     */
    protected function findRouteInGroups($routes, $pattern, $name, $prefix = '')
    {
        foreach ($routes as $path => $route) {
            if (isset($route['type']) && $route['type'] === 'group') {
                $newPrefix = $prefix . ($route['prefix'] ?? '');
                
                if (isset($route['routes'])) {
                    $result = $this->findRouteInGroups($route['routes'], $pattern, $name, $newPrefix);
                    if ($result !== null) {
                        return $result;
                    }
                }
                
                continue;
            }
            
            if (is_string($path)) {
                $fullPath = $prefix . $path;
                
                if ($fullPath === $pattern) {
                    return $route;
                }
                
                if (isset($route['name']) && $route['name'] === $name) {
                    return $route;
                }
            }
        }
        
        return null;
    }
    
    /**
     * Test route resolution
     *
     * @param string $uri URI to resolve
     * @return void
     */
    public function resolveRoute($uri)
    {
        try {
            // Create a router instance for testing
            $testRouter = new \App\Routes\OptimizedRouter($this->routes);
            
            // Simulate the HTTP request method from the current request or fallback to GET
            $_SERVER['REQUEST_METHOD'] = $_SERVER['REQUEST_METHOD'] ?? 'GET';
            
            // Find the matching route using the router's internal method
            $reflectionClass = new \ReflectionClass($testRouter);
            $method = $reflectionClass->getMethod('findRoute');
            $method->setAccessible(true);
            
            $routeInfo = $method->invoke($testRouter, $uri);
            
            if ($routeInfo === null) {
                $this->error("No route found for URI: {$uri}");
                echo "Consider the following similar routes:" . PHP_EOL;
                $this->suggestSimilarRoutes($uri);
                return;
            }
            
            $route = $routeInfo['route'];
            $params = $routeInfo['params'];
            
            // Display the matched route information
            echo self::COLOR_BOLD . "URI: " . self::COLOR_RESET . self::COLOR_CYAN . $uri . self::COLOR_RESET . PHP_EOL;
            echo self::COLOR_BOLD . "Matched Route: " . self::COLOR_RESET . PHP_EOL;
            
            if (isset($route['controller'])) {
                echo "  " . self::COLOR_BOLD . "Controller: " . self::COLOR_RESET . self::COLOR_GREEN . $route['controller'] . self::COLOR_RESET . PHP_EOL;
            }
            
            if (isset($route['action'])) {
                echo "  " . self::COLOR_BOLD . "Action: " . self::COLOR_RESET . self::COLOR_MAGENTA . $route['action'] . self::COLOR_RESET . PHP_EOL;
            }
            
            // Find the route name if available
            $routeName = null;
            foreach ($this->namedRoutes as $name => $pattern) {
                if (isset($route['name']) && $route['name'] === $name) {
                    $routeName = $name;
                    break;
                }
            }
            
            if ($routeName !== null) {
                echo "  " . self::COLOR_BOLD . "Route Name: " . self::COLOR_RESET . self::COLOR_BLUE . $routeName . self::COLOR_RESET . PHP_EOL;
            }
            
            if (!empty($params)) {
                echo "  " . self::COLOR_BOLD . "Parameters: " . self::COLOR_RESET . PHP_EOL;
                foreach ($params as $key => $value) {
                    echo "    " . self::COLOR_CYAN . $key . self::COLOR_RESET . ": " . self::COLOR_YELLOW . $value . self::COLOR_RESET . PHP_EOL;
                }
            }
            
            if (isset($route['middleware']) && !empty($route['middleware'])) {
                echo "  " . self::COLOR_BOLD . "Middleware: " . self::COLOR_RESET . self::COLOR_BLUE . implode(', ', $route['middleware']) . self::COLOR_RESET . PHP_EOL;
            }
            
        } catch (\Exception $e) {
            $this->error("Error resolving route: " . $e->getMessage());
        }
    }
    
    /**
     * Suggest similar routes to a given URI
     *
     * @param string $uri URI to find similar routes for
     * @return void
     */
    protected function suggestSimilarRoutes($uri)
    {
        $suggestions = [];
        $flatRoutes = [];
        
        // Build a flat list of routes
        $this->processRoutes($this->routes, $flatRoutes);
        
        // Find similar routes based on Levenshtein distance
        foreach ($flatRoutes as $route) {
            $distance = levenshtein($uri, $route['uri']);
            
            if ($distance <= 3) {
                $suggestions[] = [
                    'uri' => $route['uri'],
                    'distance' => $distance,
                ];
            }
        }
        
        // Sort by distance
        usort($suggestions, function ($a, $b) {
            return $a['distance'] - $b['distance'];
        });
        
        // Display suggestions
        if (!empty($suggestions)) {
            foreach (array_slice($suggestions, 0, 5) as $suggestion) {
                echo "  " . self::COLOR_CYAN . $suggestion['uri'] . self::COLOR_RESET . PHP_EOL;
            }
        } else {
            echo "  No similar routes found." . PHP_EOL;
        }
    }
    
    /**
     * Generate a URL for a named route
     *
     * @param string $name Route name
     * @param array $params Parameters for URL generation
     * @return void
     */
    public function generateUrl($name, array $params = [])
    {
        try {
            $url = $this->routingHelper->url($name, $params);
            
            if (empty($url)) {
                $this->error("Could not generate URL for route '{$name}'.");
                
                // Check if the route exists
                if (!isset($this->namedRoutes[$name])) {
                    $this->warning("Route with name '{$name}' not found.");
                    echo "Available route names:" . PHP_EOL;
                    $this->listNamedRoutes();
                } else {
                    $this->warning("Route exists but URL generation failed. Check if all required parameters are provided.");
                }
                
                return;
            }
            
            $this->success("Generated URL:");
            echo $url . PHP_EOL;
            
            // Show absolute URL too
            $absoluteUrl = $this->routingHelper->url($name, $params, true);
            $this->info("Absolute URL:");
            echo $absoluteUrl . PHP_EOL;
            
        } catch (\Exception $e) {
            $this->error("Error generating URL: " . $e->getMessage());
        }
    }
    
    /**
     * List all named routes
     *
     * @return void
     */
    protected function listNamedRoutes()
    {
        $namedRoutes = $this->routingHelper->getNamedRoutes();
        
        if (empty($namedRoutes)) {
            echo "  No named routes found." . PHP_EOL;
            return;
        }
        
        // Sort by name
        ksort($namedRoutes);
        
        foreach ($namedRoutes as $name => $pattern) {
            echo "  " . self::COLOR_BLUE . $name . self::COLOR_RESET . ": " . self::COLOR_CYAN . $pattern . self::COLOR_RESET . PHP_EOL;
        }
    }
    
    /**
     * Generate the route cache
     *
     * @param bool $force Force regeneration even if cache is valid
     * @return void
     */
    public function generateCache($force = false)
    {
        // Check if cache is already valid
        if (!$force && $this->cacheGenerator->isCacheValid()) {
            $this->warning("Cache is already up to date.");
            $this->showCacheStatus();
            echo PHP_EOL . "Use --force to regenerate cache anyway." . PHP_EOL;
            return;
        }
        
        // Generate cache
        $result = $this->cacheGenerator->generate();
        
        if ($result) {
            $this->success("Route cache generated successfully!");
            $this->showCacheStatus();
        } else {
            $this->error("Failed to generate route cache.");
        }
    }
    
    /**
     * Clear the route cache
     *
     * @return void
     */
    public function clearCache()
    {
        $result = $this->cacheGenerator->clear();
        
        if ($result) {
            $this->success("Route cache cleared successfully!");
        } else {
            $this->error("Failed to clear route cache.");
        }
    }
    
    /**
     * Show the route cache status
     *
     * @return void
     */
    public function showCacheStatus()
    {
        $cacheInfo = $this->cacheGenerator->getCacheInfo();
        
        if ($cacheInfo === null) {
            $this->info("No route cache exists.");
            echo "Run 'php route_tool.php cache:generate' to create the cache." . PHP_EOL;
            return;
        }
        
        echo self::COLOR_BOLD . "Route Cache Status:" . self::COLOR_RESET . PHP_EOL;
        
        // Cache file location
        echo self::COLOR_BOLD . "Cache File: " . self::COLOR_RESET . self::COLOR_CYAN . $cacheInfo['cache_file'] . self::COLOR_RESET . PHP_EOL;
        
        // Cache file size
        echo self::COLOR_BOLD . "Cache Size: " . self::COLOR_RESET . self::COLOR_CYAN . $this->formatFileSize($cacheInfo['cache_size']) . self::COLOR_RESET . PHP_EOL;
        
        // Route count
        echo self::COLOR_BOLD . "Routes Cached: " . self::COLOR_RESET . self::COLOR_CYAN . $cacheInfo['route_count'] . self::COLOR_RESET . PHP_EOL;
        
        // Cache generation time
        echo self::COLOR_BOLD . "Generated At: " . self::COLOR_RESET . self::COLOR_CYAN . $cacheInfo['generated_date'] . self::COLOR_RESET . PHP_EOL;
        
        // Source file last modified
        echo self::COLOR_BOLD . "Source Modified At: " . self::COLOR_RESET . self::COLOR_CYAN . $cacheInfo['source_modified_date'] . self::COLOR_RESET . PHP_EOL;
        
        // Cache validity
        if ($cacheInfo['is_valid']) {
            echo self::COLOR_BOLD . "Status: " . self::COLOR_RESET . self::COLOR_GREEN . "Valid" . self::COLOR_RESET . PHP_EOL;
        } else {
            echo self::COLOR_BOLD . "Status: " . self::COLOR_RESET . self::COLOR_YELLOW . "Outdated" . self::COLOR_RESET . PHP_EOL;
            echo "The source file has been modified since the cache was generated." . PHP_EOL;
            echo "Run 'php route_tool.php cache:generate' to update the cache." . PHP_EOL;
        }
    }
    
    /**
     * Benchmark the routing system performance
     *
     * @param int $iterations Number of iterations to run
     * @param bool $useCache Whether to use cached routes
     * @return void
     */
    public function runBenchmark($iterations = 1000, $useCache = false)
    {
        $this->info("Running routing benchmark...");
        echo "Iterations: {$iterations}" . PHP_EOL;
        echo "Using cache: " . ($useCache ? 'Yes' : 'No') . PHP_EOL;
        echo PHP_EOL;
        
        // Prepare test URIs
        $testUris = [
            '/',                       // Home
            '/login',                  // Simple route
            '/predictions/view/123',   // Parameterized route
            '/admin/users/456',        // Nested route with parameter
            '/api/stocks/AAPL',        // API route with string parameter
            '/nonexistent/path'        // Non-existent route
        ];
        
        $results = [];
        $totalTime = 0;
        
        // Run benchmark for each URI
        foreach ($testUris as $uri) {
            $times = [];
            
            // Warmup
            for ($i = 0; $i < 10; $i++) {
                $this->benchmarkRouteResolution($uri, $useCache);
            }
            
            // Run benchmark
            for ($i = 0; $i < $iterations; $i++) {
                $time = $this->benchmarkRouteResolution($uri, $useCache);
                $times[] = $time;
                $totalTime += $time;
            }
            
            // Calculate statistics
            $avgTime = array_sum($times) / count($times);
            $minTime = min($times);
            $maxTime = max($times);
            
            // Sort times for percentile calculation
            sort($times);
            $p50 = $times[floor(count($times) * 0.5)];
            $p95 = $times[floor(count($times) * 0.95)];
            $p99 = $times[floor(count($times) * 0.99)];
            
            $results[$uri] = [
                'avg' => $avgTime,
                'min' => $minTime,
                'max' => $maxTime,
                'p50' => $p50,
                'p95' => $p95,
                'p99' => $p99
            ];
        }
        
        // Display results
        echo self::COLOR_BOLD . "Benchmark Results:" . self::COLOR_RESET . PHP_EOL;
        
        foreach ($results as $uri => $stats) {
            echo self::COLOR_BOLD . "URI: " . self::COLOR_RESET . self::COLOR_CYAN . $uri . self::COLOR_RESET . PHP_EOL;
            echo "  Avg: " . self::COLOR_GREEN . $this->formatDuration($stats['avg']) . self::COLOR_RESET . PHP_EOL;
            echo "  Min: " . self::COLOR_GREEN . $this->formatDuration($stats['min']) . self::COLOR_RESET . PHP_EOL;
            echo "  Max: " . self::COLOR_GREEN . $this->formatDuration($stats['max']) . self::COLOR_RESET . PHP_EOL;
            echo "  P50: " . self::COLOR_GREEN . $this->formatDuration($stats['p50']) . self::COLOR_RESET . PHP_EOL;
            echo "  P95: " . self::COLOR_GREEN . $this->formatDuration($stats['p95']) . self::COLOR_RESET . PHP_EOL;
            echo "  P99: " . self::COLOR_GREEN . $this->formatDuration($stats['p99']) . self::COLOR_RESET . PHP_EOL;
            echo PHP_EOL;
        }
        
        // Overall stats
        $overallAvg = $totalTime / ($iterations * count($testUris));
        echo self::COLOR_BOLD . "Overall Average: " . self::COLOR_RESET . self::COLOR_GREEN . $this->formatDuration($overallAvg) . self::COLOR_RESET . PHP_EOL;
        echo self::COLOR_BOLD . "Routes per second: " . self::COLOR_RESET . self::COLOR_GREEN . round(1000 / $overallAvg) . self::COLOR_RESET . PHP_EOL;
        
        // Compare with and without cache
        if (!$useCache) {
            echo PHP_EOL;
            echo "Try running with --cached to compare performance with cache: " . PHP_EOL;
            echo "  php route_tool.php benchmark --iterations={$iterations} --cached" . PHP_EOL;
        }
    }
    
    /**
     * Benchmark a single route resolution
     *
     * @param string $uri URI to resolve
     * @param bool $useCache Whether to use cached routes
     * @return float Time taken in milliseconds
     */
    protected function benchmarkRouteResolution($uri, $useCache = false)
    {
        // Initialize a fresh router for each test to avoid state issues
        $router = new \App\Routes\OptimizedRouter($this->routes, '', null, $useCache);
        
        // Measure time for findRoute method
        $startTime = microtime(true);
        
        $reflectionClass = new \ReflectionClass($router);
        $method = $reflectionClass->getMethod('findRoute');
        $method->setAccessible(true);
        
        $routeInfo = $method->invoke($router, $uri);
        
        $endTime = microtime(true);
        
        // Return time in milliseconds
        return ($endTime - $startTime) * 1000;
    }
    
    /**
     * Display help information
     *
     * @return void
     */
    public function showHelp()
    {
        echo self::COLOR_BOLD . "SoVest Route Tool" . self::COLOR_RESET . PHP_EOL;
        echo "A command line tool for managing and debugging routes in the SoVest application." . PHP_EOL . PHP_EOL;
        
        echo self::COLOR_BOLD . "Usage:" . self::COLOR_RESET . PHP_EOL;
        echo "  php route_tool.php " . self::COLOR_GREEN . "list" . self::COLOR_RESET . " [--verbose] [--format=table|json]" . PHP_EOL;
        echo "  php route_tool.php " . self::COLOR_GREEN . "show" . self::COLOR_RESET . " <route-name>" . PHP_EOL;
        echo "  php route_tool.php " . self::COLOR_GREEN . "resolve" . self::COLOR_RESET . " <url-path>" . PHP_EOL;
        echo "  php route_tool.php " . self::COLOR_GREEN . "generate" . self::COLOR_RESET . " <route-name> [param1=value1 param2=value2 ...]" . PHP_EOL;
        echo "  php route_tool.php " . self::COLOR_GREEN . "cache:generate" . self::COLOR_RESET . " [--force]" . PHP_EOL;
        echo "  php route_tool.php " . self::COLOR_GREEN . "cache:clear" . self::COLOR_RESET . PHP_EOL;
        echo "  php route_tool.php " . self::COLOR_GREEN . "cache:status" . self::COLOR_RESET . PHP_EOL;
        echo "  php route_tool.php " . self::COLOR_GREEN . "benchmark" . self::COLOR_RESET . " [--iterations=1000] [--cached]" . PHP_EOL;
        echo "  php route_tool.php " . self::COLOR_GREEN . "help" . self::COLOR_RESET . PHP_EOL . PHP_EOL;
        
        echo self::COLOR_BOLD . "Commands:" . self::COLOR_RESET . PHP_EOL;
        echo "  " . self::COLOR_GREEN . "list" . self::COLOR_RESET . "          List all defined routes" . PHP_EOL;
        echo "  " . self::COLOR_GREEN . "show" . self::COLOR_RESET . "          Show details about a specific route" . PHP_EOL;
        echo "  " . self::COLOR_GREEN . "resolve" . self::COLOR_RESET . "       Test route resolution (check if a URL resolves to the correct controller/action)" . PHP_EOL;
        echo "  " . self::COLOR_GREEN . "generate" . self::COLOR_RESET . "      Generate a URL for a named route" . PHP_EOL;
        echo "  " . self::COLOR_GREEN . "cache:generate" . self::COLOR_RESET . " Generate the route cache for improved performance" . PHP_EOL;
        echo "  " . self::COLOR_GREEN . "cache:clear" . self::COLOR_RESET . "   Clear the route cache" . PHP_EOL;
        echo "  " . self::COLOR_GREEN . "cache:status" . self::COLOR_RESET . "  Display information about the route cache" . PHP_EOL;
        echo "  " . self::COLOR_GREEN . "benchmark" . self::COLOR_RESET . "     Benchmark routing system performance" . PHP_EOL;
        echo "  " . self::COLOR_GREEN . "benchmark:detail" . self::COLOR_RESET . " Run detailed router performance comparison" . PHP_EOL;
        echo "  " . self::COLOR_GREEN . "help" . self::COLOR_RESET . "          Show this help information" . PHP_EOL . PHP_EOL;
        
        echo self::COLOR_BOLD . "Examples:" . self::COLOR_RESET . PHP_EOL;
        echo "  php route_tool.php list" . PHP_EOL;
        echo "  php route_tool.php list --verbose" . PHP_EOL;
        echo "  php route_tool.php show login.form" . PHP_EOL;
        echo "  php route_tool.php resolve /predictions/view/123" . PHP_EOL;
        echo "  php route_tool.php generate predictions.view id=123" . PHP_EOL;
        echo "  php route_tool.php cache:generate" . PHP_EOL;
        echo "  php route_tool.php cache:clear" . PHP_EOL;
        echo "  php route_tool.php benchmark --iterations=5000 --cached" . PHP_EOL;
    }
}

// Main execution
if (php_sapi_name() !== 'cli') {
    echo "This script can only be run from the command line.";
    exit(1);
}

// Parse command line arguments
$command = $argv[1] ?? 'help';
$args = array_slice($argv, 2);

// Initialize command
$routeTool = new RouteToolCommand();

// Process command
switch ($command) {
    case 'list':
        $verbose = in_array('--verbose', $args);
        $format = 'table';
        
        // Check for format
        foreach ($args as $arg) {
            if (strpos($arg, '--format=') === 0) {
                $format = substr($arg, 9);
            }
        }
        
        // Remove options from args
        $args = array_filter($args, function ($arg) {
            return strpos($arg, '--') !== 0;
        });
        
        $routeTool->listRoutes($verbose, $format);
        break;
        
    case 'show':
        if (empty($args)) {
            $routeTool->error("Missing route name. Usage: php route_tool.php show <route-name>");
            exit(1);
        }
        
        $routeTool->showRoute($args[0]);
        break;
        
    case 'resolve':
        if (empty($args)) {
            $routeTool->error("Missing URL path. Usage: php route_tool.php resolve <url-path>");
            exit(1);
        }
        
        $routeTool->resolveRoute($args[0]);
        break;
        
    case 'generate':
        if (empty($args)) {
            $routeTool->error("Missing route name. Usage: php route_tool.php generate <route-name> [param1=value1 param2=value2 ...]");
            exit(1);
        }
        
        $routeName = array_shift($args);
        $params = [];
        
        // Parse parameters
        foreach ($args as $arg) {
            if (strpos($arg, '=') !== false) {
                list($key, $value) = explode('=', $arg, 2);
                $params[$key] = $value;
            }
        }
        
        $routeTool->generateUrl($routeName, $params);
        break;
        
    case 'cache:generate':
        $force = in_array('--force', $args);
        $routeTool->generateCache($force);
        break;
        
    case 'cache:clear':
        $routeTool->clearCache();
        break;
        
    case 'cache:status':
        $routeTool->showCacheStatus();
        break;
        
    case 'benchmark':
        $iterations = 1000;
        $useCache = in_array('--cached', $args);
        
        // Check for iterations parameter
        foreach ($args as $arg) {
            if (strpos($arg, '--iterations=') === 0) {
                $iterations = (int) substr($arg, 13);
                if ($iterations <= 0) {
                    $iterations = 1000;
                }
            }
        }
        
        $routeTool->runBenchmark($iterations, $useCache);
        break;
        
    case 'benchmark:detail':
        echo "Running detailed benchmark..." . PHP_EOL;
        
        // Run the detailed benchmark script
        $cmd = 'php ' . __DIR__ . '/benchmark_realistic.php';
        system($cmd);
        break;
        
    case 'help':
    default:
        $routeTool->showHelp();
        break;
}