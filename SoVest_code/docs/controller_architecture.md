# Controller Architecture

## Overview

This document outlines the controller architecture in SoVest, focusing on the dependency injection approach, standardized request validation, and response handling. It also provides guidance for creating new controllers and migrating existing ones to the new architecture.

## Table of Contents

1. [Dependency Injection Approach](#dependency-injection-approach)
2. [Request Validation](#request-validation)
3. [Response Handling](#response-handling)
4. [Creating a New Controller](#creating-a-new-controller)
5. [Migrating Existing Controllers](#migrating-existing-controllers)
6. [Best Practices](#best-practices)

## Dependency Injection Approach

### Overview

SoVest's controller architecture now uses dependency injection (DI) to manage dependencies between components. This approach:

- Reduces tight coupling between classes
- Makes testing easier by allowing mock objects
- Centralizes dependency management
- Enhances maintenance and readability

### How It Works

1. **ServiceProvider Class**:
   The `App\Services\ServiceProvider` class manages the DI container and provides methods to retrieve service instances.
   
   ```php
   // ServiceProvider provides access to services
   namespace App\Services;
   
   class ServiceProvider
   {
       // Methods to get services and controllers
       public static function getController($controllerClass);
       public static function getService($serviceName);
   }
   ```

2. **Container Configuration**:
   Services are registered in `bootstrap/container.php` using the PHP-DI container:
   
   ```php
   // bootstrap/container.php
   return [
       // Service definitions
       AuthService::class => factory(function() {
           return AuthService::getInstance();
       }),
       
       DatabaseService::class => factory(function() {
           return DatabaseService::getInstance();
       }),
       
       // Other service definitions...
   ];
   ```

3. **Controller Constructor Injection**:
   Controllers receive their dependencies through constructor parameters:
   
   ```php
   class AuthController extends Controller
   {
       protected $authService;
       
       public function __construct(AuthService $authService = null)
       {
           parent::__construct($authService);
       }
   }
   ```

4. **Router Integration**:
   The router uses `ServiceProvider::getController()` to instantiate controllers with their dependencies:
   
   ```php
   // Inside Router::dispatch()
   $controller = \App\Services\ServiceProvider::getController($controllerName);
   ```

### Backward Compatibility

For backward compatibility, the system falls back to the singleton pattern if dependency injection fails:

```php
// Base Controller fallback for AuthService
if ($this->authService === null && class_exists('Services\\AuthService')) {
    $this->authService = AuthService::getInstance();
}
```

## Request Validation

Controllers provide standardized methods for validating request data.

### Basic Validation

```php
public function login()
{
    $validation = $this->validateRequest([
        'email' => 'required|email',
        'password' => 'required|min:6'
    ]);
    
    if ($validation !== true) {
        // Handle validation errors
    }
    
    // Continue with validated data
    $email = $this->input('email');
    $password = $this->input('password');
}
```

### Validation Rules

The following validation rules are supported:

- `required`: The field must be present and not empty
- `email`: The field must be a valid email address
- `numeric`: The field must be a number
- `min:n`: The field must be at least n characters
- `max:n`: The field must not be more than n characters

### Handling Validation Results

Controllers provide methods to handle validation results consistently:

```php
// Web request validation
$validation = $this->validateRequest($rules);
if ($validation !== true) {
    return $this->redirect('login.php', ['error' => 'invalid_credentials']);
}

// API request validation
$validation = $this->validateJsonRequest($rules);
// If validation fails, validateJsonRequest automatically sends a JSON error response
```

### Unified Validation Handling

For controllers that need to handle both web and API requests:

```php
$validation = $this->validateRequest($rules);
$this->handleValidationResult($validation, 
    function() {
        // Success callback
        return $this->processForm();
    }, 
    'view/form' // View to render on validation failure
);
```

## Response Handling

The controller architecture provides standardized methods for consistent response handling across the application.

### Web Responses

```php
// Render a view with data
$this->render('user/profile', [
    'user' => $user,
    'stats' => $stats
]);

// Redirect with parameters
$this->redirect('login.php', ['error' => 'session_expired']);

// Set flash message and render
$this->withSuccess('Profile updated successfully')
     ->render('user/profile', ['user' => $user]);
```

### API Responses

```php
// Return JSON data
$this->json(['user' => $user, 'token' => $token]);

// Return JSON success response
$this->jsonSuccess('Login successful', ['user' => $user]);

// Return JSON error response
$this->jsonError('Invalid credentials', ['field' => 'password']);
```

### Unified Response Handling

For controllers that need to handle both web and API requests:

```php
// Respond based on request type
$this->respond(
    'user/profile',       // View for web requests
    ['user' => $user],    // Data for both web and API
    'Profile updated',    // Success message
    '/dashboard'          // Redirect URL for web (optional)
);

// Error response based on request type
$this->respondWithError(
    'user/profile',       // View for web requests
    ['errors' => $errors], // Data for both web and API
    'Update failed',      // Error message
    null,                 // No redirect
    400                   // HTTP status code for API
);
```

## Creating a New Controller

Here's a complete example of creating a new controller using the dependency injection approach:

```php
<?php

namespace App\Controllers;

use Database\Models\User;
use Services\AuthService;
use Services\ProfileService;

class ProfileController extends Controller
{
    /**
     * @var ProfileService Profile service instance
     */
    protected $profileService;
    
    /**
     * Constructor with dependency injection
     */
    public function __construct(AuthService $authService = null, ProfileService $profileService = null)
    {
        parent::__construct($authService);
        $this->profileService = $profileService;
    }
    
    /**
     * Display user profile
     */
    public function show()
    {
        // Require authentication
        $this->requireAuth();
        
        // Get the current user
        $user = $this->getAuthUser();
        
        // Get profile data from service
        $profileData = $this->profileService->getUserProfile($user['id']);
        
        // Render the profile view
        $this->render('user/profile', [
            'user' => $user,
            'profile' => $profileData
        ]);
    }
    
    /**
     * Update user profile
     */
    public function update()
    {
        // Require authentication
        $this->requireAuth();
        
        // Validate the request
        $validation = $this->validateRequest([
            'name' => 'required|min:2',
            'email' => 'required|email',
            'bio' => 'max:500'
        ]);
        
        // Handle validation result
        return $this->handleValidationResult(
            $validation,
            function() {
                // Get current user
                $user = $this->getAuthUser();
                
                // Extract form data
                $profileData = [
                    'name' => $this->input('name'),
                    'email' => $this->input('email'),
                    'bio' => $this->input('bio')
                ];
                
                // Update profile using service
                $result = $this->profileService->updateProfile($user['id'], $profileData);
                
                // Respond based on request type
                $this->respond(
                    'user/profile',
                    ['user' => $user, 'profile' => $profileData],
                    'Profile updated successfully'
                );
            },
            'user/profile',
            ['user' => $this->getAuthUser()]
        );
    }
}
```

### Defining Routes

After creating a controller, define routes in `app/Routes/routes.php` to map URLs to your controller actions:

```php
// Using the enhanced router syntax
$router->get('/profile', 'ProfileController@show', 'profile.show');
$router->post('/profile/update', 'ProfileController@update', 'profile.update');

// Legacy route format (for backward compatibility)
return [
    '/profile' => ['controller' => 'ProfileController', 'action' => 'show'],
    '/profile/update' => ['controller' => 'ProfileController', 'action' => 'update']
];
```

## Migrating Existing Controllers

This section provides guidance for migrating existing controllers to the new dependency injection approach.

### Step 1: Update the Constructor

Replace direct service instantiation with constructor dependency injection:

**Before:**
```php
class SearchController extends Controller
{
    protected $searchService;
    
    public function __construct()
    {
        parent::__construct();
        require_once __DIR__ . '/../../services/SearchService.php';
        $this->searchService = \Services\SearchService::getInstance();
    }
}
```

**After:**
```php
class SearchController extends Controller
{
    protected $searchService;
    
    public function __construct(AuthService $authService = null, SearchService $searchService = null)
    {
        parent::__construct($authService);
        $this->searchService = $searchService;
        
        // Fallback for backward compatibility
        if ($this->searchService === null) {
            require_once __DIR__ . '/../../services/SearchService.php';
            $this->searchService = \Services\SearchService::getInstance();
        }
    }
}
```

### Step 2: Standardize Validation

Replace custom validation with standardized methods:

**Before:**
```php
$email = $_POST['email'] ?? '';
if (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
    return $this->redirect('login.php', ['error' => 'invalid_email']);
}
```

**After:**
```php
$validation = $this->validateRequest([
    'email' => 'required|email'
]);

if ($validation !== true) {
    return $this->redirect('login.php', ['error' => 'invalid_email']);
}

$email = $this->input('email');
```

### Step 3: Standardize Response Handling

Replace custom response handling with standardized methods:

**Before:**
```php
if ($isAjax) {
    header('Content-Type: application/json');
    echo json_encode(['success' => true, 'message' => 'Search completed']);
    exit;
} else {
    $_SESSION['success'] = 'Search completed';
    header('Location: search_results.php');
    exit;
}
```

**After:**
```php
$this->respond(
    'search/results',
    ['results' => $results],
    'Search completed',
    'search_results.php'
);
```

### Step 4: Use Middleware for Cross-Cutting Concerns

Replace repetitive authentication checks with middleware:

**Before:**
```php
public function showProfile()
{
    if (!isset($_COOKIE["userID"])) {
        header("Location: login.php");
        exit;
    }
    
    // Rest of the method...
}
```

**After:**
```php
// In the controller constructor or in bootstrap
$this->middleware(new \App\Middleware\AuthMiddleware());

// OR in the routes file
$router->get('/profile', 'ProfileController@show')->addMiddleware(AuthMiddleware::class);

// Then the action doesn't need to check auth
public function showProfile()
{
    // Method logic without auth check...
}
```

## Best Practices

### Use Type Hints

Always use type hints for injected dependencies to help the DI container:

```php
public function __construct(AuthService $authService, SearchService $searchService)
```

### Prefer Constructor Injection

Use constructor injection for required dependencies and setter injection for optional ones.

### Keep Controllers Lean

Controllers should delegate business logic to service classes and focus on:
- Request validation
- Coordinating service calls
- Preparing data for views
- Response generation

### Use Dependency Injection Consistently

Avoid mixing dependency injection with direct instantiation in new code.

### Follow Single Responsibility Principle

Each controller should focus on a specific area of functionality.

### Use Middleware for Cross-Cutting Concerns

Extract reusable logic like authentication, logging, or CSRF protection into middleware.

### Standardize Error Handling

Use the provided error handling methods consistently:
- `withError()` for view errors
- `withModelErrors()` for model validation errors
- `jsonError()` for API error responses

### Test Controllers

Write unit tests for controllers by mocking service dependencies:

```php
// Example of testing a controller with mocked services
public function testLogin()
{
    // Mock AuthService
    $authService = $this->createMock(AuthService::class);
    $authService->method('login')
                ->willReturn(['id' => 1, 'email' => 'user@example.com']);
    
    // Inject mocked service
    $controller = new AuthController($authService);
    
    // Test the controller action
    // ...
}
```