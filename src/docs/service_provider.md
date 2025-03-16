# ServiceProvider Implementation

TODO: this needs to be rewritten as we are using the Laravel DI framework instead of a handrolled solition.

## Overview
The ServiceProvider class enhances the PHP-DI container to manage service dependencies for controllers in the SoVest application. It provides a central access point for retrieving service instances and instantiating controllers with their dependencies.

## Key Features
1. **Centralized Container Access**: Provides static methods to access the container across the application
2. **Controller Dependency Injection**: Creates controller instances with their service dependencies automatically injected
3. **Service Registry**: Manages service bindings for all existing services
4. **Backward Compatibility**: Maintains compatibility with existing singleton service patterns

## Usage

### Getting a Service
```php
use App\Services\ServiceProvider;
use Services\AuthService;

// Get a service instance
$authService = ServiceProvider::getService(AuthService::class);
```

### Getting a Controller with Dependencies
```php
use App\Services\ServiceProvider;

// Get a controller instance with dependencies injected
$authController = ServiceProvider::getController('App\\Controllers\\AuthController');
```

### Checking for a Service
```php
use App\Services\ServiceProvider;
use Services\AuthService;

if (ServiceProvider::hasService(AuthService::class)) {
    // Service exists
}
```

## Container Configuration
The container configuration is defined in `bootstrap/container.php` and includes bindings for:

- AuthService
- DatabaseService
- SearchService
- StockDataService
- PredictionScoringService

## Integration with Router
To fully utilize the ServiceProvider, the Router class should be updated to use the ServiceProvider for creating controller instances:

```php
// In Router.php, replace:
$controller = new $controllerName();

// With:
$controller = ServiceProvider::getController($controllerName);
```

## Testing
Run the ServiceProviderTest to verify that the implementation works correctly:

```bash
php tests/ServiceProviderTest.php
```