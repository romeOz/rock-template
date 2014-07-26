<?php
namespace rock\template\filters;

use rock\template\ClassName;
use rock\template\date\DateTime;
use rock\template\helpers\ArrayHelper;
use rock\template\helpers\Helper;
use rock\template\helpers\Json;
use rock\template\helpers\Serialize;
use rock\template\Template;
use rock\template\url\Url;

class BaseFilter
{
    use ClassName;

    /**
     * Unserialize
     *
     * @param string $value             - serialized array
     * @param array  $params            - params
     *                                  => key
     *                                  => separator
     * @return string
     */
    public static function unserialize($value, array $params)
    {
        if (empty($value)) {
            return null;
        }

        if (!empty($params['key'])) {
           return ArrayHelper::getValue(
                Serialize::unserialize($value, false),
                explode(Helper::getValue($params['separator'], '.'), $params['key'])
            );
        }

        return Serialize::unserialize($value, false);
    }

    /**
     * Replace variables template (chunk, snippet...)
     *
     * @param string                  $content - content
     * @param array                   $placeholders
     * @param \rock\template\Template $template
     * @return string
     */
    public static function replaceTpl($content, array $placeholders = null, Template $template)
    {
        $template = clone $template;
        $template->removeAllPlaceholders();
        return $template->replace($content, $placeholders);
    }


    /**
     * Modify date
     *
     * @param string $date   - date
     * @param array  $params - params
     *                       => format   - date format
     * @return string|null
     */
    public static function modifyDate($date, array $params = [])
    {
        if (empty($date)) {
            return null;
        }
        return (new DateTime($date, Helper::getValue($params['timezone']), Helper::getValue($params['config'], [])))
            ->format(Helper::getValue($params['format']));
    }


    /**
     * Modify url
     *
     * @param string $url
     * @param array  $params    - params
     *                          => args        - add args-url
     *                          => scheme      - scheme
     *                          => beginPath     - add string to begin
     *                          => endPath       - add string to end
     *                          => const
     *                          => urlManager - instance [[\rock\template\url\Url]]
     * @return string
     */
    public static function modifyUrl($url, array $params = [])
    {
        if (empty($url)) {
            return '#';
        }
        $urlManager = isset($params['urlManager']) ? $params['urlManager'] : new Url();
        $urlManager->set($url);
        if (isset($params['removeAllArgs'])) {
            $urlManager->removeAllArgs();
        }
        if (isset($params['removeArgs'])) {
            $urlManager->removeArgs($params['removeArgs']);
        }
        if (isset($params['removeAnchor'])) {
            $urlManager->removeAnchor();
        }
        if (isset($params['beginPath'])) {
            $urlManager->addBeginPath($params['beginPath']);
        }
        if (isset($params['endPath'])) {
            $urlManager->addEndPath($params['endPath']);
        }
        if (isset($params['args'])) {
            $urlManager->setArgs($params['args']);
        }
        if (isset($params['addArgs'])) {
            $urlManager->addArgs($params['addArgs']);
        }
        if (isset($params['anchor'])) {
            $urlManager->addAnchor($params['anchor']);
        }
        return $urlManager->get(Helper::getValue($params['const'], 0), (bool)Helper::getValue($params['selfHost']));
    }

    /**
     * Converting array to json-object
     *
     * @param array $array - current array
     * @return string
     */
    public static function arrayToJson($array)
    {
        if (empty($array)) {
            return null;
        }
        return Json::encode($array) ? : null;
    }
}