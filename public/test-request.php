<?php
// Test handling a real HTTP request through Symfony
error_reporting(E_ALL);
ini_set('display_errors', 1);
ini_set('log_errors', 1);

use Symfony\Component\HttpFoundation\Request;

echo "<pre>";
echo "Testing Symfony HTTP Request Handling...\n\n";

try {
    require_once dirname(__DIR__) . '/vendor/autoload_runtime.php';
    
    $context = [
        'APP_ENV' => $_ENV['APP_ENV'] ?? 'prod',
        'APP_DEBUG' => (bool) ($_ENV['APP_DEBUG'] ?? false),
    ];
    
    echo "Context: " . json_encode($context, JSON_PRETTY_PRINT) . "\n\n";
    
    $kernelFunction = require dirname(__DIR__) . '/public/index.php';
    $kernel = $kernelFunction($context);
    
    echo "Kernel created: " . get_class($kernel) . "\n\n";
    
    // Try to create a simple request
    $request = Request::create('/', 'GET');
    echo "Request created for path: " . $request->getPathInfo() . "\n\n";
    
    // Try to handle the request
    echo "Attempting to handle request...\n";
    $response = $kernel->handle($request);
    
    echo "Response status: " . $response->getStatusCode() . "\n";
    echo "Response content length: " . strlen($response->getContent()) . " bytes\n";
    echo "SUCCESS: Request handled!\n";
    
    $kernel->terminate($request, $response);
    
} catch (Throwable $e) {
    echo "ERROR: " . get_class($e) . "\n";
    echo "Message: " . $e->getMessage() . "\n";
    echo "File: " . $e->getFile() . ":" . $e->getLine() . "\n";
    echo "\nStack trace:\n" . $e->getTraceAsString() . "\n";
}
echo "</pre>";
