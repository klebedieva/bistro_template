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

    // Trust proxies for platforms behind reverse proxy (Render, Railway, etc.)
    $proxies = array_filter([
        '127.0.0.1',
        '::1',
        $request->server->get('REMOTE_ADDR')
    ]);
    
    Request::setTrustedProxies(
        array_values($proxies),
        $trustedHeaders
    );

    return new Kernel($context['APP_ENV'], (bool) $context['APP_DEBUG']);
};
