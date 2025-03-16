<?php
/**
 * SoVest - Validation Service Interface
 *
 * This interface defines the contract for validation services
 * in the SoVest application. It provides methods for validating both
 * requests and models with support for complex validation scenarios.
 */

namespace App\Services\Interfaces;

interface ValidationServiceInterface
{
    /**
     * Validate request data against validation rules
     *
     * @param array $rules Validation rules
     * @param array $data Data to validate
     * @param array $customMessages Custom error messages
     * @return array|bool Array of errors or true if valid
     */
    public function validateRequest(array $rules, array $data, array $customMessages = []);
    
    /**
     * Validate a model against its validation rules
     *
     * @param object $model Model to validate
     * @param array $rules Optional override rules
     * @param array $customMessages Custom error messages
     * @return array|bool Array of errors or true if valid
     */
    public function validateModel($model, array $rules = null, array $customMessages = []);
    
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
    public function applyRule($rule, $field, $value, array $params = [], array $customMessages = [], array $data = []);
    
    /**
     * Get the list of available validation rules
     *
     * @return array Array of available rules
     */
    public function getAvailableRules();
    
    /**
     * Add a custom validation rule
     *
     * @param string $rule Rule name
     * @param callable $callback Validation callback
     * @param string $errorMessage Default error message
     * @return self For method chaining
     */
    public function addRule($rule, callable $callback, $errorMessage);
}