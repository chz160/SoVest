<?php

namespace App\Middleware;

use App\Services\AuthService;
use App\Services\Interfaces\AuthServiceInterface;

/**
 * Authentication Middleware
 * 
 * This middleware checks if a user is authenticated before allowing
 * access to protected routes. Unauthenticated users are redirected
 * to the login page or receive a 401 response for API requests.
 */
class AuthMiddleware implements MiddlewareInterface
{
    /**
     * @var AuthService|AuthServiceInterface
     */
    protected $authService;
    
    /**
     * @var string|null
     */
    protected $redirectUrl;
    
    /**
     * Constructor
     *
     * @param AuthServiceInterface|null $authService Authentication service
     * @param string $redirectUrl URL to redirect to if not authenticated
     */
    public function __construct(AuthServiceInterface $authService = null, $redirectUrl = '/login')
    {
        // Use injected service or fallback to singleton
        $this->authService = $authService ?? AuthService::getInstance();
        $this->redirectUrl = $redirectUrl;
    }
    
    /**
     * Handle an incoming request
     *
     * @param array $request The request data
     * @param callable $next The next middleware to be called
     * @return mixed
     */
    public function handle(array $request, callable $next)
    {
        if (!$this->authService->isAuthenticated()) {
            if ($this->isApiRequest()) {
                // Return JSON response for API requests
                http_response_code(401);
                header('Content-Type: application/json');
                echo json_encode([
                    'success' => false,
                    'message' => 'Unauthorized. Authentication required.'
                ]);
                return false;
            } else {
                // Get the current URL to redirect back after login
                $currentUrl = $_SERVER['REQUEST_URI'] ?? '';
                $redirectParam = $currentUrl ? '?redirect=' . urlencode($currentUrl) : '';
                
                // Redirect to login page for web requests
                header("Location: {$this->redirectUrl}{$redirectParam}");
                exit;
            }
        }
        
        // User is authenticated, proceed to next middleware
        return $next($request);
    }
    
    /**
     * Perform any final actions after the response has been sent to the browser
     *
     * @param array $request The request data
     * @param mixed $response The response data
     * @return void
     */
    public function terminate(array $request, $response)
    {
        // No post-processing needed for auth middleware
    }
    
    /**
     * Check if the current request is an API request
     *
     * @return bool True if API request, false otherwise
     */
    protected function isApiRequest()
    {
        return (
            isset($_SERVER['HTTP_X_REQUESTED_WITH']) && 
            strtolower($_SERVER['HTTP_X_REQUESTED_WITH']) === 'xmlhttprequest'
        ) || (
            isset($_SERVER['HTTP_ACCEPT']) && 
            strpos($_SERVER['HTTP_ACCEPT'], 'application/json') !== false
        ) || (
            isset($_GET['format']) && $_GET['format'] === 'json'
        );
    }
}