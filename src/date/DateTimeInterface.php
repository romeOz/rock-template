<?php

namespace rock\template\date;

/**
 * All this methods works through DateTime::__call method, mapped to format date with Dater::$formats[METHOD_NAME] format:
 * @method date() Get date in Date::$formats['date'] format, in client timezone
 * @method time() Get date in Date::$formats['time'] format, in client timezone
 * @method datetime() Get date in Date::$formats['datetime'] format, in client timezone
 * @method isoDate() Get date in Date::$formats['isoDate'] format, in client timezone
 * @method isoTime() Get date in Date::$formats['isoTime'] format, in client timezone
 * @method isoDatetime() Get date in Date::$formats['isoDatetime'] format, in client timezone
 */
interface DateTimeInterface
{
    const USER_DATE_FORMAT = 'date';
    const USER_TIME_FORMAT = 'time';
    const USER_DATETIME_FORMAT = 'datetime';
    const ISO_DATE_FORMAT = 'isoDate';
    const ISO_TIME_FORMAT = 'isoTime';
    const ISO_DATETIME_FORMAT = 'isoDatetime';
} 