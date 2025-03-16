<?php
/**
 * SoVest - Validation Service
 *
 * This service provides validation functionality for both requests and models.
 * It supports complex validation scenarios with nested rules and detailed error messages.
 */

namespace App\Services;

// Handle the case where autoloading isn't set up
if (!interface_exists('App\Services\Interfaces\ValidationServiceInterface')) {
    require_once __DIR__ . '/../app/Services/Interfaces/ValidationServiceInterface.php';
}

use App\Services\Interfaces\ValidationServiceInterface;

class ValidationService implements ValidationServiceInterface
{
    /**
     * @var array Available validation rules
     */
    protected $rules = [];
    
    /**
     * @var array Default error messages
     */
    protected $defaultMessages = [];
    
    /**
     * Constructor
     */
    public function __construct()
    {
        $this->initializeRules();
        $this->initializeMessages();
    }
    
    /**
     * Initialize available validation rules
     */
    protected function initializeRules()
    {
        // Required validation
        $this->rules['required'] = function($value) {
            return !empty($value) || $value === '0' || $value === 0;
        };
        
        // Email validation
        $this->rules['email'] = function($value) {
            return empty($value) || filter_var($value, FILTER_VALIDATE_EMAIL) !== false;
        };
        
        // Numeric validation
        $this->rules['numeric'] = function($value) {
            return empty($value) || is_numeric($value);
        };
        
        // Min value validation
        $this->rules['min'] = function($value, $params) {
            $min = $params[0] ?? 0;
            return empty($value) || strlen($value) >= $min || (is_numeric($value) && $value >= $min);
        };
        
        // Max value validation
        $this->rules['max'] = function($value, $params) {
            $max = $params[0] ?? PHP_INT_MAX;
            return empty($value) || strlen($value) <= $max || (is_numeric($value) && $value <= $max);
        };
        
        // URL validation
        $this->rules['url'] = function($value) {
            return empty($value) || filter_var($value, FILTER_VALIDATE_URL) !== false;
        };
        
        // Boolean validation
        $this->rules['boolean'] = function($value) {
            return empty($value) || in_array($value, [true, false, 0, 1, '0', '1'], true);
        };
        
        // Date validation
        $this->rules['date'] = function($value) {
            return empty($value) || strtotime($value) !== false;
        };
        
        // In list validation
        $this->rules['in'] = function($value, $params) {
            return empty($value) || in_array($value, $params, true);
        };
        
        // Regular expression validation
        $this->rules['regex'] = function($value, $params) {
            return empty($value) || preg_match($params[0], $value);
        };
        
        // Required if validation
        $this->rules['required_if'] = function($value, $params, $data) {
            $otherField = $params[0] ?? null;
            $otherValue = $params[1] ?? null;
            
            if ($otherField && isset($data[$otherField]) && $data[$otherField] == $otherValue) {
                return !empty($value) || $value === '0' || $value === 0;
            }
            
            return true;
        };
        
        // Required unless validation
        $this->rules['required_unless'] = function($value, $params, $data) {
            $otherField = $params[0] ?? null;
            $otherValue = $params[1] ?? null;
            
            if ($otherField && isset($data[$otherField]) && $data[$otherField] != $otherValue) {
                return !empty($value) || $value === '0' || $value === 0;
            }
            
            return true;
        };
    }
    
    /**
     * Initialize default error messages
     */
    protected function initializeMessages()
    {
        $this->defaultMessages = [
            'required' => 'The %s field is required.',
            'email' => 'The %s field must be a valid email address.',
            'numeric' => 'The %s field must be a number.',
            'min' => 'The %s field must be at least %s.',
            'max' => 'The %s field must not exceed %s.',
            'url' => 'The %s field must be a valid URL.',
            'boolean' => 'The %s field must be a boolean value.',
            'date' => 'The %s field must be a valid date.',
            'in' => 'The %s field must be one of: %s.',
            'regex' => 'The %s field format is invalid.',
            'required_if' => 'The %s field is required when %s is %s.',
            'required_unless' => 'The %s field is required unless %s is %s.'
        ];
    }
    
    /**
     * Validate request data against validation rules
     *
     * @param array $rules Validation rules
     * @param array $data Data to validate
     * @param array $customMessages Custom error messages
     * @return array|bool Array of errors or true if valid
     */
    public function validateRequest(array $rules, array $data, array $customMessages = [])
    {
        $errors = [];
        
        foreach ($rules as $field => $fieldRules) {
            // Check if we're validating a nested object or array
            if (is_array($fieldRules) && !$this->isRuleString(key($fieldRules) ?? '')) {
                $nestedErrors = $this->validateNested($field, $fieldRules, $data, $customMessages);
                
                if (!empty($nestedErrors)) {
                    $errors[$field] = $nestedErrors;
                }
                
                continue;
            }
            
            // Convert string rules to array
            if (is_string($fieldRules)) {
                $fieldRules = explode('|', $fieldRules);
            }
            
            foreach ($fieldRules as $rule) {
                $ruleName = $rule;
                $params = [];
                
                // Extract parameters from rule
                if (is_string($rule) && strpos($rule, ':') !== false) {
                    list($ruleName, $paramStr) = explode(':', $rule, 2);
                    $params = explode(',', $paramStr);
                }
                
                // Get field value
                $value = $this->getNestedValue($field, $data);
                
                // Apply validation rule
                $result = $this->applyRule($ruleName, $field, $value, $params, $customMessages, $data);
                
                if ($result !== true) {
                    if (!isset($errors[$field])) {
                        $errors[$field] = [];
                    }
                    
                    $errors[$field][] = $result;
                }
            }
        }
        
        return empty($errors) ? true : $errors;
    }
    
    /**
     * Validate a model against its validation rules
     *
     * @param object $model Model to validate
     * @param array $rules Optional override rules
     * @param array $customMessages Custom error messages
     * @return array|bool Array of errors or true if valid
     */
    public function validateModel($model, array $rules = null, array $customMessages = [])
    {
        // Get model rules
        $modelRules = $rules;
        
        // If no override rules provided, try to get rules from model
        if ($modelRules === null) {
            // Check using ValidationTrait approach
            if (property_exists($model, 'rules')) {
                $modelRules = $model->rules;
            } 
            // Check using method approach
            elseif (method_exists($model, 'getValidationRules')) {
                $modelRules = $model->getValidationRules();
            } 
            // No rules found
            else {
                return true;
            }
        }
        
        // Get custom messages from model if available
        if (empty($customMessages) && property_exists($model, 'messages')) {
            $customMessages = $model->messages;
        }
        
        // Convert model to array for validation
        $data = [];
        
        // Use toArray method if available
        if (method_exists($model, 'toArray')) {
            $data = $model->toArray();
        } 
        // Otherwise get object properties
        else {
            $data = get_object_vars($model);
        }
        
        // Validate model data
        $validationResult = $this->validateRequest($modelRules, $data, $customMessages);
        
        // Set errors on model if validation failed and model uses ValidationTrait
        if ($validationResult !== true && property_exists($model, 'errors') && method_exists($model, 'addError')) {
            // Clear existing errors
            if (method_exists($model, 'clearErrors')) {
                $model->clearErrors();
            }
            
            // Add new errors
            foreach ($validationResult as $field => $fieldErrors) {
                foreach ($fieldErrors as $error) {
                    $model->addError($field, $error);
                }
            }
        }
        
        return $validationResult;
    }
    
    /**
     * Apply a validation rule to a field
     *
     * @param string $rule Rule to apply
     * @param string $field Field name
     * @param mixed $value Field value
     * @param array $params Rule parameters
     * @param array $customMessages Custom error messages
     * @param array $data Complete data set (for context-aware rules)
     * @return string|bool True if valid, error message if invalid
     */
    public function applyRule($rule, $field, $value, array $params = [], array $customMessages = [], array $data = [])
    {
        // Check if rule exists
        if (!isset($this->rules[$rule])) {
            return true;
        }
        
        // Apply rule
        $callback = $this->rules[$rule];
        $isValid = $callback($value, $params, $data);
        
        if ($isValid) {
            return true;
        }
        
        // Get field label
        $fieldLabel = ucfirst(str_replace('_', ' ', $field));
        
        // Get error message
        $messageKey = "{$field}.{$rule}";
        $genericKey = "{$rule}";
        
        $message = $customMessages[$messageKey] ?? 
                  $customMessages[$genericKey] ?? 
                  $this->defaultMessages[$rule] ?? 
                  "The {$fieldLabel} field is invalid.";
        
        // Format message with parameters
        $replacements = array_merge([$fieldLabel], $params);
        return vsprintf($message, $replacements);
    }
    
    /**
     * Validate nested objects or arrays
     *
     * @param string $prefix Field prefix
     * @param array $rules Nested rules
     * @param array $data Data to validate
     * @param array $customMessages Custom error messages
     * @return array Errors for nested validation
     */
    protected function validateNested($prefix, array $rules, array $data, array $customMessages = [])
    {
        $errors = [];
        $nestedData = $this->getNestedValue($prefix, $data);
        
        if (!is_array($nestedData)) {
            return [];
        }
        
        // Handle wildcard validation (for arrays)
        if (isset($rules['*'])) {
            $wildcardRules = $rules['*'];
            
            foreach ($nestedData as $index => $item) {
                $itemPrefix = "{$prefix}.{$index}";
                
                if (is_array($wildcardRules) && !$this->isRuleString(key($wildcardRules) ?? '')) {
                    // Nested object in array
                    $itemErrors = $this->validateNested($itemPrefix, $wildcardRules, $data, $customMessages);
                    
                    if (!empty($itemErrors)) {
                        $errors[$index] = $itemErrors;
                    }
                } else {
                    // Simple field in array
                    $itemData = [$itemPrefix => $item];
                    $itemRules = [$itemPrefix => $wildcardRules];
                    
                    $itemValidation = $this->validateRequest($itemRules, $itemData, $customMessages);
                    
                    if ($itemValidation !== true) {
                        $errors[$index] = $itemValidation[$itemPrefix];
                    }
                }
            }
            
            return $errors;
        }
        
        // Handle object validation
        $nestedRules = [];
        
        foreach ($rules as $field => $fieldRules) {
            $nestedRules["{$prefix}.{$field}"] = $fieldRules;
        }
        
        $nestedValidation = $this->validateRequest($nestedRules, $data, $customMessages);
        
        if ($nestedValidation !== true) {
            foreach ($nestedValidation as $field => $fieldErrors) {
                $fieldName = str_replace("{$prefix}.", '', $field);
                $errors[$fieldName] = $fieldErrors;
            }
        }
        
        return $errors;
    }
    
    /**
     * Get a nested value from an array using dot notation
     *
     * @param string $key Key in dot notation
     * @param array $data Data array
     * @return mixed Value or null if not found
     */
    protected function getNestedValue($key, array $data)
    {
        if (isset($data[$key])) {
            return $data[$key];
        }
        
        $segments = explode('.', $key);
        $current = $data;
        
        foreach ($segments as $segment) {
            if (!is_array($current) || !isset($current[$segment])) {
                return null;
            }
            
            $current = $current[$segment];
        }
        
        return $current;
    }
    
    /**
     * Check if a string is a rule definition
     *
     * @param string $rule Rule to check
     * @return bool True if it's a rule string
     */
    protected function isRuleString($rule)
    {
        if (empty($rule)) {
            return false;
        }
        
        return is_string($rule) && (
            isset($this->rules[$rule]) || 
            strpos($rule, ':') !== false
        );
    }
    
    /**
     * Get the list of available validation rules
     *
     * @return array Array of available rules
     */
    public function getAvailableRules()
    {
        return array_keys($this->rules);
    }
    
    /**
     * Add a custom validation rule
     *
     * @param string $rule Rule name
     * @param callable $callback Validation callback
     * @param string $errorMessage Default error message
     * @return self For method chaining
     */
    public function addRule($rule, callable $callback, $errorMessage)
    {
        $this->rules[$rule] = $callback;
        $this->defaultMessages[$rule] = $errorMessage;
        
        return $this;
    }
    
    /**
     * Get instance for singleton pattern (backward compatibility)
     *
     * @return ValidationService
     */
    public static function getInstance()
    {
        static $instance = null;
        
        if ($instance === null) {
            $instance = new self();
        }
        
        return $instance;
    }
}