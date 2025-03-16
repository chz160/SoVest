# SoVest URL Generation Helpers

This document explains how to use the URL generation helper functions and classes provided by SoVest.

## Overview

The URL generation system provides a convenient way to generate URLs from named routes or controller/action pairs. This makes your code more maintainable by avoiding hardcoded URLs.

## Helper Functions

### sovest_route()

Generate a URL from a named route.

```php
// Basic usage
sovest_route('home');                               // Output: /

// With parameters
sovest_route('predictions.view', ['id' => 123]);     // Output: /predictions/view/123

// With absolute URL (includes domain)
sovest_route('home', [], true);                      // Output: http://example.com/
```

### sovest_route_action()

Generate a URL from a controller and action.

```php
// Basic usage
sovest_route_action('HomeController', 'index');      // Output: /

// With parameters
sovest_route_action('PredictionController', 'view', ['id' => 123]); // Output: /predictions/view/123
```

### sovest_route_absolute()

Generate an absolute URL from a named route (includes domain).

```php
sovest_route_absolute('home');                       // Output: http://example.com/
```

### sovest_get_named_routes()

Return all named routes for debugging purposes.

```php
$routes = sovest_get_named_routes();
print_r($routes);
```

## Usage in Views

URL generation is particularly useful in views for creating links, forms, etc.

```php
<!-- Link to the home page -->
<a href="<?= sovest_route('home') ?>">Home</a>

<!-- Link to a prediction -->
<a href="<?= sovest_route('predictions.view', ['id' => $prediction->id]) ?>">View Prediction</a>

<!-- Form submission -->
<form action="<?= sovest_route('predictions.store') ?>" method="POST">
    <!-- Form fields -->
</form>
```

## Usage in Controllers

In controllers, you can use URL generation for redirects and other operations.

```php
// Redirect to the home page
header('Location: ' . sovest_route('home'));
exit;

// Redirect to a prediction view
header('Location: ' . sovest_route('predictions.view', ['id' => $newPredictionId]));
exit;
```

## Available Route Names

The SoVest application defines the following named routes that you can use with the `sovest_route()` function:

### Authentication Routes
- `home` - Home page
- `login.form` - Login form
- `login.submit` - Login form submission
- `register.form` - Registration form
- `register.submit` - Registration form submission
- `logout` - Log out action

### User Routes
- `user.home` - User dashboard
- `user.account` - User account page
- `user.leaderboard` - Leaderboard page

### Prediction Routes
- `predictions.index` - List predictions
- `predictions.view` - View a prediction (requires 'id' parameter)
- `predictions.trending` - View trending predictions
- `predictions.create` - Create prediction form
- `predictions.store` - Store new prediction
- `predictions.edit` - Edit prediction form (requires 'id' parameter)
- `predictions.update` - Update a prediction (requires 'id' parameter)
- `predictions.delete` - Delete a prediction (requires 'id' parameter)
- `predictions.vote` - Vote on a prediction (requires 'id' parameter)

### API Routes
- `api.predictions` - Predictions API
- `api.search` - Search API
- `api.stocks` - Stocks API
- `api.stocks.get` - Get a specific stock (requires 'symbol' parameter)

### Page Routes
- `pages.about` - About page
- `search` - Search page

### Admin Routes
- `admin.dashboard` - Admin dashboard
- `admin.users.index` - List users
- `admin.users.view` - View user details (requires 'id' parameter)

## Advanced Usage

For more advanced usage, you can use the `RoutingHelper` class directly:

```php
use App\Helpers\RoutingHelper;

$helper = new RoutingHelper();
$url = $helper->url('home');
```

## Legacy Support

For backward compatibility, the helper also supports generating URLs using the controller.action format:

```php
sovest_route('home.index');        // Same as sovest_route('home')
sovest_route('prediction.view', ['id' => 123]); // Same as sovest_route('predictions.view', ['id' => 123])
```