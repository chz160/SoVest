# Controller Standardization Guide

## Introduction

This document outlines the standardized approach for implementing controllers in the SoVest application. As part of Phase 3 (Application Restructuring) of our development plan, we are unifying controller implementation patterns to improve maintainability, reduce code duplication, and establish consistent patterns throughout the application.

The standardized controller approach is built around these core principles:

1. **Dependency Injection**: Controllers receive their dependencies via constructor injection
2. **Standard Validation**: Consistent validation patterns for both request validation and model validation
3. **Uniform Response Handling**: Standardized approaches for both API and web responses
4. **Consistent Error Handling**: Clear error reporting with appropriate status codes
5. **Middleware Support**: Standard application of middleware for cross-cutting concerns

Following these standards will ensure a consistent developer experience, predictable behavior, and easier maintenance across the application.

## Validation Standards

SoVest supports two complementary validation approaches that should be used consistently:

### 1. Controller-based Request Validation

Use the `validateRequest()` method for validating incoming request data:

```php
protected function validateRequest(array $rules, array $data = null)
{
    // Validation implementation in base Controller class
    // Returns true if valid, or array of errors if invalid
}
```

Example usage:

```php
public function store()
{
    $rules = [
        'username' => 'required|min:3|max:50',
        'email' => 'required|email',
        'password' => 'required|min:8'
    ];
    
    $validationResult = $this->validateRequest($rules);
    
    if ($validationResult !== true) {
        return $this->handleValidationResult(
            $validationResult,
            function() {
                // Success callback - won't be called if validation fails
            },
            'user/register' // View to render if web request
        );
    }
    
    // Proceed with storing the user...
}
```

#### Standard Validation Rules

| Rule | Description | Example |
|------|-------------|---------|
| required | Field must be present and not empty | `'name' => 'required'` |
| email | Field must be a valid email address | `'email' => 'required|email'` |
| numeric | Field must be a number | `'age' => 'numeric'` |
| min | Field must have a minimum value/length | `'password' => 'min:8'` |
| max | Field must have a maximum value/length | `'username' => 'max:50'` |
| in | Field must be one of the specified values | `'status' => 'in:active,pending,inactive'` |
| date | Field must be a valid date | `'birth_date' => 'date'` |
| regex | Field must match the regex pattern | `'code' => 'regex:/^[A-Z0-9]{6}$/'` |

### 2. Model-based Validation

For model data validation, use the `ValidationTrait` that's included in our models:

```php
// Create model instance with data
$user = new User([
    'username' => $this->input('username'),
    'email' => $this->input('email'),
    'password' => $this->input('password')
]);

// Validate the model
if ($user->validate()) {
    // Validation passed, save the model
    $user->save();
    return $this->respond('user/profile', ['user' => $user], 'User created successfully');
} else {
    // Get validation errors from the model
    $errors = $user->getErrors();
    return $this->respondWithError('user/register', ['user' => $user], 'Validation failed');
}
```

### Recommended Pattern: handleValidationResult

For consistent validation handling, use the `handleValidationResult()` method:

```php
public function update($id)
{
    $rules = [
        'name' => 'required|max:255',
        'email' => 'required|email',
        'phone' => 'numeric'
    ];
    
    return $this->handleValidationResult(
        $this->validateRequest($rules),
        function() use ($id) {
            // This callback only runs if validation passes
            $user = User::find($id);
            $user->name = $this->input('name');
            $user->email = $this->input('email');
            $user->phone = $this->input('phone');
            $user->save();
            
            return $this->respond(
                'user/profile',
                ['user' => $user],
                'User profile updated successfully'
            );
        },
        'user/edit', // View to render on validation failure
        ['id' => $id] // Additional data for the error view
    );
}
```

## Response Handling Standards

Controllers must provide consistent responses for both API and web requests. The base Controller class provides several methods to help with this.

### 1. API Responses

For API endpoints, use the following methods:

```php
// Basic JSON response
$this->json(['key' => 'value'], 200);

// Success response with standard format
$this->jsonSuccess('Operation completed successfully', ['result' => $data]);

// Error response with standard format
$this->jsonError('Invalid data provided', ['field_errors' => $errors], 422);
```

#### Standard API Response Format

Success responses:
```json
{
    "success": true,
    "message": "Operation completed successfully",
    "data": {
        "result": "..."
    }
}
```

Error responses:
```json
{
    "success": false,
    "message": "Invalid data provided",
    "errors": {
        "field_errors": {
            "username": ["Username is required"]
        }
    }
}
```

### 2. Web Responses

For web requests, use the following methods:

```php
// Render a view with data
$this->render('user/profile', ['user' => $user]);

// Add a success message and redirect
$this->withSuccess('User created successfully');
$this->redirect('user/profile');

// Add an error message and re-render form
$this->withError('Invalid username provided');
$this->render('user/register', ['input' => $this->request]);
```

### 3. Unified Response Handling

For controllers that handle both API and web requests, use these unified methods:

```php
// Success response for both API and web
$this->respond(
    'user/profile',              // View to render for web requests
    ['user' => $user],           // Data for both API and web responses
    'User created successfully', // Success message
    '/user/profile'              // Optional redirect URL for web requests
);

// Error response for both API and web
$this->respondWithError(
    'user/register',           // View to render for web requests
    ['errors' => $errors],     // Data for both API and web responses
    'Invalid data provided',   // Error message
    null,                      // Optional redirect URL for web requests
    422                        // HTTP status code for API requests
);
```

## Error Handling Standards

### HTTP Status Codes

Use appropriate HTTP status codes for API responses:

| Status Code | Description | When to Use |
|-------------|-------------|------------|
| 200 | OK | Successful request |
| 201 | Created | Resource successfully created |
| 204 | No Content | Successful request with no content to return |
| 400 | Bad Request | Invalid request format or parameters |
| 401 | Unauthorized | Authentication required |
| 403 | Forbidden | Authentication succeeded but user lacks permissions |
| 404 | Not Found | Resource not found |
| 422 | Unprocessable Entity | Validation failed |
| 500 | Internal Server Error | Server error occurred |

### Error Categories

Organize errors into these categories:

1. **Validation Errors**: Related to invalid input data
2. **Authentication Errors**: Related to authentication failures
3. **Authorization Errors**: Related to permission issues
4. **Resource Errors**: Related to resources not found or unavailable
5. **Server Errors**: Related to internal server failures

### Error Message Format

Follow these guidelines for error messages:

1. Be specific about what failed
2. Use consistent terminology
3. Avoid technical jargon in user-facing messages
4. Provide guidance on how to fix the error when possible

Example error responses:

```php
// Validation error
$this->jsonError(
    'Please correct the errors below',
    [
        'username' => ['Username must be at least 3 characters'],
        'email' => ['Email address is not valid']
    ],
    422
);

// Authentication error
$this->jsonError(
    'Authentication required',
    ['auth' => ['Please log in to access this resource']],
    401
);

// Resource error
$this->jsonError(
    'Resource not found',
    ['id' => ['The requested prediction was not found']],
    404
);
```

## Migration Guide

Follow these steps to migrate existing controllers to the new standardized approach:

### Step 1: Update Constructor with Dependency Injection

```php
// Before
class StockController extends Controller
{
    private $stockService;
    
    public function __construct()
    {
        parent::__construct();
        $this->stockService = StockService::getInstance();
    }
}

// After
use App\Services\Interfaces\StockDataServiceInterface;

class StockController extends Controller
{
    private $stockService;
    
    public function __construct(
        AuthServiceInterface $authService = null,
        StockDataServiceInterface $stockService = null,
        array $services = []
    ) {
        parent::__construct($authService, $services);
        
        // Initialize stock service with dependency injection
        $this->stockService = $stockService;
        
        // Fallback to ServiceFactory for backward compatibility
        if ($this->stockService === null) {
            $this->stockService = ServiceFactory::createStockDataService();
        }
    }
}
```

### Step 2: Standardize Validation Approach

```php
// Before - mixed validation approaches
public function store()
{
    // Direct validation in controller
    if (empty($_POST['stock_id'])) {
        $this->withError('Stock is required');
        return $this->render('prediction/create');
    }
    
    if (!is_numeric($_POST['target_price'])) {
        $this->withError('Target price must be a number');
        return $this->render('prediction/create');
    }
    
    // Create and save without validation
    $prediction = new Prediction([
        'user_id' => $userId,
        'stock_id' => $_POST['stock_id'],
        'target_price' => $_POST['target_price']
    ]);
    $prediction->save();
}

// After - standardized validation
public function store()
{
    $rules = [
        'stock_id' => 'required|numeric',
        'target_price' => 'required|numeric'
    ];
    
    return $this->handleValidationResult(
        $this->validateRequest($rules),
        function() use ($userId) {
            $prediction = new Prediction([
                'user_id' => $userId,
                'stock_id' => $this->input('stock_id'),
                'target_price' => $this->input('target_price')
            ]);
            
            if ($prediction->validate()) {
                $prediction->save();
                return $this->respond(
                    'prediction/view',
                    ['prediction' => $prediction],
                    'Prediction created successfully'
                );
            } else {
                return $this->respondWithError(
                    'prediction/create',
                    ['prediction' => $prediction],
                    'Invalid prediction data'
                );
            }
        },
        'prediction/create'
    );
}
```

### Step 3: Standardize Response Handling

```php
// Before - inconsistent response handling
public function search()
{
    $query = $_GET['q'] ?? '';
    $results = $this->searchService->search($query);
    
    if (isset($_GET['api'])) {
        // API response
        header('Content-Type: application/json');
        echo json_encode(['results' => $results]);
        exit;
    } else {
        // Web response
        $this->viewData['results'] = $results;
        $this->viewData['query'] = $query;
        include('views/search_results.php');
    }
}

// After - standardized response handling
public function search()
{
    $query = $this->input('q', '');
    $results = $this->searchService->search($query);
    
    return $this->respond(
        'search/results',
        [
            'results' => $results,
            'query' => $query
        ],
        'Search completed'
    );
}
```

### Step 4: Implement Middleware

```php
// Before - direct authentication check
public function profile()
{
    if (!isset($_SESSION['user_id'])) {
        header('Location: login.php');
        exit;
    }
    
    $userId = $_SESSION['user_id'];
    $user = User::find($userId);
    
    $this->render('user/profile', ['user' => $user]);
}

// After - using middleware
public function __construct(AuthServiceInterface $authService = null, array $services = [])
{
    parent::__construct($authService, $services);
    
    // Add authentication middleware
    $this->middleware(new AuthMiddleware());
}

public function profile()
{
    // Middleware will handle authentication check
    $userId = $this->getAuthUser()['id'];
    $user = User::find($userId);
    
    $this->render('user/profile', ['user' => $user]);
}
```

### Common Pitfalls to Avoid

1. **Mixed Response Formats**: Don't mix direct output with controller response methods
2. **Inconsistent Error Handling**: Don't use different formats for similar errors
3. **Manual Session Handling**: Use AuthMiddleware instead of direct session checks
4. **Direct Superglobal Access**: Use $this->input() method instead of accessing $_POST, $_GET directly
5. **Missing Validation**: Always validate input data before processing

By following this standardization guide, controllers in the SoVest application will be consistent, maintainable, and easier to understand.