<?php

/**
 * Representation of date interval. A date interval stores either a fixed amount of
 * time (in years, months, days, hours etc) or a relative time string in the format
 * that DateTime's constructor supports.
 * @link http://php.net/manual/en/class.dateinterval.php
 */
class DateInterval {
    /**
     * Number of years
     * @var int
     */
    public $y;

    /**
     * Number of months
     * @var int
     */
    public $m;

    /**
     * Number of weeks
     * @var int
     */
    public $w;

    /**
     * Number of days
     * @var int
     */
    public $d;

    /**
     * Number of hours
     * @var int
     */
    public $h;

    /**
     * Number of minutes
     * @var int
     */
    public $i;

    /**
     * Number of seconds
     * @var int
     */
    public $s;

    /**
     * Is 1 if the interval is inverted and 0 otherwise
     * @var int
     */
    public $invert;

    /**
     * Total number of days the interval spans. If this is unknown, days will be FALSE.
     * @var mixed
     */
    public $days;


    /**
     * @param string $interval_spec
     * @link http://php.net/manual/en/dateinterval.construct.php
     */
    public function __construct ($interval_spec) {}

    /**
     * Formats the interval
     * @param $format
     * @return string
     * @link http://php.net/manual/en/dateinterval.format.php
     */
    public function format ($format) {}

    /**
     * Sets up a DateInterval from the relative parts of the string
     * @param string $time
     * @return DateInterval
     * @link http://php.net/manual/en/dateinterval.createfromdatestring.php
     */
    public static function createFromDateString ($time) {}
}