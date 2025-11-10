<?php

namespace App\Monolog;

use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\RequestStack;

/**
 * RequestContextProcessor
 *
 * Monolog processor that enriches every log record with HTTP request context:
 * - request_id: taken from 'X-Request-Id' header if present, otherwise generated once per request
 * - route: Symfony route name (e.g., app_order_create)
 * - method, uri, client_ip: helpful for troubleshooting
 *
 * This provides consistent, searchable context across all logs (4xx/5xx included)
 * without changing individual logger calls in controllers/services.
 */
class RequestContextProcessor
{
    public function __construct(private RequestStack $requestStack)
    {
    }

    /**
     * Invoked by Monolog to modify the log record.
     *
     * Monolog v2 processors receive an associative array.
     * Monolog v3 processors receive an instance of Monolog\LogRecord (value object).
     * Support both to remain compatible across versions.
     *
     * @param mixed $record Monolog log record (array or Monolog\LogRecord)
     * @return mixed Enriched log record in the same shape as input
     */
    public function __invoke($record)
    {
        $request = $this->requestStack->getCurrentRequest();
        if (!$request instanceof Request) {
            return $record;
        }

        // Prefer client-provided request id; generate one if missing
        $requestId = $request->headers->get('X-Request-Id');
        if (!$requestId) {
            $requestId = $request->attributes->get('request_id');
            if (!$requestId) {
                try {
                    $requestId = bin2hex(random_bytes(8));
                } catch (\Exception) {
                    $requestId = uniqid('req_', true);
                }
                $request->attributes->set('request_id', $requestId);
            }
        }

        // Monolog v3: LogRecord object with public "extra" array
        if (class_exists('Monolog\\LogRecord') && $record instanceof \Monolog\LogRecord) {
            $record->extra['request_id'] = $requestId;
            $record->extra['route'] = $request->attributes->get('_route');
            $record->extra['method'] = $request->getMethod();
            $record->extra['uri'] = $request->getRequestUri();
            $record->extra['client_ip'] = $request->getClientIp();
            return $record;
        }

        // Monolog v2: associative array
        if (is_array($record)) {
            $record['extra']['request_id'] = $requestId;
            $record['extra']['route'] = $request->attributes->get('_route');
            $record['extra']['method'] = $request->getMethod();
            $record['extra']['uri'] = $request->getRequestUri();
            $record['extra']['client_ip'] = $request->getClientIp();
            return $record;
        }

        return $record;
    }
}


