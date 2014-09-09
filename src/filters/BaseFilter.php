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
        return (new DateTime($date, null, Helper::getValue($params['config'], [])))
            ->convertTimezone(Helper::getValue($params['timezone']))
            ->format(Helper::getValue($params['format']));
    }

    /**
     * Modify url
     *
     * @param string $url
     * @param array  $params - params
     *                  => args        - URL-arguments for set.
     *                  => addArgs        - URL-arguments for adding.
     *                  => removeArgs       - URL-arguments for removing.
     *                  => removeAllArgs        - Remove all URL-arguments.
     *                  => beginPath     - String to begin of URL-path.
     *                  => endPath       - String to end of URL-path.
     *                  => replace       - The replacement data.
     *                  => anchor       - Anchor for adding.
     *                  => removeAnchor       - Remove anchor.
     *                  => referrer - referrer URL for formatting.
     *                  => const - Adduce URL to: `\rock\template\url\UrlInterface::ABS`, `\rock\template\url\UrlInterface::HTTP`,
     *                  `\rock\template\url\UrlInterface::HTTPS`. @see UrlInterface.
     * @return string
     */
    public static function modifyUrl($url, array $params = [])
    {
        if (empty($url)) {
            return '#';
        }

        if (isset($params['referrer'])) {
            $url = Url::getReferrer() ? : '';
        }
        $urlBuilder = new Url($url);
        if (isset($params['removeAllArgs'])) {
            $urlBuilder->removeAllArgs();
        }
        if (isset($params['removeArgs'])) {
            $urlBuilder->removeArgs($params['removeArgs']);
        }
        if (isset($params['removeAnchor'])) {
            $urlBuilder->removeAnchor();
        }
        if (isset($params['beginPath'])) {
            $urlBuilder->addBeginPath($params['beginPath']);
        }
        if (isset($params['endPath'])) {
            $urlBuilder->addEndPath($params['endPath']);
        }
        if (isset($params['replace'])) {
            if (!isset($params['replace'][1])) {
                $params['replace'][1] = '';
            }
            list($search, $replace) = $params['replace'];
            $urlBuilder->replacePath($search, $replace);
        }
        if (isset($params['args'])) {
            $urlBuilder->setArgs($params['args']);
        }
        if (isset($params['addArgs'])) {
            $urlBuilder->addArgs($params['addArgs']);
        }
        if (isset($params['anchor'])) {
            $urlBuilder->addAnchor($params['anchor']);
        }
        return $urlBuilder->get(Helper::getValue($params['const'], 0), (bool)Helper::getValue($params['selfHost']));
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