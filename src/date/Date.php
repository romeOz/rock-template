<?php

namespace rock\template\date;

use rock\template\date\locale\Locale;
use rock\template\ObjectTrait;

/** @noinspection PhpHierarchyChecksInspection */
/**
 * @method Locale getLocale()
 * @method string format(string $format)
 * @method DateTime modify(string $modify)
 * @method string serverDate()
 * @method string serverTime()
 * @method string serverDatetime()
 * @method string|null getFormat(string $alias)
 * @method string[] getFormats()
 * @method bool|\DateInterval diff($datetime2, bool $absolute = false)
 */
class Date implements DateTimeInterface
{
    use ObjectTrait;

    /** @var  string */
    public $locale = 'en';

    public $formats = [];

    /**
     * @param string|int       $time
     * @param string|\DateTimeZone $timezone
     * @return DateTime
     */
    public function set($time = 'now', $timezone = null)
    {
        $config = [
            'formats' => $this->formats,
            'locale' => $this->locale
        ];
        return new DateTime($time, $timezone, $config);
    }

    /**
     * @param       $methodName
     * @param array $params
     * @return DateTime
     */
    public function __call($methodName, $params = [])
    {
        $config = [
            'formats' => $this->formats,
            'locale' => $this->locale
        ];
        $datetime = new DateTime('now', null, $config);
        return call_user_func_array([$datetime, $methodName], $params);
    }

    /**
     * Set locale
     *
     * @param string    $locale (e.g. en)
     */
    public function setLocale($locale)
    {
        $this->locale = $locale;
    }

    /**
     * @param string $alias
     * @param string $format
     */
    public function addFormat($alias, $format)
    {
        $this->formats[$alias] = $format;
    }

    public static function addFormatOption($optionName, callable $callback)
    {
        DateTime::addFormatOption($optionName, $callback);
    }

    /**
     * Validate is date
     *
     * @param string|int $date
     * @return bool
     */
    public static function is($date)
    {
        return DateTime::is($date);
    }

    /**
     * Validation exist date in the format of timestamp
     *
     * @param string|int $timestamp
     * @return bool
     */
    public static function isTimestamp($timestamp)
    {
        return DateTime::isTimestamp($timestamp);
    }
}