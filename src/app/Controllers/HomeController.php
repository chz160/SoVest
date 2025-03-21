<?php

namespace App\Controllers;

use App\Services\Interfaces\AuthServiceInterface;

/**
 * Home Controller
 * 
 * Handles the home page and related functionality.
 */
class HomeController extends Controller
{
    /**
     * Constructor
     * 
     * @param AuthServiceInterface|null $authService Authentication service (optional)
     * @param array $services Additional services to inject (optional)
     */
    public function __construct(?AuthServiceInterface $authService, array $services = [])
    {
        parent::__construct($authService, null, $services);
    }
    
    /**
     * Display the landing page for guests
     * 
     * This method renders the main landing page for unauthenticated users.
     * For authenticated users, redirects to the home dashboard using modern routing.
     * 
     * @return void
     */
    public function index()
    {
        // If user is already logged in, redirect to home
        if ($this->isAuthenticated()) {
            // Use route name instead of direct file reference
            return $this->redirect(App\Helpers\RoutingHelper::url('user.home'));
        }
        
        // Prepare data for the view
        $data = [
            'pageTitle' => 'Welcome to SoVest',
            'pageHeader' => 'Social Investment Platform',
            'pageSubheader' => 'Create and share stock predictions with the community'
        ];
        
        // Display the landing page with data
        //$this->render('index', $data);
        return view('index');
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
        return view('home', [
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
        return view('about', [
            'pageTitle' => $pageTitle
        ]);
    }
}