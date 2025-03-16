# Model Standardization Guide for SoVest

## Introduction

This document defines the standards and patterns for the model layer in the SoVest application. As part of Phase 3 (Application Restructuring), we're standardizing how models are defined, validated, and used throughout the application.

The model layer serves as the foundation for interacting with the database, providing a consistent interface for creating, retrieving, updating, and deleting records. By following these standards, we ensure that all models function consistently, are properly validated, and maintain clean separation of concerns.

### Key Benefits of Standardized Models

1. **Consistency**: Uniform approach to defining models across the application
2. **Data Integrity**: Consistent validation rules ensure data correctness
3. **Maintainability**: Standard patterns are easier to maintain and extend
4. **Testability**: Standardized models are easier to test
5. **Developer Experience**: Predictable structure reduces onboarding time for new developers

## Current Status and Migration Path

### Current Structure

Currently, models are located in the `database/models/` directory with the following structure:

```
database/models/
├── User.php
├── Stock.php
├── Prediction.php
├── PredictionVote.php
├── StockPrice.php
├── SearchHistory.php
├── SavedSearch.php
└── traits/
    └── ValidationTrait.php
```

### Target Structure

The target structure will move models to the `app/Models/` directory:

```
app/Models/
├── User.php
├── Stock.php
├── Prediction.php
├── PredictionVote.php
├── StockPrice.php
├── SearchHistory.php
├── SavedSearch.php
└── Traits/
    └── ValidationTrait.php
```

### Migration Process

1. Create the new `app/Models/` directory structure if it doesn't exist
2. Move existing models to the new location, updating namespace declarations
3. Update imports in dependent files
4. Update the autoloading configuration in `composer.json`
5. Test thoroughly to ensure functionality is preserved

## Model Definition Standards

### Basic Model Structure

All models should follow this standard structure:

```php
<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use App\Models\Traits\ValidationTrait;

class ModelName extends Model 
{
    use ValidationTrait;

    // Table name (required)
    protected $table = 'table_name';

    // Primary key (required)
    protected $primaryKey = 'id';

    // Timestamps configuration (required)
    public $timestamps = true;  // or false if not using timestamps

    // Mass assignment protection (required)
    protected $fillable = [
        'field1',
        'field2',
        'field3',
    ];

    // Hidden attributes for serialization (optional)
    protected $hidden = [
        'password',
        'remember_token',
    ];

    // Attribute casting (optional)
    protected $casts = [
        'is_active' => 'boolean',
        'created_at' => 'datetime',
    ];

    // Validation rules (required if using ValidationTrait)
    protected $rules = [
        'field1' => ['required'],
        'field2' => ['numeric', 'min:0'],
        'field3' => ['string', 'max:255'],
    ];

    // Custom error messages (optional)
    protected $messages = [
        'field1.required' => 'Field1 is required',
        'field2.numeric' => 'Field2 must be a number',
    ];

    // Relationships (as needed)
    public function relatedModels()
    {
        return $this->hasMany(RelatedModel::class, 'foreign_key');
    }

    // Accessors (as needed)
    public function getFullNameAttribute()
    {
        return $this->first_name . ' ' . $this->last_name;
    }

    // Mutators (as needed)
    public function setPasswordAttribute($value)
    {
        $this->attributes['password'] = password_hash($value, PASSWORD_DEFAULT);
    }

    // Custom validation methods (as needed)
    public function validateCustomRule($attribute, $value, $parameters = [])
    {
        // Custom validation logic
        return true;
    }
}
```

### Required Properties

All models must define these properties:

1. **$table**: The database table name
2. **$primaryKey**: The primary key column name
3. **$timestamps**: Whether to use automatic timestamps
4. **$fillable**: The list of mass-assignable attributes
5. **$rules**: Validation rules (if using ValidationTrait)

### Optional Properties

These properties should be defined as needed:

1. **$hidden**: Attributes to hide from serialization
2. **$casts**: Type casting for attributes
3. **$messages**: Custom validation error messages
4. **$with**: Relationships to always eager load

### Naming Conventions

1. **Model Classes**:
   - Singular, PascalCase (e.g., `User`, `StockPrice`)
   - Match the entity they represent

2. **Properties and Methods**:
   - Properties: camelCase (e.g., `$primaryKey`, `$fillable`)
   - Methods: camelCase (e.g., `getFullNameAttribute()`, `hasMany()`)

3. **Relationship Methods**:
   - One-to-many: Plural noun (e.g., `predictions()`)
   - One-to-one/Many-to-one: Singular noun (e.g., `user()`)
   - Many-to-many: Plural noun (e.g., `roles()`)

## Validation System

### ValidationTrait

All models should use the `ValidationTrait` to implement consistent validation:

```php
use App\Models\Traits\ValidationTrait;

class User extends Model
{
    use ValidationTrait;

    protected $rules = [
        'email' => ['required', 'email', 'unique'],
        'password' => ['required', 'min:6'],
        'first_name' => ['max:50'],
        'last_name' => ['max:50']
    ];
}
```

### Standard Validation Rules

The ValidationTrait provides these standard rules:

1. **required**: Field must not be empty
2. **email**: Field must be a valid email
3. **numeric**: Field must be a number
4. **min:X**: String length or numeric value must be at least X
5. **max:X**: String length or numeric value must not exceed X
6. **date**: Field must be a valid date
7. **in:val1,val2**: Field must be one of the specified values
8. **regex:pattern**: Field must match the regex pattern
9. **unique**: Field must be unique in the database table

### Custom Validation Rules

Add custom validation rules by creating methods in the format `validateRuleName`:

```php
/**
 * Validate that a date is in the future
 * 
 * @param string $attribute
 * @param mixed $value
 * @param array $parameters
 * @return boolean
 */
public function validateFutureDate($attribute, $value, $parameters = [])
{
    if (empty($value)) {
        return true;
    }

    // Parse the date
    $date = date_parse($value);
    if ($date['error_count'] > 0 || !checkdate($date['month'], $date['day'], $date['year'])) {
        return true; // Date format already validated by date rule
    }

    // Convert to a DateTime object
    $dateObj = new \DateTime($value);
    $now = new \DateTime();

    // Check if the date is in the future
    if ($dateObj <= $now) {
        $this->addError($attribute, "The $attribute must be a date in the future.");
        return false;
    }
    
    return true;
}
```

### Validation Implementation

Validate models using the following methods:

```php
// Create a new model instance
$user = new User([
    'email' => 'user@example.com',
    'password' => 'password123',
    'first_name' => 'John',
    'last_name' => 'Doe'
]);

// Validate the model
if ($user->validate()) {
    // Validation passed, save the record
    $user->save();
} else {
    // Get validation errors
    $errors = $user->getErrors();
}

// Alternative: Validate and save in one step
if ($user->validateAndSave()) {
    // Validation passed and record saved
} else {
    // Get validation errors
    $errors = $user->getErrors();
}
```

## Defining and Using Relationships

### Available Relationship Types

1. **One-to-Many (hasMany)**:
   ```php
   public function predictions()
   {
       return $this->hasMany(Prediction::class, 'user_id');
   }
   ```

2. **Many-to-One (belongsTo)**:
   ```php
   public function user()
   {
       return $this->belongsTo(User::class, 'user_id');
   }
   ```

3. **One-to-One (hasOne)**:
   ```php
   public function profile()
   {
       return $this->hasOne(UserProfile::class, 'user_id');
   }
   ```

4. **Many-to-Many (belongsToMany)**:
   ```php
   public function tags()
   {
       return $this->belongsToMany(
           Tag::class,
           'stock_tags',  // pivot table
           'stock_id',    // foreign key of this model
           'tag_id'       // foreign key of related model
       );
   }
   ```

### Using Relationships

```php
// Get related records
$user = User::find(1);
$predictions = $user->predictions;

// Eager loading to avoid N+1 query problem
$users = User::with('predictions')->get();

// Filtering based on relationship
$usersWithPredictions = User::has('predictions')->get();

// Filtering with relationship conditions
$usersWithBullishPredictions = User::whereHas('predictions', function ($query) {
    $query->where('prediction_type', 'bullish');
})->get();
```

## Model Examples

### User Model Example

```php
<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use App\Models\Traits\ValidationTrait;

class User extends Model 
{
    use ValidationTrait;

    // Table name
    protected $table = 'users';

    // Primary key
    protected $primaryKey = 'id';

    // Timestamps are enabled in this table
    public $timestamps = true;

    // Allow mass assignment for these columns
    protected $fillable = [
        'email',
        'password',
        'first_name',
        'last_name',
        'major',
        'year',
        'scholarship',
        'reputation_score'
    ];

    // Hide these fields from serialization
    protected $hidden = [
        'password'
    ];

    /**
     * Validation rules for User model
     */
    protected $rules = [
        'email' => ['required', 'email', 'unique'],
        'password' => ['required', 'min:6'],
        'first_name' => ['max:50'],
        'last_name' => ['max:50']
    ];

    /**
     * Custom error messages for validation
     */
    protected $messages = [
        'email.required' => 'Email address is required',
        'email.email' => 'Please provide a valid email address',
        'email.unique' => 'This email address is already registered',
        'password.required' => 'Password is required',
        'password.min' => 'Password must be at least 6 characters long',
        'first_name.max' => 'First name cannot exceed 50 characters',
        'last_name.max' => 'Last name cannot exceed 50 characters'
    ];

    /**
     * Validate uniqueness of email in database
     * 
     * @param string $attribute
     * @param mixed $value
     * @param array $parameters
     * @return boolean
     */
    public function validateUnique($attribute, $value, $parameters = [])
    {
        if (empty($value)) {
            return true;
        }

        // Build query to check for existing records
        $query = self::where($attribute, $value);
        
        // If updating an existing record, exclude the current record
        if ($this->exists) {
            $query->where($this->primaryKey, '!=', $this->{$this->primaryKey});
        }
        
        // If a record with this value exists, validation fails
        if ($query->exists()) {
            $this->addError($attribute, $this->getMessage($attribute, 'unique', "The $attribute has already been taken."));
            return false;
        }
        
        return true;
    }

    // Set password attribute (mutator)
    public function setPasswordAttribute($value)
    {
        // Only hash the password if it's not already hashed
        if (!empty($value) && strlen($value) < 60) {
            $this->attributes['password'] = password_hash($value, PASSWORD_DEFAULT);
        } else {
            $this->attributes['password'] = $value;
        }
    }

    // Relationships
    public function predictions()
    {
        return $this->hasMany(Prediction::class, 'user_id');
    }

    public function predictionVotes()
    {
        return $this->hasMany(PredictionVote::class, 'user_id');
    }

    public function searchHistory()
    {
        return $this->hasMany(SearchHistory::class, 'user_id');
    }

    public function savedSearches()
    {
        return $this->hasMany(SavedSearch::class, 'user_id');
    }
    
    // Full name accessor
    public function getFullNameAttribute()
    {
        return $this->first_name . ' ' . $this->last_name;
    }
}
```

### Prediction Model Example

```php
<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use App\Models\Traits\ValidationTrait;

class Prediction extends Model 
{
    use ValidationTrait;

    // Table name
    protected $table = 'predictions';

    // Primary key
    protected $primaryKey = 'prediction_id';

    // Timestamps (using prediction_date instead of created_at)
    public $timestamps = false;

    // Allow mass assignment for these columns
    protected $fillable = [
        'user_id',
        'stock_id',
        'prediction_type',
        'target_price',
        'prediction_date',
        'end_date',
        'is_active',
        'accuracy',
        'reasoning'
    ];

    // Type casting
    protected $casts = [
        'is_active' => 'boolean',
        'target_price' => 'float',
        'accuracy' => 'float',
    ];

    /**
     * Validation rules for Prediction model
     */
    protected $rules = [
        'user_id' => ['required', 'exists'],
        'stock_id' => ['required', 'exists'],
        'prediction_type' => ['required', 'in:Bullish,Bearish'],
        'target_price' => ['numeric', 'nullable'],
        'end_date' => ['required', 'date', 'futureDate'],
        'reasoning' => ['required']
    ];

    /**
     * Custom error messages for validation
     */
    protected $messages = [
        'user_id.required' => 'User ID is required',
        'user_id.exists' => 'The selected user does not exist',
        'stock_id.required' => 'Stock ID is required',
        'stock_id.exists' => 'The selected stock does not exist',
        'prediction_type.required' => 'Prediction type is required',
        'prediction_type.in' => 'Prediction type must be either Bullish or Bearish',
        'target_price.numeric' => 'Target price must be a numeric value',
        'end_date.required' => 'End date is required',
        'end_date.date' => 'End date must be a valid date',
        'end_date.futureDate' => 'End date must be a future date',
        'reasoning.required' => 'Reasoning for your prediction is required'
    ];

    /**
     * Validate if a record exists in the database
     * 
     * @param string $attribute
     * @param mixed $value
     * @param array $parameters
     * @return boolean
     */
    public function validateExists($attribute, $value, $parameters = [])
    {
        if (empty($value)) {
            return true;
        }

        // Determine the table and column to check
        $table = null;
        if ($attribute === 'user_id') {
            $model = new User();
            $table = $model->getTable();
            $column = $model->getKeyName();
        } elseif ($attribute === 'stock_id') {
            $model = new Stock();
            $table = $model->getTable();
            $column = $model->getKeyName();
        } else {
            $this->addError($attribute, "Cannot validate existence for $attribute");
            return false;
        }

        // Check if the record exists
        $exists = $model->where($column, $value)->exists();
        
        if (!$exists) {
            $this->addError($attribute, $this->getMessage($attribute, 'exists', "The selected $attribute does not exist."));
            return false;
        }
        
        return true;
    }

    /**
     * Validate that a date is in the future
     * 
     * @param string $attribute
     * @param mixed $value
     * @param array $parameters
     * @return boolean
     */
    public function validateFutureDate($attribute, $value, $parameters = [])
    {
        if (empty($value)) {
            return true;
        }

        // Parse the date
        $date = date_parse($value);
        if ($date['error_count'] > 0 || !checkdate($date['month'], $date['day'], $date['year'])) {
            // This is already checked by the date validator
            return true;
        }

        // Convert to a DateTime object
        $dateObj = new \DateTime($value);
        $now = new \DateTime();

        // Check if the date is in the future
        if ($dateObj <= $now) {
            $this->addError($attribute, $this->getMessage($attribute, 'futureDate', "The $attribute must be a date in the future."));
            return false;
        }
        
        return true;
    }

    // Set prediction_date attribute on creation
    protected static function boot()
    {
        parent::boot();
        
        static::creating(function ($model) {
            if (empty($model->prediction_date)) {
                $model->prediction_date = date('Y-m-d H:i:s');
            }
        });
    }

    // Relationships
    public function user()
    {
        return $this->belongsTo(User::class, 'user_id');
    }

    public function stock()
    {
        return $this->belongsTo(Stock::class, 'stock_id');
    }

    public function votes()
    {
        return $this->hasMany(PredictionVote::class, 'prediction_id');
    }
}
```

## Testing Models

### Testing Approach

Models should be tested for:

1. Creation and basic attributes
2. Relationships
3. Validation
4. Custom methods
5. Accessors and mutators

### Example Test Case

```php
<?php

namespace Tests\Unit\Models;

use Database\Models\User;
use Database\Models\Prediction;
use PHPUnit\Framework\TestCase;

class UserTest extends TestCase
{
    /**
     * Test user can be created with valid attributes
     */
    public function testUserCanBeCreated()
    {
        $user = new User([
            'email' => 'test@example.com',
            'password' => 'password123',
            'first_name' => 'Test',
            'last_name' => 'User'
        ]);
        
        $this->assertTrue($user->validate());
        $this->assertEquals('test@example.com', $user->email);
        $this->assertEquals('Test', $user->first_name);
    }
    
    /**
     * Test user validation fails with invalid attributes
     */
    public function testUserValidationFailsWithInvalidAttributes()
    {
        $user = new User([
            'email' => 'not-an-email',
            'password' => 'pw', // too short
        ]);
        
        $this->assertFalse($user->validate());
        $this->assertTrue($user->hasErrors());
        $this->assertNotEmpty($user->getErrorsFor('email'));
        $this->assertNotEmpty($user->getErrorsFor('password'));
    }
    
    /**
     * Test user full name accessor
     */
    public function testFullNameAccessor()
    {
        $user = new User([
            'first_name' => 'John',
            'last_name' => 'Doe'
        ]);
        
        $this->assertEquals('John Doe', $user->full_name);
    }
    
    /**
     * Test password is hashed when set
     */
    public function testPasswordIsHashed()
    {
        $user = new User();
        $user->password = 'password123';
        
        $this->assertNotEquals('password123', $user->password);
        $this->assertTrue(password_verify('password123', $user->password));
    }
    
    /**
     * Test user to predictions relationship
     */
    public function testUserPredictionsRelationship()
    {
        // This would require database integration testing
        // or mocking the relationship behavior
    }
}
```

### Testing Best Practices

1. **Unit Test Each Model**: Create separate test cases for each model
2. **Use Database Transactions**: When testing with a database, wrap tests in transactions
3. **Test Validation Rules**: Verify all validation rules work correctly
4. **Test Relationships**: Ensure relationships are correctly defined
5. **Test Custom Methods**: Cover any custom methods with tests
6. **Use Data Providers**: For testing multiple validation scenarios

## Conclusion

Following these model standardization guidelines will ensure consistency across the SoVest application. As we complete the migration from `database/models/` to `app/Models/`, all models should adhere to these patterns, making the codebase more maintainable, testable, and robust.

The standard model approach with the ValidationTrait provides a clean, consistent way to handle data validation throughout the application, while leveraging Eloquent ORM's powerful features for database operations.

By implementing these standards, we move closer to completing Phase 3 (Application Restructuring) and prepare the application for future enhancements.