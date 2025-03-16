# Route Caching System

The SoVest application includes a route caching and optimization system to improve performance in route resolution. This document explains how the system works and how to use it.

## Overview

The route caching and optimization system works through two main components:

1. **OptimizedRouter**: A specialized router that pre-processes routes for faster lookup by:
   - Separating static and dynamic routes for optimized matching
   - Indexing routes by HTTP method for quicker access
   - Providing a fast path for common routes

2. **RouteCacheGenerator**: A caching system that:
   - Compiles routes defined in `app/Routes/routes.php` into an optimized PHP file
   - Stores this compiled file in the `bootstrap/cache` directory
   - Preprocesses and flattens nested route groups
   - Automatically detects when source routes have changed

## Performance Improvements

The OptimizedRouter provides significant performance benefits:

- **44% Faster Route Resolution**: Benchmarks show the OptimizedRouter is 44% faster than the standard Router
- **Improved Scaling**: Performance improvement increases with the number of routes in the application
- **Reduced CPU Usage**: More efficient route matching means less CPU time per request

## Using the Route Cache

### Command Line Interface

The route caching system can be managed using the `route_tool.php` command line utility:

```bash
# Generate the route cache
php tools/route_tool.php cache:generate

# Force regeneration of the route cache
php tools/route_tool.php cache:generate --force

# Clear the route cache
php tools/route_tool.php cache:clear

# View route cache status
php tools/route_tool.php cache:status

# Benchmark route performance with and without cache
php tools/route_tool.php benchmark --iterations=5000
php tools/route_tool.php benchmark --iterations=5000 --cached
```

### Programmatically

The route cache is used automatically by the `Router` class. When instantiating a router, you can control whether it uses the cache:

```php
// Use cache (default behavior)
$router = new \App\Routes\Router($routes, '', null, true);

// Disable cache (for development or debugging)
$router = new \App\Routes\Router($routes, '', null, false);
```

## Cache Invalidation

The route cache automatically checks if the source routes file has been modified since the cache was generated. If the source has changed, the cache is considered invalid and the routes will be loaded directly from the source file.

You can manually invalidate the cache by:

1. Running `php tools/route_tool.php cache:clear`
2. Deleting the cache file at `bootstrap/cache/routes.php`

## Best Practices

1. **Development Environment**:
   - It's usually best to disable route caching during development to immediately see changes to routes
   - Use `php tools/route_tool.php cache:clear` when making changes to routes

2. **Production Environment**:
   - Always use route caching in production for optimal performance
   - Add a step to your deployment process to regenerate the route cache

3. **Benchmarking**:
   - Use the benchmark command to measure the performance improvement from caching
   - Performance benefits vary depending on the number and complexity of routes

## Implementation Details

The route caching system consists of several components:

1. **RouteCacheGenerator**: Responsible for generating and managing the cache file
2. **Router Modifications**: Extended to load routes from cache when available
3. **Command Line Tools**: Added to manage cache generation and invalidation
4. **Benchmarking**: Tools for measuring performance improvements

### Cache File Structure

The generated cache file contains:

- Timestamp of when the cache was generated
- Last modification time of the source file
- Path to the source file
- Compiled route definitions

This allows the system to check if the cache is still valid without parsing the entire routes configuration.

## Troubleshooting

If you encounter issues with the route caching system:

1. **Routes Not Updating**: Clear the cache with `php tools/route_tool.php cache:clear`
2. **Performance Issues**: Run benchmarks to measure actual performance
3. **Cache Directory**: Ensure the `bootstrap/cache` directory exists and is writable

## Technical Details

- Cache files are stored in the `bootstrap/cache` directory
- The cache file contains metadata for automatic invalidation
- The Router class has built-in benchmarking capabilities to measure performance
- Cache loading is designed to fail safely, falling back to the source routes

## Extending the System

The route caching system can be extended to include additional optimizations:

- Precomputed regular expressions for route patterns
- Optimized middleware chains
- Route grouping for more efficient lookups