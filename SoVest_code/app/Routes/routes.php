<?php

/**
 * SoVest Routing System
 * 
 * This file defines routes that map URLs to controller actions.
 * Routes can be defined in multiple formats:
 * 
 * 1. Simple route (backward compatible):
 *    '/path' => ['controller' => 'ControllerName', 'action' => 'actionName']
 * 
 * 2. Advanced route with method constraints, middleware, and name:
 *    '/path' => [
 *      'controller' => 'ControllerName',
 *      'action' => 'actionName',
 *      'method' => 'GET|POST',                 // HTTP method constraint (optional)
 *      'middleware' => ['auth', 'api'],        // Middleware to apply (optional)
 *      'name' => 'route.name',                 // Route name for URL generation (optional)
 *      'params' => [                           // Parameter validation rules (optional)
 *        'id' => ['type' => 'int', 'required' => true]
 *      ]
 *    ]
 * 
 * 3. Route groups for organization:
 *    [
 *      'type' => 'group',
 *      'prefix' => '/admin',                   // URL prefix for all routes in the group
 *      'middleware' => ['auth', 'admin'],      // Middleware applied to all routes in group
 *      'namespace' => 'Admin',                 // Controller namespace prefix
 *      'routes' => [
 *        // Routes within this group...
 *      ]
 *    ]
 */

return [
    // ===================================
    // Route groups with advanced features
    // ===================================
    
    // Authentication routes group
    [
        'type' => 'group',
        'name' => 'auth',
        'routes' => [
            '/' => [
                'controller' => 'HomeController',
                'action' => 'index',
                'method' => 'GET',
                'name' => 'home',
            ],
            '/login' => [
                'controller' => 'AuthController',
                'action' => 'loginForm',
                'method' => 'GET',
                'name' => 'login.form',
            ],
            '/login/submit' => [
                'controller' => 'AuthController',
                'action' => 'login',
                'method' => 'POST',
                'name' => 'login.submit',
            ],
            '/register' => [
                'controller' => 'AuthController',
                'action' => 'registerForm',
                'method' => 'GET',
                'name' => 'register.form',
            ],
            '/register/submit' => [
                'controller' => 'AuthController',
                'action' => 'register',
                'method' => 'POST',
                'name' => 'register.submit',
            ],
            '/logout' => [
                'controller' => 'AuthController',
                'action' => 'logout',
                'method' => 'GET',
                'name' => 'logout',
                'middleware' => ['auth'], // Require authentication to logout
            ],
        ],
    ],

    // User routes group
    [
        'type' => 'group',
        'name' => 'user',
        'middleware' => ['auth'], // All user routes require authentication
        'routes' => [
            '/home' => [
                'controller' => 'HomeController',
                'action' => 'home',
                'method' => 'GET',
                'name' => 'user.home',
            ],
            '/account' => [
                'controller' => 'UserController',
                'action' => 'account',
                'method' => 'GET',
                'name' => 'user.account',
            ],
            '/leaderboard' => [
                'controller' => 'UserController',
                'action' => 'leaderboard',
                'method' => 'GET',
                'name' => 'user.leaderboard',
            ],
        ],
    ],

    // Prediction routes group
    [
        'type' => 'group',
        'prefix' => '/predictions',
        'name' => 'predictions',
        'routes' => [
            // Public prediction routes
            '/' => [
                'controller' => 'PredictionController',
                'action' => 'index',
                'method' => 'GET',
                'name' => 'predictions.index',
            ],
            '/view/:id' => [
                'controller' => 'PredictionController',
                'action' => 'view',
                'method' => 'GET',
                'name' => 'predictions.view',
                'params' => [
                    'id' => ['type' => 'int', 'required' => true],
                ],
            ],
            '/trending' => [
                'controller' => 'PredictionController',
                'action' => 'trending',
                'method' => 'GET',
                'name' => 'predictions.trending',
            ],

            // Authenticated prediction routes
            [
                'type' => 'group',
                'middleware' => ['auth'],
                'routes' => [
                    '/create' => [
                        'controller' => 'PredictionController',
                        'action' => 'create',
                        'method' => 'GET',
                        'name' => 'predictions.create',
                    ],
                    '/store' => [
                        'controller' => 'PredictionController',
                        'action' => 'store',
                        'method' => 'POST',
                        'name' => 'predictions.store',
                    ],
                    '/edit/:id' => [
                        'controller' => 'PredictionController',
                        'action' => 'edit',
                        'method' => 'GET',
                        'name' => 'predictions.edit',
                        'middleware' => ['prediction.owner'], // Ensure user owns the prediction
                        'params' => [
                            'id' => ['type' => 'int', 'required' => true],
                        ],
                    ],
                    '/update/:id' => [
                        'controller' => 'PredictionController',
                        'action' => 'update',
                        'method' => 'POST',
                        'name' => 'predictions.update',
                        'middleware' => ['prediction.owner'], // Ensure user owns the prediction
                        'params' => [
                            'id' => ['type' => 'int', 'required' => true],
                        ],
                    ],
                    '/delete/:id' => [
                        'controller' => 'PredictionController',
                        'action' => 'delete',
                        'method' => 'POST',
                        'name' => 'predictions.delete',
                        'middleware' => ['prediction.owner'], // Ensure user owns the prediction
                        'params' => [
                            'id' => ['type' => 'int', 'required' => true],
                        ],
                    ],
                    '/vote/:id' => [
                        'controller' => 'PredictionController',
                        'action' => 'vote',
                        'method' => 'POST',
                        'name' => 'predictions.vote',
                        'params' => [
                            'id' => ['type' => 'int', 'required' => true],
                        ],
                    ],
                ],
            ],
        ],
    ],

    // API routes
    [
        'type' => 'group',
        'prefix' => '/api',
        'middleware' => ['api'], // API middleware for rate limiting, etc.
        'name' => 'api',
        'routes' => [
            '/predictions' => [
                'controller' => 'ApiController',
                'action' => 'predictionOperations',
                'method' => 'GET|POST',
                'name' => 'api.predictions',
            ],
            '/search' => [
                'controller' => 'ApiController',
                'action' => 'search',
                'method' => 'GET',
                'name' => 'api.search',
            ],
            '/stocks' => [
                'controller' => 'ApiController',
                'action' => 'stocks',
                'method' => 'GET',
                'name' => 'api.stocks',
            ],
            '/stocks/:symbol' => [
                'controller' => 'ApiController',
                'action' => 'getStock',
                'method' => 'GET',
                'name' => 'api.stocks.get',
                'params' => [
                    'symbol' => ['type' => 'string', 'required' => true, 'pattern' => '^[A-Z]{1,5}$'],
                ],
            ],
        ],
    ],

    // Page routes
    [
        'type' => 'group',
        'name' => 'pages',
        'routes' => [
            '/about' => [
                'controller' => 'PageController',
                'action' => 'about',
                'method' => 'GET',
                'name' => 'pages.about',
            ],
            '/search' => [
                'controller' => 'SearchController',
                'action' => 'index',
                'method' => 'GET',
                'name' => 'search',
            ],
        ],
    ],

    // Admin routes (example of nested groups with namespace)
    [
        'type' => 'group',
        'prefix' => '/admin',
        'middleware' => ['auth', 'admin'], // Require both auth and admin middleware
        'namespace' => 'Admin', // Use controllers in the Admin namespace
        'name' => 'admin',
        'routes' => [
            '/' => [
                'controller' => 'DashboardController',
                'action' => 'index',
                'method' => 'GET',
                'name' => 'admin.dashboard',
            ],
            '/users' => [
                'controller' => 'UserController',
                'action' => 'index',
                'method' => 'GET',
                'name' => 'admin.users.index',
            ],
            '/users/:id' => [
                'controller' => 'UserController',
                'action' => 'view',
                'method' => 'GET',
                'name' => 'admin.users.view',
                'params' => [
                    'id' => ['type' => 'int', 'required' => true],
                ],
            ],
        ],
    ],

    // ===================================
    // Legacy route format (for backward compatibility)
    // ===================================
    '/' => ['controller' => 'HomeController', 'action' => 'index'],
    '/login' => ['controller' => 'AuthController', 'action' => 'loginForm'],
    '/login/submit' => ['controller' => 'AuthController', 'action' => 'login'],
    '/register' => ['controller' => 'AuthController', 'action' => 'registerForm'],
    '/register/submit' => ['controller' => 'AuthController', 'action' => 'register'],
    '/logout' => ['controller' => 'AuthController', 'action' => 'logout'],
    '/home' => ['controller' => 'HomeController', 'action' => 'home'],
    '/account' => ['controller' => 'UserController', 'action' => 'account'],
    '/predictions' => ['controller' => 'PredictionController', 'action' => 'index'],
    '/predictions/create' => ['controller' => 'PredictionController', 'action' => 'create'],
    '/predictions/store' => ['controller' => 'PredictionController', 'action' => 'store'],
    '/predictions/edit/:id' => ['controller' => 'PredictionController', 'action' => 'edit'],
    '/predictions/update/:id' => ['controller' => 'PredictionController', 'action' => 'update'],
    '/predictions/delete/:id' => ['controller' => 'PredictionController', 'action' => 'delete'],
    '/predictions/view/:id' => ['controller' => 'PredictionController', 'action' => 'view'],
    '/predictions/vote/:id' => ['controller' => 'PredictionController', 'action' => 'vote'],
    '/api/predictions' => ['controller' => 'ApiController', 'action' => 'predictionOperations'],
    '/api/search' => ['controller' => 'ApiController', 'action' => 'search'],
    '/api/stocks' => ['controller' => 'ApiController', 'action' => 'stocks'],
    '/about' => ['controller' => 'PageController', 'action' => 'about'],
    '/trending' => ['controller' => 'PredictionController', 'action' => 'trending'],
    '/leaderboard' => ['controller' => 'UserController', 'action' => 'leaderboard'],
    '/search' => ['controller' => 'SearchController', 'action' => 'index'],
    '/create_prediction.php' => ['controller' => 'PredictionController', 'action' => 'create'],
    '/trending.php' => ['controller' => 'PredictionController', 'action' => 'trending'],
    '/my_predictions.php' => ['controller' => 'PredictionController', 'action' => 'index'],
    
    // Error handling routes
    '404' => ['controller' => 'ErrorController', 'action' => 'notFound'],
    '403' => ['controller' => 'ErrorController', 'action' => 'forbidden'],
    '500' => ['controller' => 'ErrorController', 'action' => 'serverError'],
];