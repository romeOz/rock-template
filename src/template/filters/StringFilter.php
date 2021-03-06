<?php
namespace rock\template\filters;

use rock\base\ClassName;
use rock\helpers\Helper;
use rock\helpers\Instance;
use rock\helpers\StringHelper;
use rock\template\TemplateException;
use rock\template\Template;

/**
 * Filter "StringFilter"
 *
 * @package rock\template
 */
class StringFilter
{
    use ClassName;

    /**
     * Size string or array.
     *
     * @param array|string $value
     * @return int
     */
    public static function size($value)
    {
        if (is_string($value)) {
            return mb_strlen($value, 'UTF-8');
        }

        return count($value);
    }

    /**
     * Trim by pattern.
     *
     * @param string $value
     * @param array $params :
     *
     * - pattern: regexp pattern
     * - limit
     *
     * @return string
     */
    public static function trimPattern($value, array $params)
    {
        if (empty($value) || empty($params['pattern'])) {
            return $value;
        }
        $params['limit'] = Helper::getValue($params['limit'], -1);
        return StringHelper::trimPattern($value, $params['pattern'], $params['limit']);
    }

    /**
     * Check contains word or char in string
     *
     * @param string $value
     * @param array $params :
     *
     * - is
     * - then
     * - else
     *
     * @param Template $template
     * @throws \rock\template\TemplateException
     * @return string
     */
    public static function contains($value, array $params, Template $template)
    {
        if (empty($params) || count($params) < 2 || !isset($params['then'])) {
            throw new TemplateException(TemplateException::UNKNOWN_PARAM_FILTER, ['name' => __METHOD__]);
        }
        $params['else'] = isset($params['else']) ? $params['else'] : null;
        $template = clone $template;
        $placeholders = [];
        $placeholders['output'] = $value;

        return StringHelper::contains($value, $template->replace($params['is']))
            ? $template->replace($params['then'], $placeholders)
            : $template->replace($params['else'], $placeholders);
    }

    /**
     * Truncates a string to the number of characters specified.
     *
     * @param string $value
     * @param array $params params:
     *
     * - length: count of output characters (minus 3, because point).
     *
     * @return string
     */
    public static function truncate($value, array $params)
    {
        if (empty($params['length']) || !is_numeric($params['length'])) {
            $params['length'] = 4;
        }

        return StringHelper::truncate($value, (int)$params['length']);
    }

    /**
     * Truncates a string to the number of words specified.
     *
     * @param string $value
     * @param array $params params:
     *
     * - length: count of output characters.
     *
     * @return string
     */
    public static function truncateWords($value, array $params)
    {
        if (empty($params['length']) || !is_numeric($params['length'])) {
            $params['length'] = 100;
        }

        return StringHelper::truncateWords($value, (int)$params['length']);
    }

    /**
     * String to uppercase.
     *
     * @param string $value
     * @return string
     */
    public static function upper($value)
    {
        return StringHelper::upper($value);
    }

    /**
     * String to lowercase.
     *
     * @param string $value
     * @return string
     */
    public static function lower($value)
    {
        return StringHelper::lower($value);
    }

    /**
     * Upper first char.
     *
     * @param string $value
     * @return string
     */
    public static function upperFirst($value)
    {
        return StringHelper::upperFirst($value);
    }

    /**
     * Encodes special characters into HTML entities.
     *
     * @param string $value
     * @return string
     */
    public static function encode($value)
    {
        return StringHelper::encode($value);
    }

    /**
     * Decodes special HTML entities back to the corresponding characters.
     *
     * @param string $value
     * @return string
     */
    public static function decode($value)
    {
        return StringHelper::decode($value);
    }

    public static function markdown($value, array $params = [])
    {
        if (empty($value)) {
            return $value;
        }
        /** @var \rock\markdown\Markdown $markdown */
        $markdown = Instance::ensure(isset($params['markdown']) ? $params['markdown'] : 'markdown', '\rock\markdown\Markdown');
        $markdown->dummy = Helper::getValue($params['dummy'], 0);
        if (!empty($params['enableNewlines'])) {
            $markdown->enableNewlines = true;
        }
        $markdown->denyTags = Helper::getValue($params['denyTags'], []);
        return $markdown->parse($value);
    }

    public static function paragraph($value, array $params = [])
    {
        if (empty($value)) {
            return $value;
        }
        /** @var \rock\markdown\Markdown $markdown */
        $markdown = Instance::ensure(isset($params['markdown']) ? $params['markdown'] : 'markdown', '\rock\markdown\Markdown');
        $markdown->dummy = Helper::getValue($params['enabledDummy'], false);
        if (!empty($params['enableNewlines'])) {
            $markdown->enableNewlines = true;
        }
        $markdown->denyTags = Helper::getValue($params['denyTags'], []);
        return $markdown->parseParagraph($value);
    }
}