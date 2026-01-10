<?php

use App\Kernel;
use Symfony\Component\HttpFoundation\Request;

require_once dirname(__DIR__).'/vendor/autoload_runtime.php';

return function (array $context) {
    $request = Request::createFromGlobals();
    $trustedHeaders = Request::HEADER_X_FORWARDED_FOR
        | Request::HEADER_X_FORWARDED_HOST
        | Request::HEADER_X_FORWARDED_PROTO
        | Request::HEADER_X_FORWARDED_PORT
        | Request::HEADER_X_FORWARDED_PREFIX;

    // Trust all proxies for Railway (Railway uses reverse proxy)
    // In production, Railway uses a reverse proxy, so we need to trust all proxies
    Request::setTrustedProxies(
        ['127.0.0.1', '::1', '10.0.0.0/8', '172.16.0.0/12', '192.168.0.0/16'],
        $trustedHeaders
    );

    return new Kernel($context['APP_ENV'], (bool) $context['APP_DEBUG']);
};
