<?php
/**
 * SoVest - Error Handler Interface
 *
 * This interface defines the contract for error handling throughout the application.
 * It centralizes all error handling logic for easier maintenance and consistency.
 */

namespace App\Handlers\Interfaces;

interface ErrorHandlerInterface
{
    /**
     * Get the singleton instance of ErrorHandler
     *
     * @return ErrorHandlerInterface
     */
    public static function getInstance();

    /**
     * Register global error and exception handlers
     *
     * @return void
     */
    public function register();

    /**
     * Handle PHP errors
     *
     * @param int $level Error level
     * @param string $message Error message
     * @param string $file File where the error occurred
     * @param int $line Line number where the error occurred
     * @param array $context Error context
     * @return bool Whether the error was handled
     */
    public function handleError($level, $message, $file, $line, $context = []);

    /**
     * Handle exceptions
     *
     * @param \Throwable $exception The exception to handle
     * @return void
     */
    public function handleException($exception);

    /**
     * Handle application shutdown and fatal errors
     *
     * @return void
     */
    public function handleShutdown();

    /**
     * Log errors with different levels of severity
     *
     * @param string $message Error message
     * @param string $level Error level (e.g., 'error', 'warning', 'info')
     * @param array $context Additional context for the error
     * @return void
     */
    public function logError($message, $level = 'error', array $context = []);

    /**
     * Display appropriate error messages to users
     *
     * @param string $message Error message
     * @param int $statusCode HTTP status code
     * @param bool $isApiRequest Whether this is an API request
     * @return void
     */
    public function displayError($message, $statusCode = 500, $isApiRequest = false);
}