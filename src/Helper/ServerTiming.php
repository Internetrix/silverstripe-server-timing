<?php

namespace Internetrix\ServerTiming\Helper;

/* Copyright 2020 Internetrix

This program is free software; you can redistribute it and/or
modify it under the terms of the GNU General Public License
version 2 as published by the Free Software Foundation.

This program is distributed in the hope that it will be useful,
but WITHOUT ANY WARRANTY; without even the implied warranty of
MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE. See the
GNU General Public License for more details. */

/**
 * Class ServerTiming
 * @package Internetrix\ServerTiming
 */
class ServerTiming
{
    /* @var self Static instance of this class to reused throughout application */
    private static $instance;

    /* @var String The timestamp of the start of the request, with microsecond precision */
    private static $requestStartTime = null;

    /* @var array Contains the start time for each metric */
    protected $startEvents;

    /* @var array Contains the end time for each metric */
    protected $endEvents;

    /**
     * On every new request to the application, a new single instance will be created
     * so that instance can be reused anywhere within application.
     * @return static
     */
    public static function inst()
    {
        return self::$instance
            ? self::$instance
            : self::$instance = new static();
    }

    /**
     * Starts the measuring of a metric
     * @param string $key - Name of Event Metric
     */
    public static function start($key)
    {

        self::inst()->startEvents[$key] = microtime(true);
    }

    /**
     * Stops the measuring of a metric
     * @param string $key Name of Event Metric
     */
    public static function end($key)
    {
        self::inst()->endEvents[$key] = microtime(true);

    }

    /**
     * @return array Returns each event metric with their duration
     */
    public function getEvents()
    {
        $defaultEndTime = microtime(true);
        $events = [];
        foreach ($this->startEvents as $key => $startTime) {
            $endTime = isset($this->endEvents[$key])
                ? $this->endEvents[$key]
                : $defaultEndTime;
            $events[$key] = ($endTime - $startTime) * 1000;
        }

        return $events;
    }

    /**
     * Add a new metric to calculate time since request started and the current time
     * @param string $key Name of Event metric
     */
    public static function addMetric($key)
    {
        $timing = self::inst();
        $timing->startEvents[$key] = self::getRequestStartTime();
        $timing->endEvents[$key] = microtime(true);
    }

    /**
     * Returns the timestamp of the start of the req
     * @return mixed|null
     */
    public static function getRequestStartTime()
    {
        $timing = self::inst();
        if ($timing::$requestStartTime) {
            return $timing::$requestStartTime;
        }

        return $timing::$requestStartTime = $_SERVER["REQUEST_TIME_FLOAT"] ?? microtime(true);
    }
}
