<?php
/**
 * SoVest Cached Routes
 * 
 * This file is auto-generated. Do not edit directly.
 * 
 * Generated: 2025-03-15 20:16:37
 * Source Last Modified: 2025-03-15 20:16:17
 */

// Timestamp for cache validation
$timestamp = 1742087797;
$sourceLastModified = 1742087777;
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
            'legacy.login' => '/login.php',
            'legacy.logout' => '/logout.php',
            'legacy.home' => '/home.php',
            'legacy.prediction.create' => '/create_prediction.php',
            'legacy.prediction.trending' => '/trending.php',
            'legacy.prediction.my' => '/my_predictions.php',
            'legacy.account' => '/account.php',
            'legacy.search' => '/search.php'
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
            'name' => 'user.leaderboard',
            'middleware' => [
                'auth'
            ],
            'methods' => [
                'GET'
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
            'name' => 'predictions.view',
            'params' => [
                'id' => [
                    'type' => 'int',
                    'required' => true
                ]
            ],
            'methods' => [
                'GET'
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
            'name' => 'predictions.create',
            'middleware' => [
                'auth'
            ],
            'methods' => [
                'GET'
            ]
        ],
        '/predictions/store' => [
            'controller' => 'PredictionController',
            'action' => 'store',
            'name' => 'predictions.store',
            'middleware' => [
                'auth'
            ],
            'methods' => [
                'POST'
            ]
        ],
        '/predictions/edit/:id' => [
            'controller' => 'PredictionController',
            'action' => 'edit',
            'name' => 'predictions.edit',
            'middleware' => [
                'auth',
                'prediction.owner'
            ],
            'params' => [
                'id' => [
                    'type' => 'int',
                    'required' => true
                ]
            ],
            'methods' => [
                'GET'
            ]
        ],
        '/predictions/update/:id' => [
            'controller' => 'PredictionController',
            'action' => 'update',
            'name' => 'predictions.update',
            'middleware' => [
                'auth',
                'prediction.owner'
            ],
            'params' => [
                'id' => [
                    'type' => 'int',
                    'required' => true
                ]
            ],
            'methods' => [
                'POST'
            ]
        ],
        '/predictions/delete/:id' => [
            'controller' => 'PredictionController',
            'action' => 'delete',
            'name' => 'predictions.delete',
            'middleware' => [
                'auth',
                'prediction.owner'
            ],
            'params' => [
                'id' => [
                    'type' => 'int',
                    'required' => true
                ]
            ],
            'methods' => [
                'POST'
            ]
        ],
        '/predictions/vote/:id' => [
            'controller' => 'PredictionController',
            'action' => 'vote',
            'name' => 'predictions.vote',
            'params' => [
                'id' => [
                    'type' => 'int',
                    'required' => true
                ]
            ],
            'middleware' => [
                'auth'
            ],
            'methods' => [
                'POST'
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
        '/api/predictions' => [
            'controller' => 'ApiController',
            'action' => 'predictionOperations',
            'name' => 'api.predictions',
            'middleware' => [
                'api'
            ],
            'methods' => [
                'GET',
                'POST'
            ]
        ],
        '/api/search' => [
            'controller' => 'ApiController',
            'action' => 'search',
            'name' => 'api.search',
            'middleware' => [
                'api'
            ],
            'methods' => [
                'GET'
            ]
        ],
        '/api/search_stocks' => [
            'controller' => 'ApiController',
            'action' => 'searchStocks',
            'name' => 'api.search_stocks',
            'middleware' => [
                'api'
            ],
            'methods' => [
                'GET'
            ]
        ],
        '/api/stocks' => [
            'controller' => 'ApiController',
            'action' => 'stocks',
            'name' => 'api.stocks',
            'middleware' => [
                'api'
            ],
            'methods' => [
                'GET'
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
        '/api/stocks/:symbol/price' => [
            'controller' => 'ApiController',
            'action' => 'getStockPrice',
            'name' => 'api.stocks.price',
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
        '/admin/' => [
            'controller' => 'DashboardController',
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
            'controller' => 'UserController',
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
            'controller' => 'UserController',
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
        '/login.php' => [
            'controller' => 'AuthController',
            'action' => 'loginForm',
            'name' => 'legacy.login',
            'methods' => [
                'GET',
                'POST',
                'PUT',
                'DELETE'
            ]
        ],
        '/logout.php' => [
            'controller' => 'AuthController',
            'action' => 'logout',
            'name' => 'legacy.logout',
            'methods' => [
                'GET',
                'POST',
                'PUT',
                'DELETE'
            ]
        ],
        '/home.php' => [
            'controller' => 'HomeController',
            'action' => 'home',
            'name' => 'legacy.home',
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
            'name' => 'legacy.prediction.create',
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
            'name' => 'legacy.prediction.trending',
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
            'name' => 'legacy.prediction.my',
            'methods' => [
                'GET',
                'POST',
                'PUT',
                'DELETE'
            ]
        ],
        '/account.php' => [
            'controller' => 'UserController',
            'action' => 'account',
            'name' => 'legacy.account',
            'methods' => [
                'GET',
                'POST',
                'PUT',
                'DELETE'
            ]
        ],
        '/search.php' => [
            'controller' => 'SearchController',
            'action' => 'index',
            'name' => 'legacy.search',
            'methods' => [
                'GET',
                'POST',
                'PUT',
                'DELETE'
            ]
        ],
        '/loginCheck.php' => [
            'controller' => 'AuthController',
            'action' => 'login',
            'methods' => [
                'GET',
                'POST',
                'PUT',
                'DELETE'
            ]
        ],
        '/accountCheck.php' => [
            'controller' => 'UserController',
            'action' => 'updateAccount',
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