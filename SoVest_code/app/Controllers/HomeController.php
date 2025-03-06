<?php

namespace App\Controllers;

/**
 * Home Controller
 * 
 * Handles the home page and related functionality.
 */
class HomeController extends Controller
{
    /**
     * Display the landing page for guests
     * 
     * @return void
     */
    public function index()
    {
        // If user is already logged in, redirect to home
        if ($this->isAuthenticated()) {
            return $this->redirect('home.php');
        }
        
        // Display the landing page
        $this->render('index');
    }
    
    /**
     * Display the home page for authenticated users
     * 
     * @return void
     */
    public function home()
    {
        // Require authentication
        $this->requireAuth();
        
        // Get current user data
        $user = $this->getAuthUser();
        
        // Set page title
        $pageTitle = 'Home';
        
        // Render the home view
        $this->render('home', [
            'user' => $user,
            'pageTitle' => $pageTitle
        ]);
    }
    
    /**
     * Display the about page
     * 
     * @return void
     */
    public function about()
    {
        // Set page title
        $pageTitle = 'About SoVest';
        
        // Render the about view
        $this->render('about', [
            'pageTitle' => $pageTitle
        ]);
    }
}
