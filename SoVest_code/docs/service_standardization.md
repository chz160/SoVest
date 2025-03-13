# Service Layer Standardization

## Introduction

The service layer in the SoVest application provides a critical abstraction between controllers and data access/business logic. It encapsulates complex operations, maintains separation of concerns, and enables dependency injection for better testability. This document outlines the standards for implementing services in the restructured SoVest application.

As SoVest transitions from the legacy architecture to a modern MVC structure, standardizing the service layer is essential for:

1. Maintaining consistency across all service implementations
2. Enabling proper dependency injection
3. Ensuring backward compatibility with existing code
4. Supporting testability through interface-based design
5. Facilitating future extensions and modifications

## Service Architecture Overview

The service layer follows these key architectural principles:

1. **Interface-Based Design**: Each service defines an interface that specifies its contract
2. **Dependency Injection**: Services receive their dependencies via constructor parameters
3. **Single Responsibility**: Each service focuses on a specific domain or functionality
4. **Testability**: Service design facilitates unit testing with easy mocking of dependencies
5. **Backward Compatibility**: Services maintain compatibility with existing code through transition patterns

## Directory Structure

```
SoVest_code/
├── app/
│   └── Services/
│       ├── Interfaces/
│       │   ├── AuthServiceInterface.php
│       │   ├── DatabaseServiceInterface.php
│       │   └── ...
│       ├── ServiceProvider.php
│       └── ServiceFactory.php
├── services/
│   ├── AuthService.php
│   ├── DatabaseService.php
│   └── ...
└── bootstrap/
    └── container.php
```

- **app/Services/Interfaces/**: Contains all service interfaces
- **services/**: Contains service implementations (will be migrated to app/Services/ in future)
- **app/Services/ServiceProvider.php**: Manages dependency injection for services
- **app/Services/ServiceFactory.php**: Transitional factory for service creation
- **bootstrap/container.php**: Configures service bindings in the DI container

## Service Interface Standards

### Naming Convention

Service interfaces follow these naming conventions:

- Interface names should end with `Interface` suffix
- Interface names should reflect the service's domain (e.g., `AuthServiceInterface`)
- Method names should be clear and descriptive of the action they perform
- Method names should use camelCase

### Location

All service interfaces should be placed in the `app/Services/Interfaces/` directory and use the `App\Services\Interfaces` namespace.

### Structure

Service interfaces should:

1. Declare all public methods that the service will expose
2. Include detailed PHPDoc comments for each method
3. Use type hints for parameters and return types when possible
4. Be focused on a specific domain or functionality

### Example Interface Template

```php
<?php
/**
 * SoVest - [Service Name] Interface
 *
 * This interface defines the contract for [service description]
 * in the SoVest application.
 */

namespace App\Services\Interfaces;

interface ExampleServiceInterface
{
    /**
     * [Method description]
     *
     * @param [type] $param [Parameter description]
     * @return [type] [Return value description]
     */
    public function exampleMethod($param);
    
    // Additional methods...
}
```

## Service Implementation Standards

### Naming Convention

Service implementations follow these naming conventions:

- Implementation names should match the interface name without the `Interface` suffix
- Class names should be clear and descriptive of the service's domain
- Method names should match those defined in the interface
- Private helper methods should use camelCase and be descriptive

### Location

Service implementations should be placed in the `services/` directory (short-term) and use the `Services` namespace. In the future, they will be migrated to the `app/Services/` directory with the `App\Services` namespace.

### Structure

Service implementations should:

1. Implement the corresponding interface
2. Use constructor dependency injection for dependencies
3. Include detailed PHPDoc comments for the class and methods
4. Handle errors gracefully with appropriate error logging
5. Maintain backward compatibility with existing code patterns

### Transitional Pattern for Backward Compatibility

To maintain backward compatibility while supporting dependency injection, service implementations should follow this transitional pattern:

1. Support constructor injection for dependencies
2. Maintain static `getInstance()` method for singleton access (if previously implemented)
3. Implement the corresponding interface
4. Catch and log exceptions to prevent bubbling up to controllers

### Example Implementation Template

```php
<?php
/**
 * SoVest - [Service Name]
 *
 * This service provides [service description]
 * following the service pattern established in the application.
 * It centralizes all [domain]-related logic for easier maintenance.
 */

namespace Services;

use App\Services\Interfaces\ExampleServiceInterface;
use Exception;

class ExampleService implements ExampleServiceInterface
{
    /**
     * @var ExampleService|null Singleton instance of the service
     */
    private static $instance = null;
    
    /**
     * @var SomeDependency Service dependency
     */
    private $dependency;

    /**
     * Get the singleton instance of ExampleService
     *
     * @return ExampleService
     */
    public static function getInstance()
    {
        if (self::$instance === null) {
            self::$instance = new self();
        }

        return self::$instance;
    }

    /**
     * Constructor - public to support dependency injection
     * while maintaining backward compatibility with singleton pattern
     * 
     * @param SomeDependency|null $dependency Optional dependency
     */
    public function __construct($dependency = null)
    {
        $this->dependency = $dependency;
        
        // If dependency wasn't injected, try to create it
        if ($this->dependency === null && class_exists('SomeDependency')) {
            // Try to get from ServiceFactory first
            if (class_exists('App\\Services\\ServiceFactory')) {
                $this->dependency = \App\Services\ServiceFactory::createSomeDependency();
            }
            // Fall back to direct instantiation
            else {
                $this->dependency = new \SomeDependency();
            }
        }
        
        // Additional initialization...
    }

    /**
     * [Method description] - Implementation of interface method
     *
     * @param [type] $param [Parameter description]
     * @return [type] [Return value description]
     */
    public function exampleMethod($param)
    {
        try {
            // Implementation...
            
            return $result;
        } catch (Exception $e) {
            error_log("[ExampleService] Error in exampleMethod: " . $e->getMessage());
            return false; // Or appropriate error response
        }
    }
    
    /**
     * Private helper method
     *
     * @param [type] $param [Parameter description]
     * @return [type] [Return value description]
     */
    private function helperMethod($param)
    {
        // Implementation...
    }
}
```

## Dependency Injection

### Service Registration in Container

All services should be registered in the DI container in `bootstrap/container.php` using these patterns:

1. Register interface-to-implementation bindings
2. Use factory functions to support both DI and backward compatibility
3. Register dependencies for automatic resolution

### Example Container Registration

```php
// Interface binding
ExampleServiceInterface::class => get(ExampleService::class),

// Implementation with backward compatibility
ExampleService::class => factory(function($container) {
    // Check for singleton instance first
    if (method_exists(ExampleService::class, 'getInstance') && 
        ExampleService::getInstance() !== null) {
        return ExampleService::getInstance();
    }
    
    // Resolve dependencies
    try {
        $dependency = $container->get(SomeDependency::class);
        return new ExampleService($dependency);
    } catch (Exception $e) {
        error_log("Error resolving ExampleService dependencies: " . $e->getMessage());
        return new ExampleService();
    }
}),
```

### Service Factory

During the transition to full dependency injection, the `ServiceFactory` class provides factory methods to create service instances with appropriate dependencies. New service implementations should add corresponding factory methods to `ServiceFactory`:

```php
/**
 * Create an ExampleService instance
 *
 * @param SomeDependency|null $dependency Optional dependency
 * @return ExampleService
 */
public static function createExampleService(SomeDependency $dependency = null)
{
    try {
        $container = self::getContainer();
        
        // If container exists and has ExampleService registered, use it
        if ($container && method_exists($container, 'has') && 
            $container->has(ExampleService::class)) {
            return $container->get(ExampleService::class);
        }
        
        // Fall back to direct instantiation with dependency injection
        return new ExampleService($dependency);
    } catch (Exception $e) {
        error_log("Error creating ExampleService: " . $e->getMessage());
        // Fall back to singleton pattern or direct instantiation
        return ExampleService::getInstance();
    }
}
```

## Error Handling

Services should handle errors using these guidelines:

1. Catch exceptions within methods to prevent bubbling up to controllers
2. Log errors with appropriate context using `error_log()`
3. Return meaningful responses that indicate success or failure
4. Include error messages for API responses, but avoid exposing sensitive information

Example:
```php
public function exampleMethod($param)
{
    try {
        // Implementation...
        if (!$this->validate($param)) {
            error_log("Validation failed for param: " . json_encode($param));
            return false;
        }
        
        $result = $this->processData($param);
        return $result;
    } catch (Exception $e) {
        error_log("Error in exampleMethod: " . $e->getMessage());
        return false; // Or appropriate error response
    }
}
```

## Service Migration Strategy

To migrate existing services to the new standard:

1. **Create Interface**: Define an interface in `app/Services/Interfaces/` that describes the service contract
2. **Update Implementation**: Modify the existing service to implement the interface
3. **Add Constructor Injection**: Update the constructor to accept dependencies
4. **Maintain Backward Compatibility**: Keep the `getInstance()` method for transition
5. **Register in Container**: Add service bindings to the container configuration
6. **Add Factory Method**: Create a factory method in `ServiceFactory` if needed
7. **Update References**: Gradually update controller references to use type-hinted interfaces
8. **Add Tests**: Create unit tests for the service implementation

## Testing Services

### Test Structure

Service tests should follow these guidelines:

1. Place test files in `tests/Unit/Services/`
2. Name test classes as `{ServiceName}Test`
3. Create separate test methods for each service method
4. Use mock objects for service dependencies
5. Test both success and failure cases

### Example Test Template

```php
<?php

namespace Tests\Unit\Services;

use PHPUnit\Framework\TestCase;
use App\Services\Interfaces\ExampleServiceInterface;
use Services\ExampleService;

class ExampleServiceTest extends TestCase
{
    /**
     * @var ExampleService
     */
    private $service;
    
    /**
     * @var MockObject
     */
    private $mockDependency;
    
    /**
     * Set up the test environment
     */
    protected function setUp(): void
    {
        // Create mock dependencies
        $this->mockDependency = $this->createMock(SomeDependency::class);
        
        // Create service instance with mock dependencies
        $this->service = new ExampleService($this->mockDependency);
    }
    
    /**
     * Test exampleMethod with valid parameters
     */
    public function testExampleMethodWithValidParams()
    {
        // Configure mocks
        $this->mockDependency->method('someMethod')
            ->willReturn(true);
        
        // Call service method
        $result = $this->service->exampleMethod('valid-param');
        
        // Assert results
        $this->assertTrue($result);
    }
    
    /**
     * Test exampleMethod with invalid parameters
     */
    public function testExampleMethodWithInvalidParams()
    {
        // Configure mocks
        $this->mockDependency->method('someMethod')
            ->willReturn(false);
        
        // Call service method
        $result = $this->service->exampleMethod('invalid-param');
        
        // Assert results
        $this->assertFalse($result);
    }
    
    // Additional test methods...
}
```

## Conclusion

Following these service standardization guidelines will help maintain consistency throughout the SoVest application, improve testability, and ensure a smooth transition from the legacy architecture to the modern MVC structure.

As the application evolves, these standards may be refined and updated to address emerging patterns and requirements. The goal is to create a maintainable, testable, and extensible service layer that provides a solid foundation for the application's business logic.