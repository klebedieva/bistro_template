<?php
// Test Symfony Kernel loading
error_reporting(E_ALL);
ini_set('display_errors', 1);
ini_set('log_errors', 1);

echo "Testing Symfony Kernel...\n";
echo "PHP Version: " . PHP_VERSION . "\n";
echo "Working Directory: " . getcwd() . "\n\n";

// Check if autoload exists
$autoloadPath = dirname(__DIR__) . '/vendor/autoload_runtime.php';
echo "Autoload path: $autoloadPath\n";
echo "Autoload exists: " . (file_exists($autoloadPath) ? 'YES' : 'NO') . "\n\n";

if (!file_exists($autoloadPath)) {
    die("ERROR: Autoload file not found!\n");
}

try {
    require_once $autoloadPath;
    echo "Autoload loaded successfully\n\n";
    
    // Try to create context
    $context = [
        'APP_ENV' => $_ENV['APP_ENV'] ?? 'prod',
        'APP_DEBUG' => (bool) ($_ENV['APP_DEBUG'] ?? false),
    ];
    echo "Context: " . json_encode($context, JSON_PRETTY_PRINT) . "\n\n";
    
    // Try to create kernel function
    $kernelFunction = require dirname(__DIR__) . '/public/index.php';
    echo "Kernel function created\n\n";
    
    // Try to create kernel
    $kernel = $kernelFunction($context);
    echo "Kernel created: " . get_class($kernel) . "\n";
    echo "SUCCESS: Symfony Kernel loaded!\n";
    
} catch (Throwable $e) {
    echo "ERROR: " . get_class($e) . "\n";
    echo "Message: " . $e->getMessage() . "\n";
    echo "File: " . $e->getFile() . ":" . $e->getLine() . "\n";
    echo "\nStack trace:\n" . $e->getTraceAsString() . "\n";
}
