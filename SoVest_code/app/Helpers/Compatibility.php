<?php

namespace App\Helpers;

/**
 * Compatibility Helper
 * 
 * This class provides helper methods for maintaining backward compatibility
 * with the existing code while transitioning to the MVC architecture.
 */
class Compatibility
{
    /**
     * Include a file from the legacy codebase
     * 
     * @param string $path Path to the file relative to the SoVest_code directory
     * @return mixed The return value of the included file
     */
    public static function includeLegacyFile($path)
    {
        $fullPath = __DIR__ . '/../../' . ltrim($path, '/');
        
        if (!file_exists($fullPath)) {
            throw new \Exception("Legacy file not found: {$path}");
        }
        
        return include $fullPath;
    }
    
    /**
     * Redirect legacy URLs to their MVC equivalents
     * 
     * @param string $url The legacy URL
     * @param array $params Additional query parameters
     * @return string The MVC equivalent URL
     */
    public static function redirectLegacyUrl($url, $params = [])
    {
        // Map of legacy URLs to MVC routes
        $urlMap = [
            'index.php' => '/',
            'login.php' => '/login',
            'loginCheck.php' => '/login/submit',
            'acctNew.php' => '/register',
            'acctCheck.php' => '/register/submit',
            'account.php' => '/account',
            'home.php' => '/home',
            'logout.php' => '/logout',
            'my_predictions.php' => '/predictions',
            'create_prediction.php' => '/predictions/create',
            'trending.php' => '/trending',
            'leaderboard.php' => '/leaderboard',
            'search.php' => '/search',
            'about.php' => '/about',
        ];
        
        // If the URL is in the map, use the MVC route
        if (isset($urlMap[$url])) {
            $mvcUrl = $urlMap[$url];
        } else {
            $mvcUrl = $url;
        }
        
        // If there are parameters, add them to the URL
        if (!empty($params)) {
            $query = http_build_query($params);
            $mvcUrl .= (strpos($mvcUrl, '?') === false) ? '?' . $query : '&' . $query;
        }
        
        return $mvcUrl;
    }
    
    /**
     * Load a legacy view
     * 
     * @param string $view Path to the view relative to the SoVest_code directory
     * @param array $data Data to pass to the view
     * @param bool $return Whether to return the view content or output it
     * @return mixed View content if $return is true, void otherwise
     */
    public static function loadLegacyView($view, $data = [], $return = false)
    {
        // Extract variables from the data array
        extract($data);
        
        // Define the full path to the view file
        $viewPath = __DIR__ . '/../../' . ltrim($view, '/');
        
        // If the view doesn't exist, throw an exception
        if (!file_exists($viewPath)) {
            throw new \Exception("Legacy view not found: {$view}");
        }
        
        // Start output buffering
        ob_start();
        
        // Include the view
        include $viewPath;
        
        // Get the contents of the output buffer
        $content = ob_get_clean();
        
        // Return or output the content
        if ($return) {
            return $content;
        }
        
        echo $content;
    }
}
