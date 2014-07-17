<?php
namespace rock\template\filters;

use rock\template\ClassName;
use rock\template\helpers\Helper;
use rock\template\helpers\String;

/**
 * Filter "StringFilter"
 *
 * @package rock\template
 */
abstract class StringFilter
{
    use ClassName;

    /**
     * Strip substring
     *
     * @param string $value
     * @param array  $params - params
     *                       => is       - substring
     *                       => limit    - limit
     * @return string
     */
    public static function stripString($value, array $params)
    {
        if (empty($value) || empty($params['is'])) {
            return $value;
        }
        $params['limit'] = Helper::getValue($params['is'], -1);

        return preg_replace(
            '/' . preg_quote($params['is'], '/') . '/iu',
            "",
            $value,
            (int)$params['limit']
        );
    }

    /**
     * Strip tags
     *
     * @param string $value
     * @param array  $params        - params
     *                              => is - allowed tags
     * @return string
     */
    public static function stripTags($value, array $params)
    {
        return strip_tags(
            $value,
            Helper::getValue($params['is'])
        );
    }

    /**
     * Truncates a string to the number of characters specified.
     *
     * @param string $value
     * @param array  $params           - params
     *                                 => len - count of output characters (minus 3, because point)
     * @return string
     */
    public static function truncate($value, array $params)
    {
        if (empty($params['length']) || !is_numeric($params['length'])) {
            $params['length'] = 4;
        }

        return String::truncate($value, (int)$params['length']);
    }

    /**
     * Truncates a string to the number of words specified.
     *
     * @param string $value
     * @param array  $params            - params
     *                                  => len - count of output characters
     * @return string
     */
    public static function truncateWords($value, array $params)
    {
        if (empty($params['length']) || !is_numeric($params['length'])) {
            $params['length'] = 100;
        }

        return String::truncateWords($value, (int)$params['length']);
    }

    /**
     * String to uppercase
     *
     * @param string $value
     * @return string
     */
    public static function upper($value)
    {
        return String::upper($value);
    }

    /**
     * String to lowercase
     *
     * @param string $value
     * @return string
     */
    public static function lower($value)
    {
        return String::lower($value);
    }

    /**
     * Upper first char
     *
     * @param string $value
     * @return string
     */
    public static function upperFirst($value)
    {
        return String::upperFirst($value);
    }

    /**
     * Encodes special characters into HTML entities.
     *
     * @param string $value
     * @return string
     */
    public static function encode($value)
    {
        return String::encode($value);
    }

    /**
     * Decodes special HTML entities back to the corresponding characters.
     *
     * @param string $value
     * @return string
     */
    public static function decode($value)
    {
        return String::decode($value);
    }
}