<?php

namespace rock\template\filters;


use rock\template\ClassName;
use rock\template\Exception;
use rock\template\helpers\Numeric;
use rock\template\Template;

class NumericFilter
{
    use ClassName;

    /**
     * Check numeric is parity
     *
     * @param string   $value
     * @param array    $params
     *                 => then
     *                 => else
     * @param Template $template
     * @throws \rock\template\Exception
     * @return string
     */
    public static function isParity($value, array $params, Template $template)
    {
        if (empty($params) || count($params) < 1 || !isset($params['then'])) {
            throw new Exception(Exception::UNKNOWN_PARAM_FILTER, 0, ['name' => __METHOD__]);
        }
        $params['else'] = isset($params['else']) ? $params['else'] : null;
        $template = clone $template;
        $placeholders = [];
        $placeholders['output'] = $value;

        return Numeric::isParity($value)
            ? $template->replace($params['then'], $placeholders)
            : $template->replace($params['else'], $placeholders);
    }

    /**
     * Number convert to positive
     * @param int $value
     * @return int
     */
    public static function positive($value)
    {
        return Numeric::toPositive($value);
    }
} 