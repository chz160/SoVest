<?php
/**
 * SoVest - New Response Formatter Service
 *
 * This service standardizes response structures across the application
 * for different formats (JSON, HTML, XML) and response types (success, error).
 */

namespace App\Services;

use App\Services\Interfaces\ResponseFormatterInterface;
use a simple approach with viewRenderer if available
            if ($this->viewRenderer !== null && method_exists($this->viewRenderer, 'render')) {
                $viewData = $data;

class ResponseFormatter implements ResponseFormatterInterface
{
    /**
     * @var ResponseFormatter|null Singleton instance of the service
     */
    private static $instance = null;

    /**
     * Get the singleton instance of ResponseFormatter
     *
     * @return ResponseFormatter
     */
    public static function getInstance()
    {
        if (self::$instance === null) {
            self::$instance = new self();
        }

        return self::$instance;
    }

    /**
     * Constructor - now public to support dependency injection
     * while maintaining backward compatibility with singleton pattern
     */
    public function __construct($viewRenderer = null)
    {
        // TODO: Implement constructor with proper dependency injection
        // This is a generated stub, you may need to customize it

    }

    /**
     * Json
     *
     * @param array $data Data
     * @param int $statusCode Status Code
     * @return mixed Result of the operation
     */
    public function json(array $data, int $statusCode = 200)
    {
        // TODO: Implement json method
        // This is a generated stub, you need to copy the implementation from the original service
        // See: services/ResponseFormatter.php for the original implementation

        return null;
    }

    /**
     * Json Success
     *
     * @param string $message Message
     * @param array $data Data
     * @param ?string $redirect Redirect
     * @return mixed Result of the operation
     */
    public function jsonSuccess(string $message, array $data = [], ?string $redirect = null)
    {
        // TODO: Implement jsonSuccess method
        // This is a generated stub, you need to copy the implementation from the original service
        // See: services/ResponseFormatter.php for the original implementation

        return null;
    }

    /**
     * Json Error
     *
     * @param string $message Message
     * @param array $errors Errors
     * @param int $statusCode Status Code
     * @return mixed Result of the operation
     */
    public function jsonError(string $message, array $errors = [], int $statusCode = 400)
    {
        // TODO: Implement jsonError method
        // This is a generated stub, you need to copy the implementation from the original service
        // See: services/ResponseFormatter.php for the original implementation

        return null;
    }

    /**
     * Html
     *
     * @param string $content Content
     * @param int $statusCode Status Code
     * @return mixed Result of the operation
     */
    public function html(string $content, int $statusCode = 200)
    {
        // TODO: Implement html method
        // This is a generated stub, you need to copy the implementation from the original service
        // See: services/ResponseFormatter.php for the original implementation

        return null;
    }

    /**
     * Xml
     *
     * @param string $xml Xml
     * @param int $statusCode Status Code
     * @return mixed Result of the operation
     */
    public function xml(string $xml, int $statusCode = 200)
    {
        // TODO: Implement xml method
        // This is a generated stub, you need to copy the implementation from the original service
        // See: services/ResponseFormatter.php for the original implementation

        return null;
    }

    /**
     * Validation Errors
     *
     * @param array $errors Errors
     * @param string $format Format
     * @param int $statusCode Status Code
     * @return mixed Result of the operation
     */
    public function validationErrors(array $errors, string $format = 'json', int $statusCode = 422)
    {
        // TODO: Implement validationErrors method
        // This is a generated stub, you need to copy the implementation from the original service
        // See: services/ResponseFormatter.php for the original implementation

        return null;
    }

    /**
     * Redirect
     *
     * @param string $url Url
     * @param int $statusCode Status Code
     * @param array $flash Flash
     * @return mixed Result of the operation
     */
    public function redirect(string $url, int $statusCode = 302, array $flash = [])
    {
        // TODO: Implement redirect method
        // This is a generated stub, you need to copy the implementation from the original service
        // See: services/ResponseFormatter.php for the original implementation

        return null;
    }

    /**
     * Is Api Request
     *
     * @return bool True on success, false on failure
     */
    public function isApiRequest(): bool
    {
        // TODO: Implement isApiRequest method
        // This is a generated stub, you need to copy the implementation from the original service
        // See: services/ResponseFormatter.php for the original implementation

        return false;
    }

    /**
     * Respond
     *
     * @param string $view View
     * @param array $data Data
     * @param string $successMessage Success Message
     * @param ?string $redirectUrl Redirect Url
     * @return mixed Result of the operation
     */
    public function respond(string $view, array $data = [], string $successMessage = '', ?string $redirectUrl = null)
    {
        // TODO: Implement respond method
        // This is a generated stub, you need to copy the implementation from the original service
        // See: services/ResponseFormatter.php for the original implementation

        return null;
    }

    /**
     * Respond With Error
     *
     * @param string $view View
     * @param array $data Data
     * @param string $errorMessage Error Message
     * @param ?string $redirectUrl Redirect Url
     * @param int $statusCode Status Code
     * @return mixed Result of the operation
     */
    public function respondWithError(string $view, array $data = [], string $errorMessage = '', ?string $redirectUrl = null, int $statusCode = 400)
    {
        // TODO: Implement respondWithError method
        // This is a generated stub, you need to copy the implementation from the original service
        // See: services/ResponseFormatter.php for the original implementation

        return null;
    }
}