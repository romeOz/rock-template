<?php
namespace rock\template\helpers;


class BaseNumeric
{
    /**
     * Is parity
     *
     * @param int $value - numeric
     * @return boolean
     */
    public static function isParity($value)
    {
        return $value & 1 ? false : true;
    }

    /**
     * Number convert to positive
     *
     * @param int $value - number
     * @return int
     */
    public static function toPositive($value)
    {
        return $value < 0 ? 0 : $value;
    }

    /**
     * String conversion to numbers
     *
     * @param string $value - value
     * @return mixed
     */
    public static function toNumeric($value)
    {
        if (!is_numeric($value)) {
            return 0;
        }
        $is = 1 + $value;
        if (is_int($is)) {
            return (int)$value;
        }
        return (float)$value;
    }
}