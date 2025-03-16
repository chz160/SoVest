<?php
/**
 * SoVest - Service Provider
 *
 * This class manages the dependency injection container and provides
 * access to service instances throughout the application.
 * It enhances the PHP-DI container to manage controller dependencies.
 */

namespace App\Services;

use DI\Container;
use DI\ContainerBuilder;
use Exception;

class ServiceProvider
{
    /**
     * @var Container|null Singleton instance of the container
     */
    private static $container = null;

    /**
     * Get the DI container instance
     *
     * @return Container
     */
    public static function getContainer()
    {
        if (self::$container === null) {
            self::initializeContainer();
        }
        
        return self::$container;
    }

    /**
     * Initialize the container with all service definitions
     * 
     * @return void
     */
    private static function initializeContainer()
    {
        try {
            // Initialize the container if it doesn't exist
            $containerBuilder = new ContainerBuilder();
            $containerBuilder->useAutowiring(true);
            
            // Add definitions from bootstrap/container.php
            $definitions = require APP_BASE_PATH . '/bootstrap/container.php';
            $containerBuilder->addDefinitions($definitions);
            
            self::$container = $containerBuilder->build();
        } catch (Exception $e) {
            error_log("Container initialization error: " . $e->getMessage());
            // Fall back to a basic container if something goes wrong
            self::$container = new Container();
        }
    }
    
    /**
     * Get a controller instance with dependencies injected
     *
     * @param string $controllerClass Fully qualified controller class name
     * @return object The controller instance with dependencies injected
     * @throws Exception If the controller class doesn't exist
     */
    public static function getController($controllerClass)
    {
        $container = self::getContainer();
        
        // If the controller class doesn't exist, throw an exception
        if (!class_exists($controllerClass)) {
            throw new Exception("Controller class '{$controllerClass}' not found");
        }
        
        try {
            // Use the container to create and inject dependencies
            return $container->get($controllerClass);
        } catch (Exception $e) {
            error_log("Error creating controller: " . $e->getMessage());
            
            // Fall back to direct instantiation if DI fails
            // This maintains backward compatibility with existing code
            return new $controllerClass();
        }
    }
    
    /**
     * Get a service instance by class name
     *
     * @param string $serviceName The service class name
     * @return object The service instance
     * @throws Exception If the service can't be resolved
     */
    public static function getService($serviceName)
    {
        return self::getContainer()->get($serviceName);
    }
    
    /**
     * Check if a service exists in the container
     *
     * @param string $serviceName The service class name
     * @return bool Whether the service exists
     */
    public static function hasService($serviceName)
    {
        return self::getContainer()->has($serviceName);
    }
    
    /**
     * Set a container definition at runtime
     *
     * @param string $id The service name/id
     * @param mixed $definition The service definition
     * @return void
     */
    public static function set($id, $definition)
    {
        self::getContainer()->set($id, $definition);
    }
}