<?php

namespace rock\template\helpers;


class BaseHelper implements SerializeInterface
{
    public static function getValue(&$value, $default = null)
    {
        return $value ? : $default;
    }

    public static function getValueIsset(&$value, $default = null)
    {
        return isset($value) ? $value : $default;
    }

    /**
     * Conversion to type
     *
     * @param mixed $value - value
     * @return mixed
     */
    public static function toType($value)
    {
        if (!is_string($value)) {
            return $value;
        }
        if ($value === 'null') {
            $value = null;
        } elseif (is_numeric($value)) {
            $value = Numeric::toNumeric($value);
        } elseif ($value === 'false') {
            $value = false;
        } elseif ($value === 'true') {
            $value = true;
        }

        return $value;
    }


    /**
     * Get hash var
     *
     * @param      $value
     * @param int  $serializator
     * @return string
     */
    public static function hash($value, $serializator = self::SERIALIZE_PHP)
    {
        if (is_array($value)) {
            $value = static::prepareHash($value, $serializator);
        }
        return md5($value);
    }

    protected static function prepareHash($value, $serializator = self::SERIALIZE_PHP)
    {
        if ($serializator === self::SERIALIZE_JSON) {
            return Json::encode($value);
        }

        return serialize($value);
    }
} 