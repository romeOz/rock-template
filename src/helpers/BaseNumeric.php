<?php
namespace rock\template\helpers;


class BaseNumeric
{
    /**
     * Validation numeric is parity
     *
     * @param int $value - numeric
     * @return boolean
     */
    public static function parity($value)
    {
        return $value & 1 ? false : true;
    }


    /**
     * Get Positive
     *
     * @param int $value - number
     * @return int
     */
    public static function positive($value)
    {
        return ($value < 0) ? 0 : $value;
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
        $buff = 1 + $value;
        if (is_int($buff)) {
            return (int)$value;
        } elseif (is_float($buff)) {
            return (float)$value;
        }

        return 0;
    }
}