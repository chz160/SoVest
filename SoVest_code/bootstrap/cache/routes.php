<?php
/**
 * SoVest Cached Routes
 * 
 * This file is auto-generated. Do not edit directly.
 * 
 * Generated: 2025-03-12 22:38:01
 * Source Last Modified: 2025-03-06 21:11:41
 */

// Timestamp for cache validation
$timestamp = 1741837081;
$sourceLastModified = 1741317101;
$sourceFile = '/home/porchn/ra.aid/SoVest/SoVest_code/app/Routes/routes.php';

// Precompiled routes for better performance
return [
    '_timestamp' => $timestamp,
    '_source_last_modified' => $sourceLastModified,
    '_source_file' => $sourceFile,
    'routes' => [
        '/' => [
            'controller' => 'HomeController',
            'action' => 'index',
            'methods' => [
                'GET',
                'POST',
                'PUT',
                'DELETE'
            ]
        ],
        '/login' => [
            'controller' => 'AuthController',
            'action' => 'loginForm',
            'methods' => [
                'GET',
                'POST',
                'PUT',
                'DELETE'
            ]
        ],
        '/login/submit' => [
            'controller' => 'AuthController',
            'action' => 'login',
            'methods' => [
                'GET',
                'POST',
                'PUT',
                'DELETE'
            ]
        ],
        '/register' => [
            'controller' => 'AuthController',
            'action' => 'registerForm',
            'methods' => [
                'GET',
                'POST',
                'PUT',
                'DELETE'
            ]
        ],
        '/register/submit' => [
            'controller' => 'AuthController',
            'action' => 'register',
            'methods' => [
                'GET',
                'POST',
                'PUT',
                'DELETE'
            ]
        ],
        '/logout' => [
            'controller' => 'AuthController',
            'action' => 'logout',
            'methods' => [
                'GET',
                'POST',
                'PUT',
                'DELETE'
            ]
        ],
        '_named_routes' => [

        ],
        '/home' => [
            'controller' => 'HomeController',
            'action' => 'home',
            'methods' => [
                'GET',
                'POST',
                'PUT',
                'DELETE'
            ]
        ],
        '/account' => [
            'controller' => 'UserController',
            'action' => 'account',
            'methods' => [
                'GET',
                'POST',
                'PUT',
                'DELETE'
            ]
        ],
        '/leaderboard' => [
            'controller' => 'UserController',
            'action' => 'leaderboard',
            'methods' => [
                'GET',
                'POST',
                'PUT',
                'DELETE'
            ]
        ],
        '/predictions/' => [
            'controller' => 'PredictionController',
            'action' => 'index',
            'name' => 'predictions.index',
            'methods' => [
                'GET'
            ]
        ],
        '/predictions/view/:id' => [
            'controller' => 'PredictionController',
            'action' => 'view',
            'methods' => [
                'GET',
                'POST',
                'PUT',
                'DELETE'
            ]
        ],
        '/predictions/trending' => [
            'controller' => 'PredictionController',
            'action' => 'trending',
            'name' => 'predictions.trending',
            'methods' => [
                'GET'
            ]
        ],
        '/predictions/create' => [
            'controller' => 'PredictionController',
            'action' => 'create',
            'methods' => [
                'GET',
                'POST',
                'PUT',
                'DELETE'
            ]
        ],
        '/predictions/store' => [
            'controller' => 'PredictionController',
            'action' => 'store',
            'methods' => [
                'GET',
                'POST',
                'PUT',
                'DELETE'
            ]
        ],
        '/predictions/edit/:id' => [
            'controller' => 'PredictionController',
            'action' => 'edit',
            'methods' => [
                'GET',
                'POST',
                'PUT',
                'DELETE'
            ]
        ],
        '/predictions/update/:id' => [
            'controller' => 'PredictionController',
            'action' => 'update',
            'methods' => [
                'GET',
                'POST',
                'PUT',
                'DELETE'
            ]
        ],
        '/predictions/delete/:id' => [
            'controller' => 'PredictionController',
            'action' => 'delete',
            'methods' => [
                'GET',
                'POST',
                'PUT',
                'DELETE'
            ]
        ],
        '/predictions/vote/:id' => [
            'controller' => 'PredictionController',
            'action' => 'vote',
            'methods' => [
                'GET',
                'POST',
                'PUT',
                'DELETE'
            ]
        ],
        '/api/predictions' => [
            'controller' => 'ApiController',
            'action' => 'predictionOperations',
            'methods' => [
                'GET',
                'POST',
                'PUT',
                'DELETE'
            ]
        ],
        '/api/search' => [
            'controller' => 'ApiController',
            'action' => 'search',
            'methods' => [
                'GET',
                'POST',
                'PUT',
                'DELETE'
            ]
        ],
        '/api/stocks' => [
            'controller' => 'ApiController',
            'action' => 'stocks',
            'methods' => [
                'GET',
                'POST',
                'PUT',
                'DELETE'
            ]
        ],
        '/api/stocks/:symbol' => [
            'controller' => 'ApiController',
            'action' => 'getStock',
            'name' => 'api.stocks.get',
            'params' => [
                'symbol' => [
                    'type' => 'string',
                    'required' => true,
                    'pattern' => '^[A-Z]{1,5}$'
                ]
            ],
            'middleware' => [
                'api'
            ],
            'methods' => [
                'GET'
            ]
        ],
        '/about' => [
            'controller' => 'PageController',
            'action' => 'about',
            'methods' => [
                'GET',
                'POST',
                'PUT',
                'DELETE'
            ]
        ],
        '/search' => [
            'controller' => 'SearchController',
            'action' => 'index',
            'methods' => [
                'GET',
                'POST',
                'PUT',
                'DELETE'
            ]
        ],
        '/admin/' => [
            'controller' => 'AdminDashboardController',
            'action' => 'index',
            'name' => 'admin.dashboard',
            'middleware' => [
                'auth',
                'admin'
            ],
            'methods' => [
                'GET'
            ]
        ],
        '/admin/users' => [
            'controller' => 'AdminUserController',
            'action' => 'index',
            'name' => 'admin.users.index',
            'middleware' => [
                'auth',
                'admin'
            ],
            'methods' => [
                'GET'
            ]
        ],
        '/admin/users/:id' => [
            'controller' => 'AdminUserController',
            'action' => 'view',
            'name' => 'admin.users.view',
            'params' => [
                'id' => [
                    'type' => 'int',
                    'required' => true
                ]
            ],
            'middleware' => [
                'auth',
                'admin'
            ],
            'methods' => [
                'GET'
            ]
        ],
        '/predictions' => [
            'controller' => 'PredictionController',
            'action' => 'index',
            'methods' => [
                'GET',
                'POST',
                'PUT',
                'DELETE'
            ]
        ],
        '/trending' => [
            'controller' => 'PredictionController',
            'action' => 'trending',
            'methods' => [
                'GET',
                'POST',
                'PUT',
                'DELETE'
            ]
        ],
        '/create_prediction.php' => [
            'controller' => 'PredictionController',
            'action' => 'create',
            'methods' => [
                'GET',
                'POST',
                'PUT',
                'DELETE'
            ]
        ],
        '/trending.php' => [
            'controller' => 'PredictionController',
            'action' => 'trending',
            'methods' => [
                'GET',
                'POST',
                'PUT',
                'DELETE'
            ]
        ],
        '/my_predictions.php' => [
            'controller' => 'PredictionController',
            'action' => 'index',
            'methods' => [
                'GET',
                'POST',
                'PUT',
                'DELETE'
            ]
        ],
        404 => [
            'controller' => 'ErrorController',
            'action' => 'notFound'
        ],
        403 => [
            'controller' => 'ErrorController',
            'action' => 'forbidden'
        ],
        500 => [
            'controller' => 'ErrorController',
            'action' => 'serverError'
        ]
    ]
];