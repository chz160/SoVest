<?php

use App\Helpers\RoutingHelper;

/**
 * Generate a URL from a named route
 *
 * @param string $name Route name
 * @param array $parameters Route parameters
 * @param bool $absolute Whether to generate an absolute URL
 * @return string
 * 
 * Example: sovest_route('home') -> /
 * Example: sovest_route('predictions.view', ['id' => 123]) -> /predictions/view/123
 */
function sovest_route($name, array $parameters = [], bool $absolute = false)
{
    static $helper;
    
    if (!$helper) {
        $helper = new RoutingHelper();
    }
    
    return $helper->url($name, $parameters, $absolute);
}

/**
 * Generate a URL for a controller and action
 *
 * @param string $controller Controller name
 * @param string $action Action name
 * @param array $parameters Route parameters
 * @param bool $absolute Whether to generate an absolute URL
 * @return string
 * 
 * Example: sovest_route_action('HomeController', 'index') -> /
 * Example: sovest_route_action('PredictionController', 'view', ['id' => 123]) -> /predictions/view/123
 */
function sovest_route_action($controller, $action, array $parameters = [], bool $absolute = false)
{
    static $helper;
    
    if (!$helper) {
        $helper = new RoutingHelper();
    }
    
    return $helper->action($controller, $action, $parameters, $absolute);
}

/**
 * Generate an absolute URL from a named route
 *
 * @param string $name Route name
 * @param array $parameters Route parameters
 * @return string
 * 
 * Example: sovest_route_absolute('home') -> http://example.com/
 */
function sovest_route_absolute($name, array $parameters = [])
{
    return sovest_route($name, $parameters, true);
}

/**
 * Get all named routes (useful for debugging)
 * 
 * @return array Array of route names mapped to their URL patterns
 */
function sovest_get_named_routes()
{
    static $helper;
    
    if (!$helper) {
        $helper = new RoutingHelper();
    }
    
    return $helper->getNamedRoutes();
}