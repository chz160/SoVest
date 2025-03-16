# SoVest MVC Structure

This directory contains the MVC (Model-View-Controller) architecture for the SoVest application. The MVC pattern separates the application into three main components:

- **Model**: Handles data and business logic
- **View**: Displays data to the user
- **Controller**: Handles user input and updates the model and view accordingly

## Directory Structure

- **Controllers/**: Contains controller classes that handle requests and coordinate between models and views
- **Views/**: Contains view files that display data to the user
- **Helpers/**: Contains helper classes for common tasks
- **Routes/**: Contains routing configuration for mapping URLs to controllers

## How to Use

### Controllers

Controllers should extend the base `App\Controllers\Controller` class. This provides access to common functionality such as:

- Rendering views: `$this->render('view_name', $data)`
- Accessing request data: `$this->input('field_name')`
- Redirecting: `$this->redirect('url', ['param' => 'value'])`
- Working with JSON responses: `$this->jsonSuccess('message', $data)`

Example controller:

```php
<?php

namespace App\Controllers;

use Database\Models\User;

class UserController extends Controller
{
    public function profile()
    {
        $this->requireAuth();
        
        $user = $this->getAuthUser();
        
        $this->render('user/profile', [
            'user' => $user
        ]);
    }
}
```

### Routes

Routes map URLs to controller actions. They are defined in `app/Routes/routes.php`.

Example route:

```php
'/profile' => ['controller' => 'UserController', 'action' => 'profile']
```

### Views

Views display data to the user. They are stored in the `app/Views` directory.

Example view (`app/Views/user/profile.php`):

```php
<?php require_once 'includes/header.php'; ?>

<div class="container">
    <h1>User Profile</h1>
    <p>Welcome, <?php echo $user['first_name']; ?></p>
</div>

<?php require_once 'includes/footer.php'; ?>
```

## Backward Compatibility

The MVC structure is designed to be backward compatible with the existing code. This is achieved through:

1. **Router Fallback**: If a route is not found in the MVC structure, the router will try to find a corresponding PHP file in the old structure.

2. **Helper Methods**: The `App\Helpers\Compatibility` class provides methods for working with legacy code, such as including legacy files and loading legacy views.

3. **Progressive Migration**: Controllers can use both new MVC features and legacy code as needed during the transition period.

## Adding New Features

When adding new features to SoVest, it's recommended to use the MVC structure:

1. Create a controller in `app/Controllers/`
2. Define a route in `app/Routes/routes.php`
3. Create a view in `app/Views/`
4. Use the models in `database/models/`

## Examples

See the existing controllers and views for examples of how to use the MVC structure.
