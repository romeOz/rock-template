<?php

namespace rockunit\template\filters;


use rock\template\ClassName;
use rock\template\date\Date;
use rock\template\url\Url;

class TestFilters
{
    use className;

    public static function foo($value, array $params)
    {
        list($urlManager, $date) = $params['_handlers'];

        if (!$urlManager instanceof Url || !$date instanceof Date) {
            return 'fail';
        }
        return $value;
    }
} 