<?php

namespace Internetrix\ServerTiming\Control\Middleware;

/* Copyright 2020 Internetrix

This program is free software; you can redistribute it and/or
modify it under the terms of the GNU General Public License
version 2 as published by the Free Software Foundation.

This program is distributed in the hope that it will be useful,
but WITHOUT ANY WARRANTY; without even the implied warranty of
MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE. See the
GNU General Public License for more details. */

use SilverStripe\Control\Middleware\HTTPMiddleware;
use SilverStripe\Control\HTTPRequest;
use SilverStripe\Control\HTTPResponse;
use Internetrix\ServerTiming\Helper\ServerTiming;
/**
 * Class ServerTimingMiddleware
 * @package Internetrix\ServerTiming
 */
class ServerTimingMiddleware implements HTTPMiddleware
{
    /* @var ServerTiming Static instance used to record metric events */
    protected $timing;

    /**
     * ServerTimingMiddleware constructor.
     * @param ServerTiming $timing
     */
    public function __construct(ServerTiming $timing)
    {
        $this->timing = $timing;
    }

    /**
     * @param HTTPRequest $request
     * @param callable $delegate
     * @return HTTPResponse
     */
    public function process(HTTPRequest $request, callable $delegate)
    {
        // Time it took for this middleware to be called
        $this->timing->addMetric('Bootstrap');

        // Time to get finish response from within application
        $this->timing::start('Application');
        $response = $delegate($request);
        $this->timing::end('Application');

        // Total time it took to send out a response back to the client-side
        $this->timing->addMetric('Total');

        // Add Server-Timing API
        $response->addHeader('Server-Timing', $this->generateHeaders());

        return $response;
    }

    /**
     * Generates the ServerTiming API Headers to add to the request response
     * @return string
     */
    public function generateHeaders()
    {
        $metrics = [];

        if (count($this->timing->getEvents())) {
            foreach($this->timing->getEvents() as $name => $timeTaken) {
                $metrics[] = sprintf('%s;desc="%s";dur=%f', $name, $name, $timeTaken);
            }
        }

        return implode($metrics, ', ');
    }
}
