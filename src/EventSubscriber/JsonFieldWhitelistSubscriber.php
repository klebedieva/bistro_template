<?php

namespace App\EventSubscriber;

use App\Service\JsonFieldWhitelistService;
use Symfony\Component\EventDispatcher\EventSubscriberInterface;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpKernel\Event\RequestEvent;
use Symfony\Component\HttpKernel\KernelEvents;

/**
 * Enforce JSON field whitelist for API endpoints
 * Prevents mass assignment attacks by filtering out unauthorized fields
 * Also enforces JSON depth limit to prevent stack overflow attacks
 */
class JsonFieldWhitelistSubscriber implements EventSubscriberInterface
{
    // Maximum JSON nesting depth (prevents stack overflow attacks)
    // PHP default is 512, but we limit to 64 for security
    private const MAX_JSON_DEPTH = 64;
    
    // Maximum number of fields in JSON object (prevents DoS via excessive fields)
    private const MAX_FIELD_COUNT = 100;
    
    // Maximum field name length (prevents DoS via very long field names)
    private const MAX_FIELD_NAME_LENGTH = 255;

    public function __construct(
        private JsonFieldWhitelistService $whitelistService
    ) {
    }

    public static function getSubscribedEvents(): array
    {
        return [
            KernelEvents::REQUEST => ['onKernelRequest', 10], // Priority 10 to run after payload size check
        ];
    }

    public function onKernelRequest(RequestEvent $event): void
    {
        if (!$event->isMainRequest()) {
            return;
        }

        $request = $event->getRequest();
        $path = $request->getPathInfo();

        // Only process API endpoints
        if (strpos($path, '/api') !== 0) {
            return;
        }

        // Only check POST, PUT, PATCH requests with JSON content
        if (!in_array($request->getMethod(), ['POST', 'PUT', 'PATCH'], true)) {
            return;
        }

        $contentType = $request->headers->get('Content-Type', '');
        if (!str_contains(strtolower($contentType), 'application/json')) {
            return;
        }

        // Get JSON data
        $content = $request->getContent(false);
        if ($content === false || empty($content)) {
            return;
        }

        // Decode JSON with depth limit to prevent stack overflow attacks
        $data = json_decode($content, true, self::MAX_JSON_DEPTH);
        
        // Check for JSON decode errors
        $jsonError = json_last_error();
        if ($jsonError !== JSON_ERROR_NONE) {
            $errorMessage = match($jsonError) {
                JSON_ERROR_DEPTH => sprintf('JSON maximum nesting depth exceeded (max: %d levels)', self::MAX_JSON_DEPTH),
                JSON_ERROR_SYNTAX => 'Invalid JSON syntax',
                JSON_ERROR_CTRL_CHAR => 'Invalid JSON: control character error',
                JSON_ERROR_STATE_MISMATCH => 'Invalid JSON: state mismatch',
                JSON_ERROR_UTF8 => 'Invalid JSON: invalid UTF-8 characters',
                default => 'Invalid JSON format'
            };
            
            $event->setResponse(new JsonResponse([
                'success' => false,
                'error' => 'Invalid JSON payload',
                'message' => $errorMessage,
                'code' => 'JSON_PARSE_ERROR'
            ], 400));
            return;
        }

        if (!is_array($data)) {
            return; // Invalid JSON structure, will be handled by other validators
        }

        // Ensure JSON is an object (associative array), not a plain array
        if (array_is_list($data)) {
            $event->setResponse(new JsonResponse([
                'success' => false,
                'error' => 'Invalid JSON structure',
                'message' => 'JSON must be an object, not an array',
                'code' => 'JSON_STRUCTURE_ERROR'
            ], 400));
            return;
        }

        // Check field count limit (prevent DoS via excessive fields)
        $fieldCount = count($data);
        if ($fieldCount > self::MAX_FIELD_COUNT) {
            $event->setResponse(new JsonResponse([
                'success' => false,
                'error' => 'Too many fields',
                'message' => sprintf('JSON object contains too many fields (max: %d, received: %d)', self::MAX_FIELD_COUNT, $fieldCount),
                'code' => 'TOO_MANY_FIELDS'
            ], 400));
            return;
        }

        // Check field name length limit (prevent DoS via very long field names)
        foreach (array_keys($data) as $fieldName) {
            if (strlen($fieldName) > self::MAX_FIELD_NAME_LENGTH) {
                $event->setResponse(new JsonResponse([
                    'success' => false,
                    'error' => 'Field name too long',
                    'message' => sprintf('Field name exceeds maximum length (max: %d characters)', self::MAX_FIELD_NAME_LENGTH),
                    'code' => 'FIELD_NAME_TOO_LONG'
                ], 400));
                return;
            }
        }

        // Check for rejected fields
        $rejectedFields = $this->whitelistService->getRejectedFields($data, $path);
        
        if (!empty($rejectedFields)) {
            $event->setResponse(new JsonResponse([
                'success' => false,
                'error' => 'Unauthorized fields detected',
                'message' => sprintf(
                    'The following fields are not allowed for this endpoint: %s',
                    implode(', ', $rejectedFields)
                ),
                'rejected_fields' => array_values($rejectedFields),
                'code' => 'UNAUTHORIZED_FIELDS'
            ], 400));
            return;
        }

        // Store filtered data in request attributes for controllers to use
        // Controllers can access via: $request->attributes->get('filtered_json_data')
        $filteredData = $this->whitelistService->filterFields($data, $path);
        $request->attributes->set('filtered_json_data', $filteredData);
        $request->attributes->set('original_json_data', $data); // Keep original for logging if needed
    }
}

