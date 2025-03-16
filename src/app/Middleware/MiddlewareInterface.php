<?php

namespace App\Middleware;

/**
 * Middleware Interface
 * 
 * This interface defines the standard for middleware classes in the SoVest application.
 * Middleware provides a way to filter HTTP requests entering the application.
 */
interface MiddlewareInterface
{
    /**
     * Handle an incoming request
     *
     * @param array $request The request data
     * @param callable $next The next middleware to be called
     * @return mixed
     */
    public function handle(array $request, callable $next);
    
    /**
     * Perform any final actions after the response has been sent to the browser
     *
     * @param array $request The request data
     * @param mixed $response The response data
     * @return void
     */
    public function terminate(array $request, $response);
}