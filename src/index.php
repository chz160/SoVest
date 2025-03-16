<?php

/**
 * SoVest Application Redirect File
 * 
 * This file provides a simple redirect to the main application entry point in the public directory.
 * All requests coming to the root index.php are forwarded to public/index.php while preserving
 * the original request path and query parameters.
 * 
 * This redirect is part of the SoVest application's architectural transition to a modern MVC structure
 * where the entry point is isolated in the public directory for improved security and organization.
 */

// Build the redirect URL with the current protocol and host
$protocol = isset($_SERVER['HTTPS']) && $_SERVER['HTTPS'] === 'on' ? 'https://' : 'http://';
$host = $_SERVER['HTTP_HOST'];
$uri = $_SERVER['REQUEST_URI'];

// Create the redirect URL
$redirect_url = $protocol . $host . '/public' . $uri;

// Redirect to the public directory
header('Location: ' . $redirect_url, true, 302);
exit;