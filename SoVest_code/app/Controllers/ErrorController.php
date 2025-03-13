<?php

namespace App\Controllers;

/**
 * Error Controller
 * 
 * Handles various error responses for the SoVest application.
 * This controller manages 404, 500, 403, 401, and 400 error pages.
 */
class ErrorController extends Controller
{
    /**
     * Handle 404 Not Found errors
     * 
     * @return void
     */
    public function notFound()
    {
        // Set HTTP status code
        http_response_code(404);
        
        // Set page data
        $pageTitle = '404 Not Found';
        $errorMessage = 'The page you requested could not be found.';
        $errorCode = 404;
        
        // Render the error view
        $this->render('error/404', [
            'pageTitle' => $pageTitle,
            'pageHeader' => 'Page Not Found',
            'errorMessage' => $errorMessage,
            'errorCode' => $errorCode
        ]);
    }
    
    /**
     * Handle 500 Server Error
     * 
     * @param string $message Optional custom error message
     * @return void
     */
    public function serverError($message = null)
    {
        // Set HTTP status code
        http_response_code(500);
        
        // Set page data
        $pageTitle = '500 Server Error';
        $errorMessage = $message ?? 'An unexpected error occurred on the server.';
        $errorCode = 500;
        
        // Render the error view
        $this->render('error/500', [
            'pageTitle' => $pageTitle,
            'pageHeader' => 'Server Error',
            'errorMessage' => $errorMessage,
            'errorCode' => $errorCode
        ]);
    }
    
    /**
     * Handle 403 Forbidden errors
     * 
     * @param string $message Optional custom error message
     * @return void
     */
    public function forbidden($message = null)
    {
        // Set HTTP status code
        http_response_code(403);
        
        // Set page data
        $pageTitle = '403 Forbidden';
        $errorMessage = $message ?? 'You do not have permission to access this resource.';
        $errorCode = 403;
        
        // Render the error view
        $this->render('error/403', [
            'pageTitle' => $pageTitle,
            'pageHeader' => 'Access Denied',
            'errorMessage' => $errorMessage,
            'errorCode' => $errorCode
        ]);
    }
    
    /**
     * Handle 401 Unauthorized errors
     * 
     * @param string $message Optional custom error message
     * @return void
     */
    public function unauthorized($message = null)
    {
        // Set HTTP status code
        http_response_code(401);
        
        // Set page data
        $pageTitle = '401 Unauthorized';
        $errorMessage = $message ?? 'Authentication is required to access this resource.';
        $errorCode = 401;
        
        // Render the error view
        $this->render('error/401', [
            'pageTitle' => $pageTitle,
            'pageHeader' => 'Authentication Required',
            'errorMessage' => $errorMessage,
            'errorCode' => $errorCode
        ]);
    }
    
    /**
     * Handle 400 Bad Request errors
     * 
     * @param string $message Optional custom error message
     * @return void
     */
    public function badRequest($message = null)
    {
        // Set HTTP status code
        http_response_code(400);
        
        // Set page data
        $pageTitle = '400 Bad Request';
        $errorMessage = $message ?? 'The request could not be understood by the server.';
        $errorCode = 400;
        
        // Render the error view
        $this->render('error/400', [
            'pageTitle' => $pageTitle,
            'pageHeader' => 'Bad Request',
            'errorMessage' => $errorMessage,
            'errorCode' => $errorCode
        ]);
    }
}