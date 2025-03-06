<?php

namespace App\Controllers;

/**
 * Page Controller
 * 
 * Handles static pages and generic content pages in the SoVest application.
 * This controller provides methods for rendering static content like about pages,
 * terms of service, privacy policy, etc.
 */
class PageController extends Controller
{
    /**
     * Display the about page
     * 
     * @return void
     */
    public function about()
    {
        // Set page title and header
        $pageTitle = 'About SoVest';
        $pageHeader = 'About SoVest';
        $pageSubheader = 'SoVest is designed to make finding reliable stock tips easy, through our proprietary algorithm that tracks users past performance.';
        
        // Render the about view
        $this->render('about', [
            'pageTitle' => $pageTitle,
            'pageHeader' => $pageHeader,
            'pageSubheader' => $pageSubheader
        ]);
    }
    
    /**
     * Display a generic static page by name
     * 
     * This method allows handling any static page by specifying its name.
     * It automatically sets up the page title and attempts to load the corresponding view.
     * 
     * @param string $pageName The name of the page to display
     * @return void
     */
    public function page($pageName)
    {
        // Convert page name to human-readable format for title
        $pageTitle = ucwords(str_replace('-', ' ', $pageName)) . ' - SoVest';
        
        // Set default header based on page name
        $pageHeader = ucwords(str_replace('-', ' ', $pageName));
        
        // Render the page view
        try {
            $this->render($pageName, [
                'pageTitle' => $pageTitle,
                'pageHeader' => $pageHeader
            ]);
        } catch (\Exception $e) {
            // If the view doesn't exist, redirect to 404
            $this->redirect('/404');
        }
    }
}