<?php

namespace rock\template\helpers;


class BaseString 
{
    /**
     * Replace
     *
     * @param string $string       - string
     * @param array  $dataReplace - array replace
     * @return string
     */
    public static function replace($string, array $dataReplace = [])
    {
        if (is_array($string) || empty($dataReplace)) {
            return $string;
        }
        // build a replacement array with braces around the context keys
        $replace = [];
        foreach ($dataReplace as $key => $val) {
            $replace['{' . $key . '}'] = $val;
        }
        $string = strtr($string, $replace);
        // interpolate replacement values into the message and return
        return $string;
    }


    /**
     * Encodes special characters into HTML entities.
     *
     * @param string  $content      the content to be encoded
     * @param boolean $doubleEncode whether to encode HTML entities in `$content`. If false,
     *                              HTML entities in `$content` will not be further encoded.
     * @param string $encoding The charset to use, defaults to charset currently used by application.
     * @return string the encoded content
     * @see decode()
     * @see http://www.php.net/manual/en/function.htmlspecialchars.php
     */
    public static function encode($content, $doubleEncode = true, $encoding = 'UTF-8')
    {
        return htmlspecialchars($content, ENT_QUOTES | ENT_SUBSTITUTE, $encoding, $doubleEncode);
    }

    /**
     * Decodes special HTML entities back to the corresponding characters.
     * @param string $content the content to be decoded
     * @return string the decoded content
     * @see encode()
     * @see http://www.php.net/manual/en/function.htmlspecialchars-decode.php
     */
    public static function decode($content)
    {
        return htmlspecialchars_decode($content, ENT_QUOTES);
    }

    /**
     * Truncates a string to the number of characters specified.
     *
     * @param string  $string The string to truncate.
     * @param integer $length How many characters from original string to include into truncated string.
     * @param string  $suffix String to append to the end of truncated string.
     * @param string $encoding The charset to use, defaults to charset currently used by application.
     * @return string
     */
    public static function truncate($string, $length = 4, $suffix = '...', $encoding = 'UTF-8')
    {
        $length = (int)$length;
        if (empty($string) || $length === 0) {
            return $string;
        }

        if (mb_strlen($string, $encoding) > $length) {
            return trim(mb_substr($string, 0, $length, $encoding)) . $suffix;
        } else {
            return $string;
        }
    }

    /**
     * Truncates a string to the number of words specified.
     *
     * @param string  $string The string to truncate.
     * @param integer $length How many words from original string to include into truncated string.
     * @param string  $suffix String to append to the end of truncated string.
     * @param string $encoding The charset to use, defaults to charset currently used by application.
     * @return string
     */
    public static function truncateWords($string, $length = 100, $suffix = '...', $encoding = 'UTF-8')
    {
        if (empty($string) || $length === 0 ||
            mb_strlen($string, $encoding) <= $length) {
            return $string;
        }
        $string = mb_substr($string, 0, $length, $encoding);
        if (mb_substr($string, -1, 1, $encoding) != ' ') {
            $string = mb_substr($string, 0, mb_strrpos($string, ' ', 0, $encoding), $encoding);
        }

        if (!$string = trim($string)) {
            return '';
        }
        return $string . $suffix;
    }

    /**
     * String to uppercase
     *
     * @param string $string - string
     * @param string $encoding The charset to use, defaults to charset currently used by application.
     * @return string
     */
    public static function upper($string, $encoding = 'UTF-8')
    {
        if (empty($string)) {
            return $string;
        }
        $string = mb_strtoupper($string, $encoding);

        return $string;
    }

    /**
     * String to lowercase
     *
     * @param string $string - string
     * @param string $encoding The charset to use, defaults to charset currently used by application.
     * @return string
     */
    public static function lower($string, $encoding = 'UTF-8')
    {
        if (empty($string)) {
            return $string;
        }
        $string = mb_strtolower($string, $encoding);

        return $string;
    }

    /**
     * Upper first char
     *
     * @param string $string - string
     * @param string $encoding The charset to use, defaults to charset currently used by application.
     * @return string
     */
    public static function upperFirst($string, $encoding = 'UTF-8')
    {
        if (empty($string)) {
            return $string;
        }
        $fc = mb_strtoupper(mb_substr($string, 0, 1, $encoding), $encoding);

        return $fc . mb_substr($string, 1, mb_strlen($string, $encoding), $encoding);
    }

    /**
     * Trim spaces
     *
     * @param string $string - string
     * @return string
     */
    public static function trimSpaces($string)
    {
        return static::trimPattern($string, '/\s+/i');
    }

    /**
     * Trim by pattern
     *
     * @param string $string
     * @param string $pattern
     * @return string
     */
    public static function trimPattern($string, $pattern)
    {
        return preg_replace($pattern, '', $string);
    }

    /**
     * Concat begin
     * @param string     $value
     * @param string     $concat
     * @param null $default
     * @return null|string
     *
     * ```php
     * String::lconcat('world', 'hello '); // hello world
     * String::lconcat(null, ' hello '); // null
     * ```
     */
    public static function lconcat(&$value, $concat, $default = null)
    {
        return $value ? $concat . $value : $default;
    }


    /**
     * Concat end
     * @param string     $value
     * @param string     $concat
     * @param null $default
     * @return null|string
     *
     * ```php
     * String::rconcat('hello', ' world'); // hello world
     * String::rconcat(null, ' world'); // null
     * ```
     */
    public static function rconcat(&$value, $concat, $default = null)
    {
        return $value ? $value . $concat : $default;
    }
} 