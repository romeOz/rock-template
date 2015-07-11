<?php
namespace rock\template\filters;

use rock\base\ClassName;
use rock\template\TemplateException;
use rock\template\Template;

/**
 * Filter "ConditionFilter"
 *
 * @package rock\template
 */
class ConditionFilter
{
    use ClassName;

    public static $conditionNames = [
        'is' => ['isequalto', 'isequal', 'equalto', 'equals', 'is', 'eq'],
        'isnot' => ['notequalto', 'notequals', 'isnt', 'isnot', 'neq', 'ne'],
        'gte' => ['greaterthanorequalto', 'equalorgreaterthen', 'ge', 'eg', 'isgte', 'gte'],
        'gt' => ['isgreaterthan', 'greaterthan', 'isgt', 'gt'],
        'lte' => ['equaltoorlessthan', 'lessthanorequalto', 'el', 'le', 'islte', 'lte'],
        'lt' => ['islowerthan', 'islessthan', 'lowerthan', 'lessthan', 'islt', 'lt'],
        'inarray' => ['inarray', 'in_array', 'in_arr'],
    ];

    /**
     * `isset` condition.
     *
     * @param string $value value
     * @param array $params params:
     *
     * - then:               returns when isset value
     * - addPlaceholders:  set names of the placeholders, which adding the result.
     *
     * @param Template $template
     * @return string
     */
    public static function _isset($value, array $params, Template $template)
    {
        if (!isset($value)) {
            return '';
        }
        //$template = clone $template;
        $placeholders = array_merge(
            !empty($params['addPlaceholders']) ? $template->findPlaceholders($params['addPlaceholders']) : [],
            ['output' => $value]
        );
        return $template->replaceByPrefix($params['then'], $placeholders);
    }

    /**
     * `!isset` condition.
     *
     * @param string $value value
     * @param array $params params
     *
     * - then: returns when `!isset` value.
     *
     * @param Template $template
     * @return string
     */
    public static function notIsset($value = null, array $params, Template $template)
    {
        if (isset($value)) {
            return $value;
        }
        //$template = clone $template;
        $placeholders = array_merge(
            !empty($params['addPlaceholders']) ? $template->findPlaceholders($params['addPlaceholders']) : [],
            ['output' => $value]
        );

        return $template->replaceByPrefix($params['then'], $placeholders);
    }

    /**
     * Condition is not empty.
     *
     * @param string $value value
     * @param array $params params:
     *
     * - then:               returns when value is not empty
     * - addPlaceholders:  set names of the placeholders, which adding the result.
     *
     * @param Template $template
     * @return string
     */
    public static function notEmpty($value, array $params, Template $template)
    {
        if (empty($value)) {
            return '';
        }
        //$template = clone $template;
        $placeholders = array_merge(
            !empty($params['addPlaceholders']) ? $template->findPlaceholders($params['addPlaceholders']) : [],
            ['output' => $value]
        );
        return $template->replaceByPrefix($params['then'], $placeholders);
    }

    /**
     * Condition is empty.
     *
     * @param string $value value
     * @param array $params params
     *
     * - then: returns when value is empty.
     *
     * @param Template $template
     * @return string
     */
    public static function _empty($value = null, array $params, Template $template)
    {
        if (!empty($value)) {
            return $value;
        }
        //$template = clone $template;
        $placeholders = array_merge(
            !empty($params['addPlaceholders']) ? $template->findPlaceholders($params['addPlaceholders']) : [],
            ['output' => $value]
        );

        return $template->replaceByPrefix($params['then'], $placeholders);
    }


    /**
     * Inline condition is not empty.
     *
     * @param string $value value
     * @param array $params params:
     *
     * - then:               returns when value is not empty
     * - addPlaceholders:  set names of the placeholders, which adding the result.
     *
     * @param Template $template
     * @return string
     */
    public static function inotEmpty($value, array $params, Template $template)
    {
        if (empty($value)) {
            return '';
        }
        $template = clone $template;
        $placeholders = array_merge(
            !empty($params['addPlaceholders']) ? $template->findPlaceholders($params['addPlaceholders']) : [],
            ['output' => $value]
        );

        return $template->replace($params['then'], $placeholders);
    }

    /**
     * Inline condition is empty.
     *
     * @param string $value value
     * @param array $params params
     *
     * - then: returns when value is empty.
     *
     * @param Template $template
     * @return string
     */
    public static function iempty($value = null, array $params, Template $template)
    {
        if (!empty($value)) {
            return $value;
        }
        $template = clone $template;
        $placeholders = array_merge(
            !empty($params['addPlaceholders']) ? $template->findPlaceholders($params['addPlaceholders']) : [],
            ['output' => $value]
        );

        return $template->replace($params['then'], $placeholders);
    }

    /**
     * if ... then ... else
     *
     * @param string $value value
     * @param array $params params
     * @param Template $template
     * @throws TemplateException
     * @return string
     */
    public static function _if($value, array $params, Template $template)
    {
        // is two operators (e.g. if ... then)
        if (empty($params) || count($params) < 2 || !isset($params['then'])) {
            throw new TemplateException(TemplateException::UNKNOWN_PARAM_FILTER, ['name' => __METHOD__]);
        }
        $params['else'] = isset($params['else']) ? $params['else'] : null;
        $template = clone $template;
        $placeholders = [];
        $placeholders['output'] = $value;

        // equals
        if ($condition = self::_getCondition($params, static::$conditionNames['is'])) {
            $result = $value == $template->replace($params[$condition])
                ? $template->replace($params['then'], $placeholders)
                : $template->replace($params['else'], $placeholders);
            // notequals
        } elseif ($condition = self::_getCondition($params, static::$conditionNames['isnot'])) {
            $result = $value <> $template->replace($params[$condition])
                ? $template->replace($params['then'], $placeholders)
                : $result = $template->replace($params['else'], $placeholders);
            // equals or greater
        } elseif ($condition = self::_getCondition($params, static::$conditionNames['gte'])) {
            $result = $value >= $template->replace($params[$condition])
                ? $template->replace($params['then'], $placeholders)
                : $template->replace($params['else'], $placeholders);
            // greater
        } elseif ($condition = self::_getCondition($params, static::$conditionNames['gt'])) {
            $result = $value > $template->replace($params[$condition])
                ? $template->replace($params['then'], $placeholders)
                : $template->replace($params['else'], $placeholders);
            // less or equals
        } elseif ($condition = self::_getCondition($params, static::$conditionNames['lte'])) {
            $result = $value <= $template->replace($params[$condition])
                ? $template->replace($params['then'], $placeholders)
                : $template->replace($params['else'], $placeholders);
            // less
        } elseif ($condition = self::_getCondition($params, static::$conditionNames['lt'])) {
            $result = $value < $template->replace($params[$condition])
                ? $template->replace($params['then'], $placeholders)
                : $template->replace($params['else'], $placeholders);
            // in array
        } elseif ($condition = self::_getCondition($params, static::$conditionNames['inarray'])) {
            $actual = trim($template->replace($params[$condition]));
            $actual = is_string($actual) ? explode(',', $actual) : $actual;
            $result = in_array($value, $actual)
                ? $template->replace($params['then'], $placeholders) : $template->replace(
                    $params['else'],
                    $placeholders);
        } else {
            throw new TemplateException(TemplateException::UNKNOWN_PARAM_FILTER, ['name' => json_encode($params)]);
        }

        return $result;
    }

    /**
     * Get name of condition.
     *
     * @param array $value value for equal
     * @param array $conditions conditions
     * @return string|boolean
     */
    private static function _getCondition($value, array $conditions)
    {
        if (empty($value) || empty($conditions)) {
            return false;
        }

        return current(array_intersect(array_keys($value), $conditions));
    }
}