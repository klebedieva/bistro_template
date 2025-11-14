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

    Request::setTrustedProxies(
        ['127.0.0.1', $request->server->get('REMOTE_ADDR')],
        $trustedHeaders
    );

    return new Kernel($context['APP_ENV'], (bool) $context['APP_DEBUG']);
};
