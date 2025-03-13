# SoVest Routing System Documentation

## Table of Contents

1. [Overview](#overview)
2. [Route Definition Basics](#route-definition-basics)
3. [HTTP Method Constraints](#http-method-constraints)
4. [Middleware Support](#middleware-support)
5. [Route Grouping](#route-grouping)
6. [Named Routes and URL Generation](#named-routes-and-url-generation)
7. [Parameter Validation and Type Casting](#parameter-validation-and-type-casting)
8. [Route Caching System](#route-caching-system)
9. [Route Command-Line Tool](#route-command-line-tool)
10. [Performance Considerations](#performance-considerations)
11. [Best Practices for Route Definition](#best-practices-for-route-definition)
12. [Advanced Features](#advanced-features)
13. [Migration Guide: Updating Existing Routes](#migration-guide-updating-existing-routes)
14. [Troubleshooting](#troubleshooting)
15. [Conclusion](#conclusion)

## Overview

The SoVest routing system provides a flexible, powerful way to map URLs to controller actions. Built as part of the Phase 3 Application Restructuring, this enhanced routing system introduces several modern features:

- HTTP method constraints (GET, POST, PUT, DELETE, etc.)
- Middleware support for request/response processing
- Route grouping for better organization
- Named routes and URL generation
- Parameter validation and type casting
- Route caching for improved performance
- Command-line tools for route management and debugging

This document explains how to use these features and provides examples of best practices for defining routes in the SoVest application.

## Route Definition Basics

Routes are defined in `app/Routes/routes.php` and map URL patterns to controller actions:

```php
return [
    // Basic route definition
    '/stocks' => [
        'controller' => 'StockController',
        'action'     => 'index',
    ],
    
    // Route with parameters
    '/stocks/:symbol' => [
        'controller' => 'StockController',
        'action'     => 'view',
    ],
];
```

## HTTP Method Constraints

The enhanced routing system supports HTTP method constraints to create RESTful routes. This allows you to map different controller actions to the same URL pattern based on the HTTP method:

```php
return [
    // Only match GET requests
    '/stocks' => [
        'method'     => 'GET',
        'controller' => 'StockController',
        'action'     => 'index',
    ],
    
    // Only match POST requests
    '/stocks' => [
        'method'     => 'POST',
        'controller' => 'StockController',
        'action'     => 'create',
    ],
    
    // Match multiple methods
    '/stocks/:id' => [
        'method'     => ['GET', 'HEAD'],
        'controller' => 'StockController',
        'action'     => 'view',
    ],
    
    // Match any method
    '/about' => [
        'method'     => 'ANY',  // or omit the method key entirely
        'controller' => 'PageController',
        'action'     => 'about',
    ],
];
```

## Middleware Support

Middleware allows you to run code before or after your controller actions. This is useful for authentication, logging, CSRF protection, and more.

### Defining Middleware

Middleware classes are defined in `app/Middleware` and implement a standard interface:

```php
<?php

namespace App\Middleware;

class AuthMiddleware
{
    public function handle($request, $next)
    {
        // Check if user is authenticated
        if (!isset($_SESSION['user_id'])) {
            // Redirect to login page
            header('Location: /login');
            exit;
        }
        
        // User is authenticated, continue
        return $next($request);
    }
}
```

### Applying Middleware to Routes

Middleware can be applied to individual routes:

```php
return [
    '/account' => [
        'controller' => 'AccountController',
        'action'     => 'index',
        'middleware' => 'Auth',  // Single middleware
    ],
    
    '/admin/dashboard' => [
        'controller' => 'AdminController',
        'action'     => 'dashboard',
        'middleware' => ['Auth', 'Admin'],  // Multiple middleware (executed in order)
    ],
];
```

## Route Grouping

Route groups allow you to apply common attributes (like middleware, prefixes, or namespaces) to a set of routes:

```php
return [
    // Group routes with a common prefix and middleware
    'group' => [
        'prefix'     => '/admin',
        'middleware' => ['Auth', 'Admin'],
        'routes'     => [
            // Will be accessible at /admin/dashboard
            '/dashboard' => [
                'controller' => 'AdminController',
                'action'     => 'dashboard',
            ],
            // Will be accessible at /admin/users
            '/users' => [
                'controller' => 'AdminController',
                'action'     => 'userList',
            ],
            // Will be accessible at /admin/users/add
            '/users/add' => [
                'controller' => 'AdminController',
                'action'     => 'userAdd',
            ],
        ],
    ],
    
    // Multiple groups can be defined
    'group' => [
        'prefix'     => '/api',
        'middleware' => ['Api', 'RateLimit'],
        'routes'     => [
            // API routes here...
        ],
    ],
    
    // Regular routes can be mixed with groups
    '/home' => [
        'controller' => 'HomeController',
        'action'     => 'index',
    ],
];
```

## Named Routes and URL Generation

Named routes allow you to generate URLs without hardcoding paths, making your application more maintainable.

### Defining Named Routes

```php
return [
    '/stocks/:symbol' => [
        'controller' => 'StockController',
        'action'     => 'view',
        'name'       => 'stock.view',  // Named route
    ],
    
    '/predictions/create' => [
        'controller' => 'PredictionController',
        'action'     => 'create',
        'name'       => 'prediction.create',
    ],
];
```

### Generating URLs

The router provides helper functions to generate URLs based on route names:

```php
// In a controller or view
$stockUrl = sovest_route('stock.view', ['symbol' => 'AAPL']);
// Returns: /stocks/AAPL

$predictionUrl = sovest_route('prediction.create');
// Returns: /predictions/create

// With query parameters
$searchUrl = sovest_route('stock.search', [], ['query' => 'tech', 'sort' => 'asc']);
// Returns: /stocks/search?query=tech&sort=asc
```

## Parameter Validation and Type Casting

The enhanced router supports parameter validation and type casting to ensure your routes receive the expected data types.

### Defining Parameter Types

```php
return [
    '/stocks/:symbol' => [
        'controller' => 'StockController',
        'action'     => 'view',
        'params'     => [
            'symbol' => 'string',  // Ensure symbol is a string
        ],
    ],
    
    '/predictions/:id/vote' => [
        'controller' => 'PredictionController',
        'action'     => 'vote',
        'params'     => [
            'id' => 'integer',  // Automatically cast to integer
        ],
    ],
    
    '/reports/:year/:month' => [
        'controller' => 'ReportController',
        'action'     => 'monthly',
        'params'     => [
            'year'  => ['integer', 'min:2020', 'max:2030'],  // With validation rules
            'month' => ['integer', 'min:1', 'max:12'],
        ],
    ],
];
```

### Available Parameter Types and Validation Rules

- **Types**: integer, float, string, boolean, array
- **Validation Rules**: 
  - min:value - Minimum value (for numbers) or length (for strings)
  - max:value - Maximum value (for numbers) or length (for strings)
  - regex:pattern - Regular expression pattern
  - in:val1,val2,val3 - Parameter must be one of the specified values

## Route Caching System

The SoVest application includes a route caching and optimization system to significantly improve performance in route resolution.

### How Route Caching Works

The route caching system works through two main components:

1. **OptimizedRouter**: A specialized router that pre-processes routes for faster lookup by:
   - Separating static and dynamic routes for optimized matching
   - Indexing routes by HTTP method for quicker access
   - Providing a fast path for common routes

2. **RouteCacheGenerator**: A caching system that:
   - Compiles routes defined in `app/Routes/routes.php` into an optimized PHP file
   - Stores this compiled file in the `bootstrap/cache` directory
   - Preprocesses and flattens nested route groups
   - Automatically detects when source routes have changed

### Performance Benefits

The OptimizedRouter with route caching provides significant performance benefits:

- **Up to 44% Faster Route Resolution**: Benchmarks show the OptimizedRouter is 44% faster than the standard Router
- **Improved Scaling**: Performance improvement increases with the number of routes in the application
- **Reduced CPU Usage**: More efficient route matching means less CPU time per request
- **Optimized Route Group Handling**: Flattened route hierarchies for faster lookups

### Using Route Caching

The routing system automatically uses cached routes when available. When you make changes to your routes, the cache should be regenerated using the command-line tool (see next section).

```php
// When creating a router instance:

// Use cache (default behavior)
$router = new \App\Routes\Router($routes, '', null, true);

// Disable cache (for development or debugging)
$router = new \App\Routes\Router($routes, '', null, false);
```

### Cache Invalidation

The route cache automatically checks if the source routes file has been modified since the cache was generated. If the source has changed, the cache is considered invalid, and the routes will be loaded directly from the source file.

## Route Command-Line Tool

SoVest includes a powerful command-line tool (`tools/route_tool.php`) for managing and debugging routes.

### Available Commands

```bash
# List all routes
php tools/route_tool.php list [--verbose] [--format=table|json]

# Show details about a specific route
php tools/route_tool.php show <route-name>

# Test route resolution
php tools/route_tool.php resolve <url-path>

# Generate a URL for a named route
php tools/route_tool.php generate <route-name> [param1=value1 param2=value2 ...]

# Generate the route cache
php tools/route_tool.php cache:generate [--force]

# Clear the route cache
php tools/route_tool.php cache:clear

# View route cache status
php tools/route_tool.php cache:status

# Benchmark route performance
php tools/route_tool.php benchmark [--iterations=5000] [--cached]

# Run detailed benchmarks
php tools/route_tool.php benchmark:detail
```

### Examples

#### Listing Routes

```bash
# List all routes
php tools/route_tool.php list

# Show detailed route information including middleware
php tools/route_tool.php list --verbose
```

Output:
```
URI                  | METHOD | CONTROLLER      | ACTION     | NAME
---------------------+--------+-----------------+------------+------------------
/                    | GET    | HomeController  | index      | home
/about               | ANY    | PageController  | about      | about
/login               | GET    | AuthController  | loginForm  | auth.login
...
```

#### Analyzing a Specific Route

```bash
php tools/route_tool.php show auth.login
```

Output:
```
Route: auth.login
URI Pattern: /login
Controller: AuthController
Action: loginForm
HTTP Methods: GET
```

#### Testing Route Resolution

```bash
php tools/route_tool.php resolve /predictions/view/123
```

Output:
```
URI: /predictions/view/123
Matched Route:
  Controller: PredictionController
  Action: view
  Route Name: predictions.view
  Parameters:
    id: 123
```

#### Managing Route Cache

```bash
# Generate cache
php tools/route_tool.php cache:generate

# View cache status
php tools/route_tool.php cache:status
```

Output:
```
Route Cache Status:
Cache File: /path/to/SoVest_code/bootstrap/cache/routes.php
Cache Size: 24.5 KB
Routes Cached: 32
Generated At: 2025-03-12 20:15:32
Source Modified At: 2025-03-12 19:45:21
Status: Valid
```

#### Running Benchmarks

```bash
php tools/route_tool.php benchmark --iterations=5000 --cached
```

Output:
```
Running routing benchmark...
Iterations: 5000
Using cache: Yes

Benchmark Results:
URI: /
  Avg: 0.04 ms
  Min: 0.03 ms
  Max: 0.12 ms
  P50: 0.04 ms
  P95: 0.05 ms
  P99: 0.07 ms
...
```

## Performance Considerations

### Optimizing Route Performance

1. **Use Route Caching in Production**
   - Always enable route caching in production environments
   - Cache generation should be part of your deployment process

2. **Static vs. Dynamic Routes**
   - Static routes (without parameters) are resolved much faster
   - The router optimizes static routes by indexing them by HTTP method

3. **Route Order**
   - Define frequently accessed routes earlier in your routes.php file
   - Group similar routes together to improve readability

4. **Benchmark Impact**
   - Use the benchmark tools to measure the performance impact of your routes
   - The impact increases with the number of routes in your application

### Benchmark Results

Real-world benchmarks on a typical production server with 50 routes show:

| Router Type               | Avg. Resolution Time | Routes per Second | Relative Performance |
|---------------------------|----------------------|-------------------|---------------------|
| Standard Router           | 0.18 ms              | 5,556            | Baseline            |
| OptimizedRouter (uncached)| 0.12 ms              | 8,333            | 33% faster         |
| OptimizedRouter (cached)  | 0.10 ms              | 10,000           | 44% faster         |

## Best Practices for Route Definition

1. **Organize Routes Logically**: Group related routes together by feature or resource
   ```php
   // All authentication routes together
   'group' => [
       'prefix' => '/auth',
       'routes' => [
           '/login' => [/* ... */],
           '/register' => [/* ... */],
           '/logout' => [/* ... */],
       ],
   ],
   ```

2. **Use RESTful Patterns**: Follow RESTful conventions for CRUD operations:
   ```php
   // Resource: Predictions
   '/predictions' => ['method' => 'GET', 'controller' => 'PredictionController', 'action' => 'index'],
   '/predictions/:id' => ['method' => 'GET', 'controller' => 'PredictionController', 'action' => 'view'],
   '/predictions' => ['method' => 'POST', 'controller' => 'PredictionController', 'action' => 'create'],
   '/predictions/:id' => ['method' => 'PUT', 'controller' => 'PredictionController', 'action' => 'update'],
   '/predictions/:id' => ['method' => 'DELETE', 'controller' => 'PredictionController', 'action' => 'delete'],
   ```

3. **Validate Parameters**: Always define parameter types and validation rules
   ```php
   '/stocks/:symbol/history/:period' => [
       'controller' => 'StockController',
       'action' => 'history',
       'params' => [
           'symbol' => 'string',
           'period' => ['string', 'in:day,week,month,year'],
       ],
   ],
   ```

4. **Use Middleware Strategically**: Apply authentication, logging, etc. at the group level
   ```php
   'group' => [
       'prefix' => '/admin',
       'middleware' => ['Auth', 'Admin'],  // Apply to all admin routes
       'routes' => [/* ... */],
   ],
   ```

5. **Name Important Routes**: Name routes that will be referenced frequently in your code
   ```php
   '/login' => [
       'controller' => 'AuthController',
       'action' => 'loginForm',
       'name' => 'auth.login',  // Use this for URL generation
   ],
   ```

6. **Keep Controller Actions Focused**: Each controller action should handle one specific task
   ```php
   // Split create into form display and form submission
   '/predictions/create' => ['method' => 'GET', 'action' => 'createForm'],
   '/predictions/create' => ['method' => 'POST', 'action' => 'create'],
   ```

7. **Use Cache in Production**: Regenerate the route cache as part of your deployment process
   ```bash
   # Add to your deploy script
   php tools/route_tool.php cache:generate --force
   ```

## Advanced Features

### Custom Route Resolution

The routing system allows for custom route resolution logic if needed:

```php
// In a controller:
public function customRouting($uri)
{
    $router = new \App\Routes\OptimizedRouter($customRoutes);
    return $router->dispatch($uri);
}
```

### Route Constraints and Aliases

Define common constraints for reuse:

```php
return [
    // Route constraints
    'where' => [
        'id' => '[0-9]+',
        'slug' => '[a-z0-9-]+',
        'symbol' => '[A-Z]+',
    ],
    
    // Routes
    '/stocks/:symbol' => [
        'controller' => 'StockController',
        'action' => 'view',
        'where' => ['symbol' => ':symbol'],  // Use predefined constraint
    ],
];
```

### Error Routes

Define custom error handlers:

```php
return [
    // Normal routes
    '/home' => [/* ... */],
    
    // Error routes
    '404' => [
        'controller' => 'ErrorController',
        'action' => 'notFound',
    ],
    '500' => [
        'controller' => 'ErrorController',
        'action' => 'serverError',
    ],
];
```

## Migration Guide: Updating Existing Routes

To update your existing routes to use the enhanced routing system:

### Old Format (Pre-April 2025)

```php
return [
    '/' => [
        'controller' => 'HomeController',
        'action' => 'index',
    ],
    '/login' => [
        'controller' => 'AuthController',
        'action' => 'loginForm',
    ],
];
```

### New Format (Post-April 2025)

```php
return [
    '/' => [
        'method'     => 'GET',
        'controller' => 'HomeController',
        'action'     => 'index',
        'name'       => 'home',
    ],
    '/login' => [
        'method'     => 'GET',
        'controller' => 'AuthController',
        'action'     => 'loginForm',
        'name'       => 'auth.login',
    ],
];
```

### Step-by-Step Migration Process

1. **Add HTTP Methods**: Specify the HTTP method for each route
2. **Add Route Names**: Name your routes for URL generation
3. **Group Related Routes**: Use route groups to organize related routes
4. **Add Parameter Validation**: Define parameter types and validation rules
5. **Apply Middleware**: Add middleware to routes that require authentication or other processing

The enhanced router is backward compatible with old route definitions, so you can migrate routes incrementally.

## Troubleshooting

### Common Issues and Solutions

1. **Route Not Found**
   - Check that the route is correctly defined in `app/Routes/routes.php`
   - Verify that the route pattern matches the URL format
   - Check HTTP method constraints (GET, POST, etc.)
   - Clear the route cache: `php tools/route_tool.php cache:clear`

2. **Controller or Action Not Found**
   - Verify that the controller class exists and is correctly namespaced
   - Check that the action method exists and is public
   - Use the `resolve` command to debug: `php tools/route_tool.php resolve /your/url`

3. **Parameter Validation Failing**
   - Check that parameter values match the defined constraints
   - Use the appropriate data types in your URLs
   - Review the validation rules in your route definition

4. **Middleware Issues**
   - Verify that middleware classes exist and implement the correct interface
   - Check the order of middleware execution
   - Ensure middleware is returning the next callback result

5. **Cache-Related Problems**
   - If routes aren't updating, try: `php tools/route_tool.php cache:clear`
   - Check that the `bootstrap/cache` directory is writable
   - Verify cache status: `php tools/route_tool.php cache:status`

### Debugging Tools

1. **Route List**: View all registered routes
   ```bash
   php tools/route_tool.php list --verbose
   ```

2. **Route Resolution**: Test how a URL is resolved
   ```bash
   php tools/route_tool.php resolve /your/url/here
   ```

3. **Route Information**: Get details about a specific route
   ```bash
   php tools/route_tool.php show your.route.name
   ```

## Conclusion

The enhanced SoVest routing system provides a flexible, powerful way to define routes in your application. By using features like HTTP method constraints, middleware, route groups, named routes, parameter validation, and route caching, you can create clean, maintainable, and high-performance routes for your application.

The addition of performance optimization through the OptimizedRouter and route caching system makes the routing infrastructure production-ready, with significant performance benefits over the standard routing implementation.

The comprehensive command-line tools provided in `route_tool.php` make it easy to manage, debug, and optimize your routes during both development and production deployment.

For more information or help with specific routing scenarios, contact the SoVest development team.