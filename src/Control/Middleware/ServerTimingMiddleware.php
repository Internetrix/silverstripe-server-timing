<?php

namespace Internetrix\LawCompliance\Middleware;

use SilverStripe\Control\Middleware\HTTPMiddleware;
use SilverStripe\Control\HTTPRequest;
use SilverStripe\Control\HTTPResponse;
use SilverStripe\Dev\Debug;

/**
 * Class ServerTimingMiddleware
 * @package Internetrix\CMSAdminIPRestriction
 */
class ServerTimingMiddleware implements HTTPMiddleware
{
    protected $timing;
    protected $start;


    /**
     * @param HTTPRequest $request
     * @param callable $delegate
     * @return HTTPResponse
     */
    public function process(HTTPRequest $request, callable $delegate)
    {

        $this->timing = new ServerTiming();

        $this->timing->start('bootstrap');
        $this->timing->end('bootstrap');
//

        $this->timing->start('app');

//        // If you want normal behaviour to occur, make sure you call $delegate($request)
        $response = $delegate($request);

        $this->timing->end('app');

        $this->timing->setDuration('Total');

        $response->addHeader('Server-Timing', $this->generateHeaders());


        return $response;
    }

    public function getRequestStartTime()
    {
        return $_SERVER["REQUEST_TIME_FLOAT"] ?? microtime(true);
    }

    public function generateHeaders()
    {
        $metrics = [];

        if (count($this->timing->getEvents())) {
            foreach($this->timing->getEvents() as $name => $timeTaken) {
                $output = sprintf('%s;desc="%s";dur=%f', $name, $name, $timeTaken);

                $metrics[] = $output;
            }
        }

        return implode($metrics, ', ');
    }
}
