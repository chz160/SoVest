<?php

namespace App\Middleware;

/**
 * MiddlewareRegistry
 * 
 * A registry for managing middleware in the application.
 * This class provides a centralized way to register, retrieve,
 * and apply middleware to requests.
 */
class MiddlewareRegistry
{
    /**
     * @var array Middleware instances indexed by their alias
     */
    protected $middleware = [];
    
    /**
     * @var array Global middleware to be applied to all requests
     */
    protected $globalMiddleware = [];
    
    /**
     * Register middleware with an alias
     *
     * @param string $alias Alias for the middleware
     * @param MiddlewareInterface $middleware Middleware instance
     * @return $this For method chaining
     */
    public function register($alias, MiddlewareInterface $middleware)
    {
        $this->middleware[$alias] = $middleware;
        return $this;
    }
    
    /**
     * Register middleware as global
     *
     * @param string $alias Middleware alias
     * @return $this For method chaining
     */
    public function registerGlobal($alias)
    {
        if ($this->has($alias) && !in_array($alias, $this->globalMiddleware)) {
            $this->globalMiddleware[] = $alias;
        }
        return $this;
    }
    
    /**
     * Get middleware instance by alias
     *
     * @param string $alias Middleware alias
     * @return MiddlewareInterface|null Middleware instance or null if not found
     */
    public function get($alias)
    {
        return $this->middleware[$alias] ?? null;
    }
    
    /**
     * Check if middleware exists by alias
     *
     * @param string $alias Middleware alias
     * @return bool True if middleware exists, false otherwise
     */
    public function has($alias)
    {
        return isset($this->middleware[$alias]);
    }
    
    /**
     * Get all registered middleware
     *
     * @return array All registered middleware
     */
    public function all()
    {
        return $this->middleware;
    }
    
    /**
     * Get all global middleware instances
     *
     * @return array Array of middleware instances
     */
    public function getGlobalMiddleware()
    {
        $middlewareInstances = [];
        foreach ($this->globalMiddleware as $alias) {
            $middleware = $this->get($alias);
            if ($middleware) {
                $middlewareInstances[] = $middleware;
            }
        }
        return $middlewareInstances;
    }
    
    /**
     * Resolve middleware from aliases to instances
     *
     * @param array|string $middlewareAliases Middleware aliases
     * @return array Array of middleware instances
     */
    public function resolve($middlewareAliases)
    {
        if (is_string($middlewareAliases)) {
            $middlewareAliases = [$middlewareAliases];
        }
        
        $middlewareInstances = [];
        foreach ($middlewareAliases as $alias) {
            $middleware = $this->get($alias);
            if ($middleware) {
                $middlewareInstances[] = $middleware;
            }
        }
        
        return $middlewareInstances;
    }
    
    /**
     * Apply middleware to a request
     *
     * @param array $request Request data
     * @param array|string $middlewareAliases Middleware aliases
     * @param callable $finalHandler Final handler to call after all middleware
     * @return mixed The result of the middleware chain
     */
    public function apply(array $request, $middlewareAliases, callable $finalHandler)
    {
        // Combine global middleware with route middleware
        $globalMiddleware = $this->getGlobalMiddleware();
        $routeMiddleware = $this->resolve($middlewareAliases);
        $middlewareStack = array_merge($globalMiddleware, $routeMiddleware);
        
        // Execute middleware in reverse order (last added, first executed)
        $next = $finalHandler;
        
        for ($i = count($middlewareStack) - 1; $i >= 0; $i--) {
            $current = $middlewareStack[$i];
            $nextMiddleware = $next;
            
            $next = function ($req) use ($current, $nextMiddleware) {
                return $current->handle($req, $nextMiddleware);
            };
        }
        
        // Execute the middleware stack
        return $next($request);
    }
}