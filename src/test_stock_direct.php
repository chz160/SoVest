<?php
/**
 * Direct test for Stock model validation
 * 
 * This is a simplified test that doesn't rely on full application setup
 */

// Define the trait and model directly
namespace App\Models\Traits {
    trait ValidationTrait
    {
        // These will be defined in the class that uses this trait
        // protected $rules = [];
        // protected $messages = [];
        protected $errors = [];

        public function validate()
        {
            $this->clearErrors();
            $valid = true;

            foreach ($this->rules as $attribute => $rules) {
                if (!$this->validateAttribute($attribute, $this->{$attribute})) {
                    $valid = false;
                }
            }

            return $valid;
        }

        public function validateAttribute($attribute, $value)
        {
            if (!isset($this->rules[$attribute])) {
                return true;
            }

            $rules = is_array($this->rules[$attribute]) 
                   ? $this->rules[$attribute] 
                   : explode('|', $this->rules[$attribute]);

            foreach ($rules as $rule) {
                $parameters = [];
                
                // Check if rule has parameters
                if (strpos($rule, ':') !== false) {
                    list($rule, $parameterStr) = explode(':', $rule, 2);
                    $parameters = explode(',', $parameterStr);
                }

                $method = 'validate' . ucfirst($rule);

                if (method_exists($this, $method)) {
                    if (!$this->{$method}($attribute, $value, $parameters)) {
                        return false;
                    }
                } else {
                    $this->addError($attribute, "Validation rule '$rule' not found");
                    return false;
                }
            }

            return true;
        }

        public function validateRequired($attribute, $value, $parameters = [])
        {
            if (empty($value) && $value !== 0 && $value !== '0') {
                $this->addError($attribute, $this->getMessage($attribute, 'required', 'The :attribute field is required.'));
                return false;
            }
            
            return true;
        }

        public function validateMax($attribute, $value, $parameters = [])
        {
            $length = isset($parameters[0]) ? (int)$parameters[0] : 0;
            
            if (!empty($value) && mb_strlen($value) > $length) {
                $this->addError($attribute, $this->getMessage($attribute, 'max', "The :attribute may not be greater than $length characters."));
                return false;
            }
            
            return true;
        }

        public function validateUnique($attribute, $value, $parameters = [])
        {
            // For test purposes, simply check if the value is "EXISTING"
            if ($value === "EXISTING") {
                $this->addError($attribute, $this->getMessage($attribute, 'unique', "The $attribute has already been taken."));
                return false;
            }
            
            return true;
        }

        public function addError($attribute, $message)
        {
            $this->errors[$attribute][] = str_replace(':attribute', $attribute, $message);
        }

        public function getErrors()
        {
            return $this->errors;
        }

        public function getErrorsFor($attribute)
        {
            return isset($this->errors[$attribute]) ? $this->errors[$attribute] : [];
        }

        public function clearErrors()
        {
            $this->errors = [];
        }

        protected function getMessage($attribute, $rule, $default)
        {
            if (isset($this->messages["$attribute.$rule"])) {
                return $this->messages["$attribute.$rule"];
            }
            
            if (isset($this->messages[$rule])) {
                return $this->messages[$rule];
            }
            
            return $default;
        }
    }
}

namespace Database\Models {
    class Stock {
        use Traits\ValidationTrait;

        // Properties that would normally be set by Eloquent
        public $stock_id;
        public $symbol;
        public $company_name;
        public $sector;
        public $created_at;
        public $exists = false;
        protected $primaryKey = 'stock_id';

        /**
         * Validation rules for Stock model
         */
        protected $rules = [
            'symbol' => ['required', 'max:10', 'unique'],
            'company_name' => ['required'],
            'sector' => []
        ];

        /**
         * Custom error messages for validation
         */
        protected $messages = [
            'symbol.required' => 'Stock symbol is required',
            'symbol.max' => 'Stock symbol cannot exceed 10 characters',
            'symbol.unique' => 'This stock symbol is already registered in the system',
            'company_name.required' => 'Company name is required'
        ];
    }
}

// Testing code
namespace {
    use Database\Models\Stock;

    echo "===== TESTING STOCK VALIDATION =====\n\n";

    // Test Case 1: Valid stock
    $validStock = new Stock();
    $validStock->symbol = "AAPL";
    $validStock->company_name = "Apple Inc.";
    $validStock->sector = "Technology";

    if ($validStock->validate()) {
        echo "Test 1 PASSED: Valid stock validated successfully\n";
    } else {
        echo "Test 1 FAILED: Valid stock should validate\n";
        print_r($validStock->getErrors());
    }

    // Test Case 2: Missing symbol
    $invalidStock1 = new Stock();
    $invalidStock1->company_name = "Missing Symbol Company";
    $invalidStock1->sector = "Finance";

    if (!$invalidStock1->validate()) {
        echo "Test 2 PASSED: Caught missing symbol\n";
        echo "  Errors: " . implode(", ", $invalidStock1->getErrorsFor('symbol')) . "\n";
    } else {
        echo "Test 2 FAILED: Should not validate without a symbol\n";
    }

    // Test Case 3: Missing company name
    $invalidStock2 = new Stock();
    $invalidStock2->symbol = "MSNG";
    $invalidStock2->sector = "Energy";

    if (!$invalidStock2->validate()) {
        echo "Test 3 PASSED: Caught missing company name\n";
        echo "  Errors: " . implode(", ", $invalidStock2->getErrorsFor('company_name')) . "\n";
    } else {
        echo "Test 3 FAILED: Should not validate without a company name\n";
    }

    // Test Case 4: Symbol too long
    $invalidStock3 = new Stock();
    $invalidStock3->symbol = "WAAAYTOOLONG";
    $invalidStock3->company_name = "Long Symbol Corp";
    $invalidStock3->sector = "Technology";

    if (!$invalidStock3->validate()) {
        echo "Test 4 PASSED: Caught too long symbol\n";
        echo "  Errors: " . implode(", ", $invalidStock3->getErrorsFor('symbol')) . "\n";
    } else {
        echo "Test 4 FAILED: Should not validate with too long symbol\n";
    }

    // Test Case 5: Test symbol uniqueness
    echo "\nSetting up uniqueness test...\n";

    $duplicateStock = new Stock();
    $duplicateStock->symbol = "EXISTING";  // This will trigger our mock uniqueness validation
    $duplicateStock->company_name = "Duplicate Inc.";
    $duplicateStock->sector = "Technology";

    if (!$duplicateStock->validate()) {
        echo "Test 5 PASSED: Caught duplicate symbol\n";
        echo "  Errors: " . implode(", ", $duplicateStock->getErrorsFor('symbol')) . "\n";
    } else {
        echo "Test 5 FAILED: Should not validate with duplicate symbol\n";
    }

    // Test Case 6: Empty sector is valid (sector is optional)
    $validStockWithoutSector = new Stock();
    $validStockWithoutSector->symbol = "NOSEC";
    $validStockWithoutSector->company_name = "No Sector Company";
    $validStockWithoutSector->sector = null; // explicitly set to null

    if ($validStockWithoutSector->validate()) {
        echo "Test 6 PASSED: Stock validates with empty sector (sector is optional)\n";
    } else {
        echo "Test 6 FAILED: Stock should validate with empty sector\n";
        print_r($validStockWithoutSector->getErrors());
    }

    echo "\n===== STOCK VALIDATION TESTING COMPLETE =====\n";
}