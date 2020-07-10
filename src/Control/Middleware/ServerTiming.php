<?php

namespace Internetrix\LawCompliance\Middleware;

use SilverStripe\Control\Middleware\HTTPMiddleware;
use SilverStripe\Control\HTTPRequest;
use SilverStripe\Control\HTTPResponse;

/**
 * Class ServerTiming
 * @package Internetrix\CMSAdminIPRestriction
 */
class ServerTiming
{
    protected $startEvents;
    protected $endEvents;

    public function start($key) {
        $this->startEvents[$key] = microtime(true);
    }

    public function end($key) {
        $this->endEvents[$key] = microtime(true);
    }

    public function getEvents()
    {
        $events = [];
        foreach ($this->startEvents as $key => $startTime) {
            if (isset($this->endEvents[$key])) {
                $endTime = $this->endEvents[$key];
                $events[$key] = ($endTime - $startTime) * 1000;
            }
        }

        return $events;
    }

    public function setDuration($key)
    {
        $this->startEvents[$key] = $_SERVER["REQUEST_TIME_FLOAT"] ?? microtime(true);
        $this->endEvents[$key] = microtime(true);
    }
}
