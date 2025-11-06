<?php

namespace App\EventSubscriber;

use App\DTO\ApiResponseDTO;
use Psr\Log\LoggerInterface;
use Symfony\Component\EventDispatcher\EventSubscriberInterface;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpKernel\Event\ExceptionEvent;
use Symfony\Component\HttpKernel\KernelEvents;

/**
 * API Exception Subscriber
 *
 * WHAT THIS DOES (Simple Explanation):
 * ====================================
 * This class automatically catches ALL errors that happen in API controllers
 * and converts them to nice JSON error responses. You don't need to call it
 * manually - Symfony does it automatically when an exception occurs.
 *
 * Think of it like a safety net: if something goes wrong in your controller
 * and you didn't catch it, this subscriber catches it and handles it properly.
 *
 * WHY IT EXISTS:
 * =============
 * Before this subscriber, every controller needed try-catch blocks for every
 * possible error. This meant:
 * - Lots of duplicate code
 * - Inconsistent error messages
 * - Easy to forget error handling
 *
 * Now, controllers can focus on business logic, and this subscriber handles
 * all the error formatting automatically.
 *
 * HOW IT WORKS (Step by Step):
 * ============================
 * 1. You write code in a controller (maybe without try-catch)
 * 2. If an exception is thrown and not caught by the controller
 * 3. Symfony automatically triggers the KernelEvents::EXCEPTION event
 * 4. This subscriber receives the event
 * 5. It checks if the request is to an API endpoint (/api/*)
 * 6. It determines what type of error it is
 * 7. It logs full error details for developers (in var/log/)
 * 8. It returns a safe, generic error message to the client
 * 9. Client gets JSON: {"success": false, "message": "Erreur interne du serveur"}
 *
 * WHEN IT TRIGGERS:
 * ================
 * - Only for API requests (paths starting with /api)
 * - Only for exceptions that were NOT caught in controllers
 * - Automatically - you don't need to do anything
 *
 * EXCEPTION TYPE MAPPING:
 * =======================
 * Different exception types get different HTTP status codes:
 * - InvalidArgumentException → 422 (Unprocessable Entity) - Business logic errors
 * - TypeError | ValueError → 422 (Unprocessable Entity) - Type validation errors
 * - AccessDeniedException → 403 (Forbidden) - Authorization errors
 * - NotFoundHttpException → 404 (Not Found) - Resource not found
 * - All other exceptions → 500 (Internal Server Error) - Unexpected errors
 *
 * SECURITY:
 * ========
 * - Never exposes internal error details to clients (prevents information leakage)
 * - Logs full details for developers (for debugging)
 * - Returns generic messages to clients (prevents attackers from understanding system)
 *
 * INTEGRATION WITH CONTROLLERS:
 * =============================
 * Controllers can still catch specific exceptions if needed:
 * 
 * ```php
 * try {
 *     $order = $this->orderService->createOrder($dto);
 * } catch (\InvalidArgumentException $e) {
 *     // This is caught here (custom handling)
 *     return $this->errorResponse($e->getMessage(), 422);
 * }
 * // All other exceptions go to ApiExceptionSubscriber automatically
 * ```
 *
 * This is called "hybrid approach":
 * - Specific exceptions (like InvalidArgumentException) can be caught in controllers
 * - All other exceptions are handled by this subscriber
 *
 * HOW TO DEBUG:
 * ============
 * - Check var/log/ directory for full error details
 * - Exception details include: file, line, stack trace, request path, method
 * - Client receives generic message, but full details are in logs
 *
 * FOR BEGINNERS:
 * =============
 * You don't need to understand all the details. Just know:
 * 1. If you don't catch an exception in your controller, this subscriber handles it
 * 2. It logs errors for you (check var/log/)
 * 3. It returns safe error messages to clients
 * 4. You can still catch specific exceptions if you need custom handling
 *
 * See also: src/EventSubscriber/README.md for more information about Event Subscribers
 */
class ApiExceptionSubscriber implements EventSubscriberInterface
{
    /**
     * Constructor
     *
     * Injects dependencies required for exception handling:
     * - LoggerInterface: Logs error details for debugging and monitoring
     *
     * @param LoggerInterface $logger Logger for error tracking
     */
    public function __construct(
        private LoggerInterface $logger
    ) {}

    /**
     * Get subscribed events
     *
     * This method tells Symfony which events this subscriber should listen to.
     * We subscribe to KernelEvents::EXCEPTION to catch all unhandled exceptions.
     *
     * Priority: 0 (default) - runs after other exception handlers
     * Lower priority means it runs later, allowing other handlers to process first
     *
     * @return array<string, array<int, int|string>> Event names and their priorities
     */
    public static function getSubscribedEvents(): array
    {
        return [
            // Listen to exception event with default priority (0)
            // This allows other exception handlers to process first if needed
            KernelEvents::EXCEPTION => ['onKernelException', 0],
        ];
    }

    /**
     * Handle kernel exception event
     *
     * This method is called automatically by Symfony when an unhandled exception occurs.
     * It processes the exception and converts it to a standardized JSON error response.
     *
     * Processing flow:
     * 1. Get the exception and request from the event
     * 2. Check if this is an API request (path starts with /api)
     * 3. If not API request, return early (let Symfony handle it normally)
     * 4. Determine HTTP status code based on exception type
     * 5. Log error details for developers (full message and stack trace)
     * 6. Create standardized error response
     * 7. Set response on event (this prevents default Symfony error page)
     *
     * Security considerations:
     * - Never expose internal error details to client (prevents information leakage)
     * - Log full details for developers (for debugging)
     * - Return generic messages to clients (prevents attackers from understanding system internals)
     *
     * @param ExceptionEvent $event Symfony exception event containing exception and request
     */
    public function onKernelException(ExceptionEvent $event): void
    {
        // Get the exception that was thrown
        $exception = $event->getThrowable();
        
        // Get the request to check if this is an API endpoint
        $request = $event->getRequest();
        
        // Only process API requests (paths starting with /api)
        // For non-API requests (HTML pages), let Symfony handle errors normally
        // This allows admin panel and other HTML pages to show standard Symfony error pages
        if (!str_starts_with($request->getPathInfo(), '/api')) {
            // Not an API request, return early and let Symfony handle it
            // This means HTML pages will show standard Symfony error pages
            return;
        }

        // Determine HTTP status code and error message based on exception type
        // Different exception types indicate different error categories
        [$statusCode, $message, $logLevel] = $this->determineErrorResponse($exception);

        // Log error details for developers
        // This provides full context for debugging while keeping client response generic
        // We log different levels based on exception type:
        // - Warning for validation/type errors (expected, but shouldn't happen after DTO validation)
        // - Error for unexpected exceptions (indicates bugs or system issues)
        $this->logger->log(
            $logLevel,
            'API exception occurred',
            [
                'exception' => get_class($exception),
                'message' => $exception->getMessage(),
                'trace' => $exception->getTraceAsString(),
                'file' => $exception->getFile(),
                'line' => $exception->getLine(),
                'path' => $request->getPathInfo(),
                'method' => $request->getMethod(),
            ]
        );

        // Create standardized error response using ApiResponseDTO
        // This ensures all API errors have consistent format:
        // {
        //   "success": false,
        //   "message": "Error message"
        // }
        $response = new ApiResponseDTO(
            success: false,
            message: $message
        );

        // Set the response on the event
        // This prevents Symfony from showing default error page
        // Instead, client receives JSON error response
        $event->setResponse(new JsonResponse($response->toArray(), $statusCode));
    }

    /**
     * Determine error response details based on exception type
     *
     * This method maps exception types to appropriate HTTP status codes and messages.
     * It provides intelligent error handling based on the nature of the exception.
     *
     * Exception type mapping:
     * - InvalidArgumentException: Business logic/validation errors → 422
     * - TypeError | ValueError: Type validation errors → 422
     * - AccessDeniedException: Authorization errors → 403
     * - NotFoundHttpException: Resource not found → 404
     * - All others: Unexpected errors → 500
     *
     * Log levels:
     * - Warning: For validation/type errors (expected but shouldn't happen)
     * - Error: For unexpected exceptions (indicates bugs or system issues)
     *
     * @param \Throwable $exception The exception that was thrown
     * @return array{0: int, 1: string, 2: string} Array containing [statusCode, message, logLevel]
     */
    private function determineErrorResponse(\Throwable $exception): array
    {
        // Business logic and validation errors
        // These are expected from services when business rules are violated
        // Examples: empty cart, invalid address, invalid coupon code
        // Status 422 (Unprocessable Entity) indicates client error with valid syntax
        if ($exception instanceof \InvalidArgumentException) {
            return [
                422, // Unprocessable Entity
                $exception->getMessage(), // Use exception message (it's user-friendly)
                'warning' // Warning level - expected but shouldn't happen after DTO validation
            ];
        }

        // Type errors (should not happen after DTO validation, but defense in depth)
        // These indicate type mismatches that should have been caught by validation
        // Status 422 indicates validation error
        if ($exception instanceof \TypeError || $exception instanceof \ValueError) {
            return [
                422, // Unprocessable Entity
                'Erreur de validation des données', // Generic message (don't expose type details)
                'warning' // Warning level - indicates potential bug in validation logic
            ];
        }

        // Authorization errors (access denied)
        // These occur when user tries to access resource without proper permissions
        // Status 403 (Forbidden) indicates authentication succeeded but authorization failed
        if ($exception instanceof \Symfony\Component\Security\Core\Exception\AccessDeniedException) {
            return [
                403, // Forbidden
                'Accès refusé', // Access denied message
                'warning' // Warning level - expected for unauthorized access attempts
            ];
        }

        // Resource not found errors
        // These occur when requested resource doesn't exist
        // Status 404 (Not Found) indicates resource doesn't exist
        if ($exception instanceof \Symfony\Component\HttpKernel\Exception\NotFoundHttpException) {
            return [
                404, // Not Found
                'Ressource introuvable', // Resource not found message
                'info' // Info level - expected for missing resources
            ];
        }

        // All other exceptions (unexpected errors)
        // These indicate bugs, system issues, or unexpected conditions
        // Status 500 (Internal Server Error) indicates server-side problem
        // We return generic message to client (security best practice)
        // Full error details are logged for developers
        return [
            500, // Internal Server Error
            'Erreur interne du serveur', // Generic message (don't expose internal errors)
            'error' // Error level - indicates unexpected problem that needs investigation
        ];
    }
}

