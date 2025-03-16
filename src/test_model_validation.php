<?php
/**
 * Model Validation Integration Test
 * 
 * This file demonstrates how to use the validation system in controllers for:
 * - User model
 * - Prediction model
 * - Stock model
 *
 * It shows code examples for implementing validation in controllers,
 * including direct attribute validation, mass assignment validation,
 * and error handling approaches.
 */

echo "====================================================\n";
echo "SoVest Model Validation Integration Examples\n";
echo "====================================================\n\n";

echo "This file contains code examples that demonstrate how to use the\n";
echo "validation system in controllers. Below you'll find examples for all\n"; 
echo "three models (User, Prediction, Stock) that show how to implement\n";
echo "validation in different scenarios.\n\n";

/**
 * SECTION 1: USER VALIDATION EXAMPLES
 */
echo "SECTION 1: USER VALIDATION EXAMPLES\n";
echo "----------------------------------------------------\n";

// Example 1.1: Creating and validating a new User (direct attribute assignment)
echo "Example 1.1: Creating a User with direct attribute validation\n\n";

$userExample1 = <<<'CODE'
/**
 * Controller method for user registration
 */
function processUserRegistration($request) {
    // Get data from request
    $userData = [
        'email' => $request->getParam('email'),
        'password' => $request->getParam('password'),
        'first_name' => $request->getParam('first_name'),
        'last_name' => $request->getParam('last_name')
    ];

    // Create user model
    $user = new User();
    $user->email = $userData['email'];
    $user->password = $userData['password'];
    $user->first_name = $userData['first_name'];
    $user->last_name = $userData['last_name'];

    // Validate before saving
    if ($user->validate()) {
        // Save user
        $user->save();
        return redirect('/login')->with('success', 'Account created successfully');
    } else {
        // Return to form with errors
        return redirect('/register')
            ->withInput($userData)
            ->with('errors', $user->getErrors());
    }
}
CODE;

echo $userExample1 . "\n\n";

// Example 1.2: Mass assignment validation with User::create
echo "Example 1.2: User validation with mass assignment\n\n";

$userExample2 = <<<'CODE'
/**
 * API controller method for user creation
 */
function createUserWithMassAssignment($request) {
    // Get data from request
    $userData = $request->getParams();

    // Validate data first
    $tempUser = new User();
    $tempUser->fill($userData);

    if ($tempUser->validate()) {
        // Create user if valid
        $user = User::create($userData);
        return jsonResponse(['success' => true, 'user_id' => $user->id]);
    } else {
        // Return errors in API response
        return jsonResponse([
            'success' => false,
            'errors' => $tempUser->getErrors()
        ], 422);
    }
}
CODE;

echo $userExample2 . "\n\n";

// Example 1.3: Using validateAndSave() helper
echo "Example 1.3: Using validateAndSave() helper method\n\n";

$userExample3 = <<<'CODE'
/**
 * Controller method for updating user profile
 */
function updateUserProfile($request, $userId) {
    // Find user
    $user = User::find($userId);
    if (!$user) {
        return redirect('/404');
    }

    // Update with new data
    $user->fill($request->getParams());

    // One-step validation and saving
    if ($user->validateAndSave()) {
        return redirect('/profile')->with('success', 'Profile updated');
    } else {
        return redirect()->back()
            ->withInput()
            ->with('errors', $user->getErrors());
    }
}
CODE;

echo $userExample3 . "\n\n";

/**
 * SECTION 2: PREDICTION VALIDATION EXAMPLES
 */
echo "\nSECTION 2: PREDICTION VALIDATION EXAMPLES\n";
echo "----------------------------------------------------\n";

// Example 2.1: Creating and validating a new Prediction
echo "Example 2.1: Creating a Prediction with validation\n\n";

$predictionExample1 = <<<'CODE'
/**
 * Controller method for creating a prediction
 */
function createPrediction($request) {
    // Get current authenticated user
    $userId = getCurrentUserId();

    // Create prediction from form data
    $prediction = new Prediction();
    $prediction->user_id = $userId;
    $prediction->stock_id = $request->getParam('stock_id');
    $prediction->prediction_type = $request->getParam('prediction_type');
    $prediction->target_price = $request->getParam('target_price');
    $prediction->end_date = $request->getParam('end_date');
    $prediction->reasoning = $request->getParam('reasoning');
    $prediction->prediction_date = date('Y-m-d H:i:s');
    $prediction->is_active = 1;

    // Validate before saving
    if ($prediction->validate()) {
        $prediction->save();
        return redirect('/predictions/my')->with('success', 'Prediction created');
    } else {
        // Handle validation errors
        return redirect()->back()
            ->withInput($request->getParams())
            ->with('errors', $prediction->getErrors());
    }
}
CODE;

echo $predictionExample1 . "\n\n";

// Example 2.2: Validate a single attribute independently
echo "Example 2.2: Validating a single attribute independently\n\n";

$predictionExample2 = <<<'CODE'
/**
 * AJAX validation controller for predictions
 */
function validatePredictionField($request) {
    $field = $request->getParam('field');
    $value = $request->getParam('value');

    // Early validation for a single field
    $prediction = new Prediction();
    $prediction->{$field} = $value;

    // Only validate the specific field
    if ($prediction->validateAttribute($field, $value)) {
        return jsonResponse(['valid' => true]);
    } else {
        return jsonResponse([
            'valid' => false,
            'errors' => $prediction->getErrorsFor($field)
        ]);
    }
}
CODE;

echo $predictionExample2 . "\n\n";

// Example 2.3: Validating a prediction with mass assignment
echo "Example 2.3: Mass assignment validation for predictions\n\n";

$predictionExample3 = <<<'CODE'
/**
 * API method for creating predictions
 */
function apiCreatePrediction($request) {
    // Get data and add current user ID
    $data = $request->getParams();
    $data['user_id'] = getCurrentUserId();
    $data['prediction_date'] = date('Y-m-d H:i:s');
    $data['is_active'] = 1;

    // Create and validate prediction
    $prediction = new Prediction();
    $prediction->fill($data);

    if ($prediction->validate()) {
        $prediction->save();
        
        return jsonResponse([
            'success' => true,
            'message' => 'Prediction created successfully',
            'prediction_id' => $prediction->prediction_id
        ]);
    } else {
        return jsonResponse([
            'success' => false,
            'message' => 'Validation failed',
            'errors' => $prediction->getErrors()
        ], 422);
    }
}
CODE;

echo $predictionExample3 . "\n\n";

/**
 * SECTION 3: STOCK VALIDATION EXAMPLES
 */
echo "\nSECTION 3: STOCK VALIDATION EXAMPLES\n";
echo "----------------------------------------------------\n";

// Example 3.1: Creating and validating a new Stock with mass assignment
echo "Example 3.1: Creating a Stock with mass assignment validation\n\n";

$stockExample1 = <<<'CODE'
/**
 * Admin controller for adding stocks
 */
function addStock($request) {
    // Get stock data from form
    $stockData = [
        'symbol' => strtoupper($request->getParam('symbol')),
        'company_name' => $request->getParam('company_name'),
        'sector' => $request->getParam('sector', 'Unknown')
    ];

    // Create and validate stock
    $stock = new Stock();
    $stock->fill($stockData);

    if ($stock->validate()) {
        // Save stock to database
        $stock = Stock::create($stockData);
        return redirect('/admin/stocks')
            ->with('success', "Stock {$stock->symbol} added successfully");
    } else {
        // Return to form with errors
        return redirect()->back()
            ->withInput($stockData)
            ->with('errors', $stock->getErrors());
    }
}
CODE;

echo $stockExample1 . "\n\n";

// Example 3.2: Updating an existing Stock with validateAndSave
echo "Example 3.2: Updating a Stock with validateAndSave()\n\n";

$stockExample2 = <<<'CODE'
/**
 * API method for updating stocks
 */
function updateStockInfo($request, $stockId) {
    // Find stock
    $stock = Stock::find($stockId);
    if (!$stock) {
        return jsonResponse(['error' => 'Stock not found'], 404);
    }

    // Update allowed fields
    $stock->company_name = $request->getParam('company_name', $stock->company_name);
    $stock->sector = $request->getParam('sector', $stock->sector);

    // Using validateAndSave for automatic validation and saving
    if ($stock->validateAndSave()) {
        return jsonResponse([
            'success' => true,
            'message' => 'Stock updated successfully',
            'stock' => $stock->toArray()
        ]);
    } else {
        return jsonResponse([
            'success' => false,
            'errors' => $stock->getErrors()
        ], 422);
    }
}
CODE;

echo $stockExample2 . "\n\n";

/**
 * SECTION 4: HANDLING VALIDATION ERRORS
 */
echo "\nSECTION 4: HANDLING VALIDATION ERRORS\n";
echo "----------------------------------------------------\n";

// Example 4.1: Handling errors in web forms
echo "Example 4.1: Handling validation errors in web forms\n\n";

$errorExample1 = <<<'CODE'
<!-- PHP template for a form with validation errors -->
<form action="/register" method="post">
    <div class="form-group <?php echo isset($errors['email']) ? 'has-error' : ''; ?>">
        <label for="email">Email</label>
        <input type="email" name="email" value="<?php echo $oldInput['email'] ?? ''; ?>">
        <?php if (isset($errors['email'])): ?>
            <span class="error-message"><?php echo $errors['email'][0]; ?></span>
        <?php endif; ?>
    </div>
    
    <div class="form-group <?php echo isset($errors['password']) ? 'has-error' : ''; ?>">
        <label for="password">Password</label>
        <input type="password" name="password">
        <?php if (isset($errors['password'])): ?>
            <span class="error-message"><?php echo $errors['password'][0]; ?></span>
        <?php endif; ?>
    </div>
    
    <button type="submit">Register</button>
</form>
CODE;

echo $errorExample1 . "\n\n";

// Example 4.2: Handling errors in API responses
echo "Example 4.2: Handling validation errors in API responses\n\n";

$errorExample2 = <<<'CODE'
// Example API error response format
{
    "success": false,
    "message": "Validation failed",
    "errors": {
        "email": [
            "Email address is required",
            "Please provide a valid email address"
        ],
        "password": [
            "Password must be at least 6 characters long"
        ]
    }
}

// JavaScript for handling API validation errors
function submitForm() {
    const formData = new FormData(document.getElementById('predictionForm'));
    
    fetch('/api/predictions', {
        method: 'POST',
        body: formData
    })
    .then(response => response.json())
    .then(data => {
        if (data.success) {
            // Show success message
            showSuccessMessage(data.message);
            redirectToPage('/predictions');
        } else {
            // Display validation errors
            clearErrors();
            
            for (const [field, fieldErrors] of Object.entries(data.errors)) {
                const errorElement = document.getElementById(`${field}-error`);
                if (errorElement) {
                    errorElement.textContent = fieldErrors[0];
                    errorElement.classList.remove('hidden');
                }
                
                // Highlight invalid field
                const inputElement = document.getElementById(field);
                if (inputElement) {
                    inputElement.classList.add('is-invalid');
                }
            }
        }
    })
    .catch(error => {
        console.error('Error:', error);
        showErrorMessage('An unexpected error occurred');
    });
}
CODE;

echo $errorExample2 . "\n\n";

/**
 * SECTION 5: ADVANCED VALIDATION TECHNIQUES
 */
echo "\nSECTION 5: ADVANCED VALIDATION TECHNIQUES\n";
echo "----------------------------------------------------\n";

// Example 5.1: Conditional validation
echo "Example 5.1: Conditional validation in controllers\n\n";

$advancedExample1 = <<<'CODE'
/**
 * Controller with conditional validation logic
 */
function createPredictionWithConditionalValidation($request) {
    $prediction = new Prediction();
    $prediction->fill($request->getParams());
    
    // Add conditional validation logic
    if ($prediction->prediction_type === 'Bullish' && empty($prediction->target_price)) {
        // Manually add a validation error
        $prediction->addError('target_price', 'Target price is required for Bullish predictions');
        
        return redirect()->back()
            ->withInput()
            ->with('errors', $prediction->getErrors());
    }
    
    // Continue with standard validation
    if ($prediction->validate()) {
        $prediction->save();
        return redirect('/success');
    } else {
        return redirect()->back()
            ->withInput()
            ->with('errors', $prediction->getErrors());
    }
}
CODE;

echo $advancedExample1 . "\n\n";

// Example 5.2: Custom validation rules
echo "Example 5.2: Adding custom validation rules at runtime\n\n";

$advancedExample2 = <<<'CODE'
/**
 * Controller with custom validation
 */
function validateCustomStockData($request) {
    $stock = new Stock();
    $stock->fill($request->getParams());
    
    // Stock symbol format validation (custom rule)
    $symbol = $stock->symbol;
    if (!empty($symbol) && !preg_match('/^[A-Z]{1,5}$/', $symbol)) {
        $stock->addError('symbol', 'Stock symbol must be 1-5 uppercase letters');
    }
    
    // Additional market-specific validation
    $exchange = $request->getParam('exchange');
    if ($exchange === 'NASDAQ' && strlen($symbol) > 4) {
        $stock->addError('symbol', 'NASDAQ symbols cannot exceed 4 characters');
    }
    
    if (!$stock->hasErrors() && $stock->validate()) {
        $stock->save();
        return redirect('/stocks')->with('success', 'Stock added');
    } else {
        return redirect()->back()
            ->withInput()
            ->with('errors', $stock->getErrors());
    }
}
CODE;

echo $advancedExample2 . "\n\n";

/**
 * SUMMARY: Best Practices for Controllers
 */
echo "\nSUMMARY: BEST PRACTICES FOR USING VALIDATION\n";
echo "----------------------------------------------------\n";
echo "1. For creating new records:\n";
echo "   - Use \$model->fill(\$data) followed by \$model->validate()\n";
echo "   - Or create a temporary model for validation before using Model::create()\n\n";

echo "2. For updating existing records:\n";
echo "   - Use \$model->fill(\$data) followed by \$model->validateAndSave()\n";
echo "   - This handles both validation and saving in one step\n\n";

echo "3. For single attribute validation:\n";
echo "   - Use \$model->validateAttribute('field', \$value) for quick validation\n\n";

echo "4. For error handling:\n";
echo "   - Use \$model->getErrors() to get all errors\n";
echo "   - Use \$model->getErrorsFor('field') for field-specific errors\n";
echo "   - Use \$model->hasErrors() to check if any validation failed\n\n";

echo "5. For API responses:\n";
echo "   - Format validation errors as part of your API response structure\n";
echo "   - Include both overall message and field-specific errors\n\n";

echo "6. For HTML forms:\n";
echo "   - Display errors next to the relevant form fields\n";
echo "   - Use session flashing for error persistence across redirects\n\n";

echo "========== END OF MODEL VALIDATION EXAMPLES ==========\n";
?>