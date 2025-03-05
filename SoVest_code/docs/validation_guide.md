# SoVest Validation Guide

## Table of Contents

1. [Introduction](#introduction)
2. [Validation System Overview](#validation-system-overview)
3. [Using Validation in Models](#using-validation-in-models)
4. [Using Validation in Controllers](#using-validation-in-controllers)
5. [Available Validation Rules](#available-validation-rules)
6. [Custom Validation Methods](#custom-validation-methods)
7. [Error Handling Best Practices](#error-handling-best-practices)
8. [Migration Strategies for Existing Controllers](#migration-strategies-for-existing-controllers)
9. [Extending the Validation System](#extending-the-validation-system)

## Introduction

This guide documents the validation system implemented in SoVest as part of the SQL to Eloquent ORM migration. The validation system provides a standardized approach to data validation across all models, ensuring data integrity and consistent error handling.

## Validation System Overview

The SoVest validation system is implemented through a `ValidationTrait` that can be used by any Eloquent model. This approach provides:

- **Centralized validation logic** across all models
- **Consistent error handling** with standardized error collection and reporting
- **Customizable validation rules** per model
- **Custom validation methods** that can be added to specific models as needed
- **Simple API** for validating data in controllers and service classes

The validation system is designed to work with SoVest's existing Eloquent ORM implementation without requiring the full Laravel framework.

## Using Validation in Models

### Basic Implementation

To add validation to an Eloquent model:

1. Import the ValidationTrait
2. Add the `use ValidationTrait;` statement to your model class
3. Define validation rules and custom error messages

```php
<?php

namespace Database\Models;

use Illuminate\Database\Eloquent\Model;
use Database\Models\Traits\ValidationTrait;

class MyModel extends Model {
    use ValidationTrait;

    // Define rules
    protected $rules = [
        'field1' => ['required', 'max:255'],
        'field2' => ['numeric', 'min:1']
    ];

    // Define custom messages (optional)
    protected $messages = [
        'field1.required' => 'Field 1 is required',
        'field2.numeric' => 'Field 2 must be a number'
    ];
}
```

### Example: User Model

```php
<?php

namespace Database\Models;

use Illuminate\Database\Eloquent\Model;
use Database\Models\Traits\ValidationTrait;

class User extends Model {
    use ValidationTrait;

    protected $table = 'users';
    protected $primaryKey = 'id';
    public $timestamps = true;

    protected $fillable = [
        'email',
        'password',
        'first_name',
        'last_name'
    ];

    protected $rules = [
        'email' => ['required', 'email', 'unique'],
        'password' => ['required', 'min:6'],
        'first_name' => ['max:50'],
        'last_name' => ['max:50']
    ];

    protected $messages = [
        'email.required' => 'Email address is required',
        'email.email' => 'Please provide a valid email address',
        'email.unique' => 'This email address is already registered',
        'password.required' => 'Password is required',
        'password.min' => 'Password must be at least 6 characters long'
    ];

    // Custom validation method for email uniqueness
    public function validateUnique($attribute, $value, $parameters = [])
    {
        if (empty($value)) {
            return true;
        }

        $query = self::where($attribute, $value);
        
        if ($this->exists) {
            $query->where($this->primaryKey, '!=', $this->{$this->primaryKey});
        }
        
        if ($query->exists()) {
            $this->addError($attribute, $this->getMessage($attribute, 'unique', "The $attribute has already been taken."));
            return false;
        }
        
        return true;
    }
}
```

### Example: Stock Model

```php
<?php

namespace Database\Models;

use Illuminate\Database\Eloquent\Model;
use Database\Models\Traits\ValidationTrait;

class Stock extends Model {
    use ValidationTrait;

    protected $table = 'stocks';
    protected $primaryKey = 'stock_id';
    public $timestamps = false;

    protected $fillable = ['symbol', 'company_name', 'sector'];

    protected $rules = [
        'symbol' => ['required', 'max:10', 'unique'],
        'company_name' => ['required'],
        'sector' => []
    ];

    protected $messages = [
        'symbol.required' => 'Stock symbol is required',
        'symbol.max' => 'Stock symbol cannot exceed 10 characters',
        'symbol.unique' => 'This stock symbol is already registered in the system',
        'company_name.required' => 'Company name is required'
    ];
}
```

### Example: Prediction Model

```php
<?php

namespace Database\Models;

use Illuminate\Database\Eloquent\Model;
use Database\Models\Traits\ValidationTrait;

class Prediction extends Model {
    use ValidationTrait;

    protected $table = 'predictions';
    protected $primaryKey = 'prediction_id';
    public $timestamps = false;

    protected $fillable = [
        'user_id',
        'stock_id',
        'prediction_type',
        'target_price',
        'prediction_date',
        'end_date',
        'is_active',
        'reasoning'
    ];

    protected $rules = [
        'user_id' => ['required', 'exists'],
        'stock_id' => ['required', 'exists'],
        'prediction_type' => ['required', 'in:Bullish,Bearish'],
        'target_price' => ['numeric', 'nullable'],
        'end_date' => ['required', 'date', 'futureDate'],
        'reasoning' => ['required']
    ];

    protected $messages = [
        'prediction_type.in' => 'Prediction type must be either Bullish or Bearish',
        'target_price.numeric' => 'Target price must be a numeric value',
        'end_date.required' => 'End date is required',
        'end_date.futureDate' => 'End date must be a future date',
        'reasoning.required' => 'Reasoning for your prediction is required'
    ];

    // Custom validation method for future dates
    public function validateFutureDate($attribute, $value, $parameters = [])
    {
        if (empty($value)) {
            return true;
        }

        $dateObj = new \DateTime($value);
        $now = new \DateTime();

        if ($dateObj <= $now) {
            $this->addError($attribute, $this->getMessage($attribute, 'futureDate', "The $attribute must be a date in the future."));
            return false;
        }
        
        return true;
    }
}
```

## Using Validation in Controllers

The validation system can be used in controllers in several ways:

### 1. Basic Validation

```php
function processForm($request)
{
    // Create a model instance
    $model = new MyModel();
    
    // Set attributes from request
    $model->field1 = $request->getParam('field1');
    $model->field2 = $request->getParam('field2');
    
    // Validate
    if ($model->validate()) {
        // Valid data - proceed to save
        $model->save();
        return redirect('/success');
    } else {
        // Invalid data - return to form with errors
        return redirect('/form')
            ->withInput()
            ->with('errors', $model->getErrors());
    }
}
```

### 2. Mass Assignment with Validation

```php
function processApiRequest($request)
{
    // Get all data from request
    $data = $request->getParams();
    
    // Create model and fill with data
    $model = new MyModel();
    $model->fill($data);
    
    // Validate
    if ($model->validate()) {
        // Save if valid
        $model->save();
        return ['success' => true, 'id' => $model->id];
    } else {
        // Return errors if invalid
        return ['success' => false, 'errors' => $model->getErrors()];
    }
}
```

### 3. One-step Validation and Saving

```php
function updateRecord($request, $id)
{
    // Find existing record
    $model = MyModel::find($id);
    
    // Update with new data
    $model->fill($request->getParams());
    
    // Validate and save in one step
    if ($model->validateAndSave()) {
        return redirect('/success');
    } else {
        return redirect('/edit/' . $id)
            ->withInput()
            ->with('errors', $model->getErrors());
    }
}
```

### 4. Single Attribute Validation

```php
function validateField($request)
{
    $field = $request->getParam('field');
    $value = $request->getParam('value');
    
    $model = new MyModel();
    
    if ($model->validateAttribute($field, $value)) {
        return ['valid' => true];
    } else {
        return [
            'valid' => false, 
            'errors' => $model->getErrorsFor($field)
        ];
    }
}
```

## Available Validation Rules

The validation system provides the following built-in validation rules:

| Rule | Description | Parameters | Example |
|------|-------------|------------|---------|
| `required` | Field must not be empty | None | `'name' => ['required']` |
| `email` | Field must be a valid email address | None | `'email' => ['email']` |
| `numeric` | Field must be a number | None | `'price' => ['numeric']` |
| `min` | Field must have a minimum length | Minimum length | `'password' => ['min:6']` |
| `max` | Field must not exceed maximum length | Maximum length | `'username' => ['max:50']` |
| `date` | Field must be a valid date | None | `'birth_date' => ['date']` |
| `in` | Field must be one of specified values | List of values | `'status' => ['in:active,inactive']` |
| `regex` | Field must match a regular expression | Pattern | `'code' => ['regex:/^[A-Z]{3}$/']` |
| `unique` | Field must be unique in the database | None | `'email' => ['unique']` |

### Using Multiple Rules

You can apply multiple rules to a single field:

```php
protected $rules = [
    'email' => ['required', 'email', 'unique'],
    'password' => ['required', 'min:8', 'max:255']
];
```

## Custom Validation Methods

### Creating Custom Validation Rules

To create a custom validation rule:

1. Add a method to your model named `validate` followed by the capitalized rule name
2. The method should accept `$attribute`, `$value`, and optional `$parameters`
3. Return `true` if validation passes, `false` if it fails
4. Call `$this->addError()` when validation fails

```php
/**
 * Validate that a value is a valid stock symbol format
 */
public function validateSymbolFormat($attribute, $value, $parameters = [])
{
    if (empty($value)) {
        return true;
    }
    
    if (!preg_match('/^[A-Z]{1,5}$/', $value)) {
        $this->addError($attribute, "The $attribute must be 1-5 uppercase letters.");
        return false;
    }
    
    return true;
}
```

### Using Custom Rules

Once defined, you can use custom rules like any built-in rule:

```php
protected $rules = [
    'symbol' => ['required', 'symbolFormat', 'unique']
];
```

### Example: Foreign Key Existence Validation

```php
/**
 * Validate that a record exists in a related table
 */
public function validateExists($attribute, $value, $parameters = [])
{
    if (empty($value)) {
        return true;
    }

    // Default to the model class name
    $modelClass = isset($parameters[0]) ? $parameters[0] : null;
    
    if (!$modelClass) {
        // Infer model class from attribute name (e.g., user_id → User)
        $parts = explode('_', $attribute);
        $modelName = ucfirst($parts[0]);
        $modelClass = "Database\\Models\\$modelName";
    }
    
    if (!class_exists($modelClass)) {
        $this->addError($attribute, "Cannot validate existence for $attribute");
        return false;
    }
    
    $model = new $modelClass();
    $exists = $model->where($model->getKeyName(), $value)->exists();
    
    if (!$exists) {
        $this->addError($attribute, $this->getMessage($attribute, 'exists', "The selected $attribute does not exist."));
        return false;
    }
    
    return true;
}
```

## Error Handling Best Practices

### Collecting Errors

The validation system automatically collects errors during validation:

```php
// Validate the model
$model->validate();

// Get all errors
$allErrors = $model->getErrors();

// Get errors for a specific field
$emailErrors = $model->getErrorsFor('email');

// Check if there are any errors
$hasErrors = $model->hasErrors();
```

### Error Structure

Errors are structured as an associative array with attributes as keys and arrays of error messages as values:

```php
[
    'email' => [
        'Email address is required',
        'Please provide a valid email address'
    ],
    'password' => [
        'Password must be at least 6 characters long'
    ]
]
```

### Displaying Errors in Web Forms

```php
<!-- Basic error display -->
<?php if (isset($errors['email'])): ?>
    <div class="error">
        <?php echo $errors['email'][0]; ?>
    </div>
<?php endif; ?>

<!-- Generic error list -->
<?php if (!empty($errors)): ?>
    <div class="error-box">
        <ul>
            <?php foreach ($errors as $field => $fieldErrors): ?>
                <?php foreach ($fieldErrors as $error): ?>
                    <li><?php echo $error; ?></li>
                <?php endforeach; ?>
            <?php endforeach; ?>
        </ul>
    </div>
<?php endif; ?>
```

### Handling Errors in API Responses

```php
function apiCreateRecord($request)
{
    $model = new MyModel();
    $model->fill($request->getParams());
    
    if ($model->validate()) {
        $model->save();
        return json_encode([
            'success' => true,
            'message' => 'Record created successfully',
            'id' => $model->id
        ]);
    } else {
        // Return structured error response
        return json_encode([
            'success' => false,
            'message' => 'Validation failed',
            'errors' => $model->getErrors()
        ]);
    }
}
```

### Error Handling in JavaScript

```javascript
// Example of handling validation errors from API in JavaScript
fetch('/api/create', {
    method: 'POST',
    body: formData
})
.then(response => response.json())
.then(data => {
    if (data.success) {
        showSuccessMessage(data.message);
    } else {
        // Display validation errors
        for (const [field, fieldErrors] of Object.entries(data.errors)) {
            const errorElement = document.getElementById(`${field}-error`);
            if (errorElement) {
                errorElement.textContent = fieldErrors[0];
                errorElement.classList.remove('hidden');
            }
        }
    }
});
```

## Migration Strategies for Existing Controllers

### Gradual Migration Approach

1. **Identify controllers with manual validation**: Look for controllers with direct validation using functions like `empty()`, `filter_var()`, etc.

2. **Create model instances**: Replace direct validation with model-based validation:

   **Before:**
   ```php
   if (empty($_POST['email']) || !filter_var($_POST['email'], FILTER_VALIDATE_EMAIL)) {
       $errors[] = "Invalid email address";
   }
   ```

   **After:**
   ```php
   $user = new User();
   $user->email = $_POST['email'];
   if (!$user->validateAttribute('email', $user->email)) {
       $errors = $user->getErrorsFor('email');
   }
   ```

3. **Full controller conversion**: Replace the entire validation section with model validation:

   **Before:**
   ```php
   $errors = [];
   if (empty($_POST['email'])) {
       $errors[] = "Email is required";
   } elseif (!filter_var($_POST['email'], FILTER_VALIDATE_EMAIL)) {
       $errors[] = "Invalid email format";
   }
   
   if (empty($_POST['password'])) {
       $errors[] = "Password is required";
   } elseif (strlen($_POST['password']) < 6) {
       $errors[] = "Password must be at least 6 characters";
   }
   
   if (empty($errors)) {
       // Proceed with saving
   }
   ```

   **After:**
   ```php
   $user = new User();
   $user->email = $_POST['email'];
   $user->password = $_POST['password'];
   
   if ($user->validate()) {
       // Proceed with saving
   } else {
       $errors = $user->getErrors();
   }
   ```

### Migration Examples

#### Example: User Registration

**Before:**
```php
function registerUser() {
    $email = $_POST['email'] ?? '';
    $password = $_POST['password'] ?? '';
    $firstName = $_POST['first_name'] ?? '';
    $lastName = $_POST['last_name'] ?? '';
    
    $errors = [];
    
    if (empty($email)) {
        $errors[] = "Email is required";
    } elseif (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
        $errors[] = "Invalid email format";
    }
    
    if (empty($password)) {
        $errors[] = "Password is required";
    } elseif (strlen($password) < 6) {
        $errors[] = "Password must be at least 6 characters";
    }
    
    // Check if email exists in database
    $existingUser = db_query("SELECT id FROM users WHERE email = ?", [$email]);
    if (!empty($existingUser)) {
        $errors[] = "Email already registered";
    }
    
    if (empty($errors)) {
        // Insert user
        $result = db_query(
            "INSERT INTO users (email, password, first_name, last_name) VALUES (?, ?, ?, ?)", 
            [$email, password_hash($password, PASSWORD_DEFAULT), $firstName, $lastName]
        );
        
        if ($result) {
            redirect('/login?success=1');
        } else {
            $errors[] = "Registration failed";
        }
    }
    
    return renderView('register', ['errors' => $errors]);
}
```

**After:**
```php
function registerUser() {
    $userData = [
        'email' => $_POST['email'] ?? '',
        'password' => $_POST['password'] ?? '',
        'first_name' => $_POST['first_name'] ?? '',
        'last_name' => $_POST['last_name'] ?? ''
    ];
    
    $user = new User();
    $user->fill($userData);
    
    if ($user->validate()) {
        // Hash password before saving
        $user->password = password_hash($user->password, PASSWORD_DEFAULT);
        
        if ($user->save()) {
            redirect('/login?success=1');
        } else {
            return renderView('register', [
                'errors' => ['system' => ['Registration failed']]
            ]);
        }
    } else {
        return renderView('register', [
            'errors' => $user->getErrors(),
            'oldInput' => $userData
        ]);
    }
}
```

#### Example: Create Prediction

**Before:**
```php
function createPrediction() {
    $userId = getCurrentUserId();
    $stockId = $_POST['stock_id'] ?? '';
    $predictionType = $_POST['prediction_type'] ?? '';
    $targetPrice = $_POST['target_price'] ?? null;
    $endDate = $_POST['end_date'] ?? '';
    $reasoning = $_POST['reasoning'] ?? '';
    
    $errors = [];
    
    if (empty($stockId)) {
        $errors[] = "Stock is required";
    }
    
    if (empty($predictionType)) {
        $errors[] = "Prediction type is required";
    } elseif (!in_array($predictionType, ['Bullish', 'Bearish'])) {
        $errors[] = "Invalid prediction type";
    }
    
    if (!empty($targetPrice) && !is_numeric($targetPrice)) {
        $errors[] = "Target price must be a number";
    }
    
    if (empty($endDate)) {
        $errors[] = "End date is required";
    } else {
        $dateObj = date_create($endDate);
        $now = date_create();
        if (!$dateObj) {
            $errors[] = "Invalid date format";
        } elseif ($dateObj <= $now) {
            $errors[] = "End date must be in the future";
        }
    }
    
    if (empty($reasoning)) {
        $errors[] = "Reasoning is required";
    }
    
    if (empty($errors)) {
        // Insert prediction
        $result = db_query(
            "INSERT INTO predictions (user_id, stock_id, prediction_type, target_price, prediction_date, end_date, is_active, reasoning) VALUES (?, ?, ?, ?, NOW(), ?, 1, ?)",
            [$userId, $stockId, $predictionType, $targetPrice, $endDate, $reasoning]
        );
        
        if ($result) {
            redirect('/my-predictions?success=1');
        } else {
            $errors[] = "Failed to create prediction";
        }
    }
    
    return renderView('create_prediction', ['errors' => $errors]);
}
```

**After:**
```php
function createPrediction() {
    $predictionData = [
        'user_id' => getCurrentUserId(),
        'stock_id' => $_POST['stock_id'] ?? '',
        'prediction_type' => $_POST['prediction_type'] ?? '',
        'target_price' => $_POST['target_price'] ?? null,
        'end_date' => $_POST['end_date'] ?? '',
        'reasoning' => $_POST['reasoning'] ?? '',
        'prediction_date' => date('Y-m-d H:i:s'),
        'is_active' => 1
    ];
    
    $prediction = new Prediction();
    $prediction->fill($predictionData);
    
    if ($prediction->validate()) {
        if ($prediction->save()) {
            redirect('/my-predictions?success=1');
        } else {
            return renderView('create_prediction', [
                'errors' => ['system' => ['Failed to create prediction']]
            ]);
        }
    } else {
        return renderView('create_prediction', [
            'errors' => $prediction->getErrors(),
            'oldInput' => $predictionData
        ]);
    }
}
```

## Extending the Validation System

### Adding New Validation Rules to ValidationTrait

To add a new validation rule that will be available to all models:

1. Add a new method to the `ValidationTrait.php` file

```php
/**
 * Validate that a value is within a given range
 */
public function validateRange($attribute, $value, $parameters = [])
{
    if (empty($value) && $value !== 0) {
        return true;
    }
    
    $min = isset($parameters[0]) ? (float)$parameters[0] : null;
    $max = isset($parameters[1]) ? (float)$parameters[1] : null;
    
    if ($min !== null && $value < $min) {
        $this->addError($attribute, $this->getMessage(
            $attribute, 
            'range', 
            "The $attribute must be at least $min."
        ));
        return false;
    }
    
    if ($max !== null && $value > $max) {
        $this->addError($attribute, $this->getMessage(
            $attribute, 
            'range', 
            "The $attribute must not exceed $max."
        ));
        return false;
    }
    
    return true;
}
```

2. Use the new rule in your models:

```php
protected $rules = [
    'price' => ['numeric', 'range:0,1000']
];
```

### Creating Model-Specific Validation Rules

For validation rules that only apply to a specific model:

```php
class Stock extends Model {
    use ValidationTrait;
    
    // ... other properties and methods
    
    /**
     * Validate that the stock symbol follows exchange-specific rules
     */
    public function validateSymbolFormat($attribute, $value, $parameters = [])
    {
        if (empty($value)) {
            return true;
        }
        
        $exchange = $this->exchange ?? 'default';
        
        if ($exchange === 'NYSE' && !preg_match('/^[A-Z]{1,3}$/', $value)) {
            $this->addError($attribute, "NYSE symbols must be 1-3 uppercase letters.");
            return false;
        }
        
        if ($exchange === 'NASDAQ' && !preg_match('/^[A-Z]{4}$/', $value)) {
            $this->addError($attribute, "NASDAQ symbols must be 4 uppercase letters.");
            return false;
        }
        
        return true;
    }
    
    protected $rules = [
        'symbol' => ['required', 'symbolFormat', 'unique']
    ];
}
```

### Adding Conditional Validation Logic

For more complex validation scenarios, you can override the `validate` method:

```php
class Prediction extends Model {
    use ValidationTrait;
    
    // ... other properties and methods
    
    /**
     * Override the validate method to add conditional logic
     */
    public function validate()
    {
        // Clear previous errors
        $this->clearErrors();
        
        // Add conditional rules based on prediction type
        if ($this->prediction_type === 'Bullish') {
            $this->rules['target_price'] = ['required', 'numeric', 'min:0'];
        }
        
        // Call parent validation
        return parent::validate();
    }
}
```

### Creating Validation Services

For complex validation that spans multiple models or requires business logic:

```php
<?php

namespace Services;

class ValidationService {
    /**
     * Validate a complete prediction submission including related data
     */
    public function validatePredictionSubmission($data)
    {
        $errors = [];
        
        // Validate user permissions
        if (!$this->canUserCreatePrediction($data['user_id'])) {
            $errors['user_id'] = ['User does not have permission to create predictions'];
        }
        
        // Validate stock exists and is active
        $stock = Stock::find($data['stock_id']);
        if (!$stock) {
            $errors['stock_id'] = ['Stock not found'];
        } elseif (!$stock->is_active) {
            $errors['stock_id'] = ['Stock is not active for predictions'];
        }
        
        // Validate market is open on end date
        if (!empty($data['end_date']) && !$this->isMarketOpenDate($data['end_date'])) {
            $errors['end_date'] = ['End date must be a market business day'];
        }
        
        // Create and validate prediction model
        $prediction = new Prediction();
        $prediction->fill($data);
        
        if (!$prediction->validate()) {
            // Merge model validation errors
            $errors = array_merge($errors, $prediction->getErrors());
        }
        
        return [
            'valid' => empty($errors),
            'errors' => $errors,
            'prediction' => $prediction
        ];
    }
    
    // Helper methods...
}
```

---

By following this guide, developers can leverage SoVest's validation system to ensure data integrity across the application while maintaining a consistent approach to error handling and user feedback.