<?php

namespace App\Routes;

/**
 * Route Cache Generator
 * 
 * This class is responsible for generating and managing a compiled routes cache
 * to improve application performance by avoiding route parsing on every request.
 */
class RouteCacheGenerator
{
    /**
     * Default cache file path
     * 
     * @var string
     */
    protected $cachePath;
    
    /**
     * Routes source file path
     * 
     * @var string
     */
    protected $routesSourcePath;
    
    /**
     * Constructor
     * 
     * @param string $cachePath Path to store the cached routes file
     * @param string $routesSourcePath Path to the routes source file
     */
    public function __construct($cachePath = null, $routesSourcePath = null)
    {
        $basePath = defined('APP_BASE_PATH') ? APP_BASE_PATH : dirname(dirname(__DIR__));
        $this->cachePath = $cachePath ?? $basePath . '/bootstrap/cache/routes.php';
        $this->routesSourcePath = $routesSourcePath ?? $basePath . '/app/Routes/routes.php';
    }
    
    /**
     * Generate the routes cache file
     * 
     * @return bool True if cache was successfully generated, false otherwise
     */
    public function generate()
    {
        // Check if source routes file exists
        if (!file_exists($this->routesSourcePath)) {
            return false;
        }
        
        // Get the routes configuration
        $routes = $this->loadRoutesFromSource();
        
        if (!is_array($routes)) {
            return false;
        }
        
        // Ensure cache directory exists
        $this->ensureCacheDirectoryExists();
        
        // Process and flatten nested routes for better performance
        $processedRoutes = $this->processRoutes($routes);
        
        // Generate the cache code
        $cacheCode = $this->generateCacheCode($processedRoutes);
        
        // Write to cache file
        return $this->writeCacheFile($cacheCode);
    }
    
    /**
     * Load routes from the source file
     * 
     * @return array The routes configuration
     */
    protected function loadRoutesFromSource()
    {
        return require $this->routesSourcePath;
    }
    
    /**
     * Process routes to flatten hierarchies and optimize for lookup
     * 
     * @param array $routes The routes configuration
     * @param string $prefix Current prefix
     * @param array $middleware Current middleware
     * @param string $namespace Current namespace
     * @return array Processed routes
     */
    protected function processRoutes($routes, $prefix = '', $middleware = [], $namespace = '')
    {
        $processed = [];
        $namedRoutes = [];
        
        foreach ($routes as $path => $route) {
            // Handle route groups
            if (isset($route['type']) && $route['type'] === 'group') {
                $newPrefix = $prefix . ($route['prefix'] ?? '');
                $newMiddleware = array_merge($middleware, $route['middleware'] ?? []);
                $newNamespace = $namespace . ($route['namespace'] ?? '');
                
                if (isset($route['routes'])) {
                    $groupRoutes = $this->processRoutes($route['routes'], $newPrefix, $newMiddleware, $newNamespace);
                    foreach ($groupRoutes as $key => $value) {
                        $processed[$key] = $value;
                    }
                }
                
                continue;
            }
            
            // Skip non-route entries
            if (!is_string($path)) {
                $processed[$path] = $route;
                continue;
            }
            
            // Build full path
            $fullPath = $prefix . $path;
            
            // Process controller namespace
            if (isset($route['controller']) && !empty($namespace) && strpos($route['controller'], '\\') === false) {
                $route['controller'] = $namespace . $route['controller'];
            }
            
            // Add middleware from current group
            if (!empty($middleware)) {
                if (!isset($route['middleware'])) {
                    $route['middleware'] = $middleware;
                } else {
                    $route['middleware'] = array_merge($middleware, (array)$route['middleware']);
                }
            }
            
            // Store named route if available
            if (isset($route['name']) && !empty($route['name'])) {
                $namedRoutes[$route['name']] = $fullPath;
            }
            
            // Standard format for HTTP methods
            if (isset($route['method']) && !isset($route['methods'])) {
                $route['methods'] = explode('|', $route['method']);
                unset($route['method']);
            } elseif (!isset($route['methods'])) {
                $route['methods'] = ['GET', 'POST', 'PUT', 'DELETE'];
            }
            
            // Store the optimized route
            $processed[$fullPath] = $route;
        }
        
        // Add named routes index if we're at the top level
        if (empty($prefix) && empty($middleware) && empty($namespace)) {
            $processed['_named_routes'] = $namedRoutes;
        }
        
        return $processed;
    }
    
    /**
     * Generate the cache code
     * 
     * @param array $processedRoutes The processed routes
     * @return string The generated PHP code
     */
    protected function generateCacheCode($processedRoutes)
    {
        $timestamp = time();
        $sourceLastModified = filemtime($this->routesSourcePath);
        
        $code = [
            '<?php',
            '/**',
            ' * SoVest Cached Routes',
            ' * ',
            ' * This file is auto-generated. Do not edit directly.',
            ' * ',
            ' * Generated: ' . date('Y-m-d H:i:s', $timestamp),
            ' * Source Last Modified: ' . date('Y-m-d H:i:s', $sourceLastModified),
            ' */',
            '',
            '// Timestamp for cache validation',
            '$timestamp = ' . $timestamp . ';',
            '$sourceLastModified = ' . $sourceLastModified . ';',
            '$sourceFile = \'' . $this->routesSourcePath . '\';',
            '',
            '// Precompiled routes for better performance',
            'return [',
            '    \'_timestamp\' => $timestamp,',
            '    \'_source_last_modified\' => $sourceLastModified,',
            '    \'_source_file\' => $sourceFile,',
            '    \'routes\' => ' . $this->varExport($processedRoutes, 4),
            '];'
        ];
        
        return implode(PHP_EOL, $code);
    }
    
    /**
     * Custom implementation of var_export with proper indentation
     * 
     * @param mixed $var Variable to export
     * @param int $indent Indentation level
     * @return string The exported variable as a string
     */
    protected function varExport($var, $indent = 0)
    {
        switch (gettype($var)) {
            case 'string':
                return '\'' . addcslashes($var, '\'\\') . '\'';
            case 'array':
                $indexed = array_keys($var) === range(0, count($var) - 1);
                $r = [];
                foreach ($var as $key => $value) {
                    $r[] = str_repeat(' ', $indent + 4)
                         . ($indexed ? '' : $this->varExport($key) . ' => ')
                         . $this->varExport($value, $indent + 4);
                }
                return "[\n" . implode(",\n", $r) . "\n" . str_repeat(' ', $indent) . "]";
            case 'boolean':
                return $var ? 'true' : 'false';
            case 'NULL':
                return 'null';
            case 'integer':
            case 'double':
            default:
                return (string)$var;
        }
    }
    
    /**
     * Write the generated code to the cache file
     * 
     * @param string $code The PHP code to write
     * @return bool True if successful, false otherwise
     */
    protected function writeCacheFile($code)
    {
        return file_put_contents($this->cachePath, $code) !== false;
    }
    
    /**
     * Ensure the cache directory exists
     * 
     * @return void
     */
    protected function ensureCacheDirectoryExists()
    {
        $cacheDir = dirname($this->cachePath);
        
        if (!file_exists($cacheDir)) {
            mkdir($cacheDir, 0755, true);
        }
    }
    
    /**
     * Check if the cache is valid
     * 
     * @return bool True if cache is valid, false otherwise
     */
    public function isCacheValid()
    {
        // Check if cache file exists
        if (!file_exists($this->cachePath)) {
            return false;
        }
        
        // Check if source file exists
        if (!file_exists($this->routesSourcePath)) {
            return false;
        }
        
        try {
            $cache = require $this->cachePath;
            
            // Check if cache has required metadata
            if (!isset($cache['_source_last_modified'])) {
                return false;
            }
            
            // Check if source file has been modified since cache generation
            $sourceLastModified = filemtime($this->routesSourcePath);
            return $sourceLastModified <= $cache['_source_last_modified'];
            
        } catch (\Exception $e) {
            return false;
        }
    }
    
    /**
     * Clear the routes cache
     * 
     * @return bool True if cache was cleared or doesn't exist, false on error
     */
    public function clear()
    {
        if (file_exists($this->cachePath)) {
            return unlink($this->cachePath);
        }
        
        return true;
    }
    
    /**
     * Get the cache file path
     * 
     * @return string The cache file path
     */
    public function getCachePath()
    {
        return $this->cachePath;
    }
    
    /**
     * Get the routes source file path
     * 
     * @return string The routes source file path
     */
    public function getRoutesSourcePath()
    {
        return $this->routesSourcePath;
    }
    
    /**
     * Get cache information
     * 
     * @return array|null Information about the cache or null if no cache exists
     */
    public function getCacheInfo()
    {
        if (!file_exists($this->cachePath)) {
            return null;
        }
        
        try {
            $cache = require $this->cachePath;
            
            if (!isset($cache['_timestamp'], $cache['_source_last_modified'])) {
                return null;
            }
            
            $cacheSize = filesize($this->cachePath);
            $routeCount = 0;
            
            if (isset($cache['routes'])) {
                foreach ($cache['routes'] as $key => $value) {
                    if ($key !== '_named_routes' && is_string($key)) {
                        $routeCount++;
                    }
                }
            }
            
            return [
                'generated_at' => $cache['_timestamp'],
                'generated_date' => date('Y-m-d H:i:s', $cache['_timestamp']),
                'source_modified_at' => $cache['_source_last_modified'],
                'source_modified_date' => date('Y-m-d H:i:s', $cache['_source_last_modified']),
                'cache_file' => $this->cachePath,
                'cache_size' => $cacheSize,
                'route_count' => $routeCount,
                'is_valid' => $this->isCacheValid()
            ];
        } catch (\Exception $e) {
            return null;
        }
    }
}