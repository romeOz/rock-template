<?php

namespace rock\template\helpers;


class BaseArrayHelper 
{
    /**
     * Retrieves the value of an array element or object property with the given key or property name.
     * If the key does not exist in the array or object, the default value will be returned instead.
     *
     * The key may be specified in a dot format to retrieve the value of a sub-array or the property
     * of an embedded object. In particular, if the key is `x.y.z`, then the returned value would
     * be `$array['x']['y']['z']` or `$array->x->y->z` (if `$array` is an object). If `$array['x']`
     * or `$array->x` is neither an array nor an object, the default value will be returned.
     * Note that if the array already has an element `x.y.z`, then its value will be returned
     * instead of going through the sub-arrays.
     *
     * Below are some usage examples,
     *
     * ```php
     * // working with array
     * $username = ArrayHelper::getValue($_POST, 'username');
     * // working with object
     * $username = ArrayHelper::getValue($user, 'username');
     * // working with anonymous function
     * $fullName = ArrayHelper::getValue($user, function($user, $defaultValue) {
     *     return $user->firstName . ' ' . $user->lastName;
     * });
     * // using dot format to retrieve the property of embedded object
     * $street = ArrayHelper::get($users, 'address.street');
     * ```
     *
     * @param array|object          $array   array or object to extract value from
     * @param string|array|\Closure $key     key name of the array element, or property name of the object,
     *                                       or an anonymous function returning the value. The anonymous function signature should be:
     *                                       `function($array, $defaultValue)`.
     * @param mixed                 $default the default value to be returned if the specified key does not exist
     * @return mixed the value of the element if found, default value otherwise
     */
    public static function getValue($array, $key, $default = null)
    {
        if (empty($array)) {
            return $default;
        }
        if ($key instanceof \Closure) {
            return $key($array, $default);
        }
        if (is_array($array) && is_array($key)) {
            return static::keyAsArray($array, $key, $default);
        }
        if (is_array($array) && array_key_exists($key, $array)) {
            return $array[$key];
        }
        if (($pos = strrpos($key, '.')) !== false) {
            $array = static::getValue($array, substr($key, 0, $pos), $default);
            $key = substr($key, $pos + 1);
        }
        if (is_object($array)) {
            return $array->$key;
        } elseif (is_array($array)) {
            return array_key_exists($key, $array) ? $array[$key] : $default;
        } else {
            return $default;
        }
    }

    /**
     * @param array $keys  - keys
     * @param array $array - current array
     * @param mixed $default
     * @return mixed
     */
    protected static function keyAsArray(array $array, array $keys, $default = null)
    {
        if (!$keys) {
            return $array;
        }
        $current = $array;
        foreach ($keys as $key) {
            if (!is_array($current) || empty($current[$key])) {
                return $default;
            }
            $current = $current[$key];
        }

        return $current;
    }

    public static function intersectByKeys(array $array, array $keys)
    {
        return array_intersect_key($array, array_flip($keys));
    }

    public static function diffByKeys(array $array, array $keys)
    {
        return array_diff_key($array, array_flip($keys));
    }

    public static function prepareArray(array $array = [], array $only = [], array $exclude = [])
    {
        if (empty($array)) {
            return [];
        }
        if (!empty($only)) {
            $array = static::intersectByKeys($array, $only);
        }
        if (!empty($exclude)) {
            $array = static::diffByKeys($array, $exclude);
        }

        return $array;
    }


    /**
     * Map recursive
     *
     * @param array    $array
     * @param callable $callback
     * @param bool     $recursive
     * @param int      $depth
     * @param int      $count
     * @return array
     */
    public static function map(array $array, \Closure $callback, $recursive = false, $depth = null, &$count = 0)
    {
        foreach ($array as $key => $value) {
            if (isset($depth) && $count === $depth) {
                return $array;
            }
            ++$count;
            if (is_array($array[$key]) && $recursive === true) {
                $array[$key] = static::map($array[$key], $callback, $recursive);
            } else {
                $array[$key] = call_user_func($callback, $array[$key], $key);
            }
        }

        return $array;
    }
}