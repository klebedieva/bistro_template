<?php
// Simple health check endpoint
header('Content-Type: application/json');
echo json_encode([
    'status' => 'ok',
    'php_version' => PHP_VERSION,
    'server' => $_SERVER['SERVER_SOFTWARE'] ?? 'unknown',
    'timestamp' => date('Y-m-d H:i:s')
]);
