<?php

namespace Tests\Unit;

use PHPUnit\Framework\TestCase;
use App\Helpers\RoutingHelper;

class RoutingHelperTest extends TestCase
{
    public function testUrlGeneration()
    {
        // Create a helper with test routes
        $helper = new RoutingHelper();
        
        // Test the different ways to generate URLs
        
        // 1. Using named routes (new format)
        $output = $helper->url('home');
        echo "sovest_route('home') -> {$output}\n";
        
        $output = $helper->url('predictions.view', ['id' => 123]);
        echo "sovest_route('predictions.view', ['id' => 123]) -> {$output}\n";
        
        $output = $helper->url('api.stocks.get', ['symbol' => 'AAPL']);
        echo "sovest_route('api.stocks.get', ['symbol' => 'AAPL']) -> {$output}\n";
        
        // 2. Using controller.action format (for legacy support)
        $output = $helper->url('home.index');
        echo "sovest_route('home.index') -> {$output}\n";
        
        $output = $helper->url('prediction.view', ['id' => 123]);
        echo "sovest_route('prediction.view', ['id' => 123]) -> {$output}\n";
        
        // 3. Using direct controller/action
        $output = $helper->action('AuthController', 'login');
        echo "sovest_route_action('AuthController', 'login') -> {$output}\n";
        
        $output = $helper->action('PredictionController', 'view', ['id' => 123]);
        echo "sovest_route_action('PredictionController', 'view', ['id' => 123]) -> {$output}\n";
        
        // 4. Using helper functions
        $output = sovest_route('home');
        echo "sovest_route('home') [function] -> {$output}\n";
        
        $output = sovest_route_action('AuthController', 'login');
        echo "sovest_route_action('AuthController', 'login') [function] -> {$output}\n";
        
        $output = sovest_route_absolute('home');
        echo "sovest_route_absolute('home') [function] -> {$output}\n";
        
        // 5. Test admin routes with namespace
        $output = sovest_route('admin.dashboard');
        echo "sovest_route('admin.dashboard') -> {$output}\n";
        
        // 6. Show all named routes for debugging
        echo "\nAll named routes:\n";
        $namedRoutes = sovest_get_named_routes();
        foreach ($namedRoutes as $name => $pattern) {
            echo "  {$name} => {$pattern}\n";
        }
    }
    
    public function run()
    {
        echo "Running RoutingHelperTest...\n";
        $this->testUrlGeneration();
    }
}

// Run the test
define('APP_BASE_PATH', __DIR__ . '/..');
$test = new RoutingHelperTest();
$test->run();