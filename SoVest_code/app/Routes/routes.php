<?php

/**
 * SoVest Routing System
 * 
 * This file defines routes that map URLs to controller actions.
 * Routes are defined in an associative array with the URL pattern as the key
 * and the controller/action as the value.
 */

return [
    // Authentication routes
    '/'             => ['controller' => 'HomeController', 'action' => 'index'],
    '/login'        => ['controller' => 'AuthController', 'action' => 'loginForm'],
    '/login/submit' => ['controller' => 'AuthController', 'action' => 'login'],
    '/register'     => ['controller' => 'AuthController', 'action' => 'registerForm'],
    '/register/submit' => ['controller' => 'AuthController', 'action' => 'register'],
    '/logout'       => ['controller' => 'AuthController', 'action' => 'logout'],
    
    // User routes
    '/home'         => ['controller' => 'HomeController', 'action' => 'home'],
    '/account'      => ['controller' => 'UserController', 'action' => 'account'],
    
    // Prediction routes
    '/predictions'         => ['controller' => 'PredictionController', 'action' => 'index'],
    '/predictions/create'  => ['controller' => 'PredictionController', 'action' => 'create'],
    '/predictions/store'   => ['controller' => 'PredictionController', 'action' => 'store'],
    '/predictions/edit/:id'    => ['controller' => 'PredictionController', 'action' => 'edit'],
    '/predictions/update/:id'  => ['controller' => 'PredictionController', 'action' => 'update'],
    '/predictions/delete/:id'  => ['controller' => 'PredictionController', 'action' => 'delete'],
    '/predictions/view/:id'    => ['controller' => 'PredictionController', 'action' => 'view'],
    '/predictions/vote/:id'    => ['controller' => 'PredictionController', 'action' => 'vote'],
    
    // API routes (for backward compatibility)
    '/api/predictions'     => ['controller' => 'ApiController', 'action' => 'predictionOperations'],
    '/api/search'          => ['controller' => 'ApiController', 'action' => 'search'],
    '/api/stocks'          => ['controller' => 'ApiController', 'action' => 'stocks'],
    
    // Other pages
    '/about'        => ['controller' => 'PageController', 'action' => 'about'],
    '/trending'     => ['controller' => 'PredictionController', 'action' => 'trending'],
    '/leaderboard'  => ['controller' => 'UserController', 'action' => 'leaderboard'],
    '/search'       => ['controller' => 'SearchController', 'action' => 'index'],
    
    // Backward compatibility routes for prediction-related functionality
    '/create_prediction.php' => ['controller' => 'PredictionController', 'action' => 'create'],
    '/trending.php'          => ['controller' => 'PredictionController', 'action' => 'trending'],
    '/my_predictions.php'    => ['controller' => 'PredictionController', 'action' => 'index'],
    
    // Fallback route
    '404'           => ['controller' => 'ErrorController', 'action' => 'notFound'],
];