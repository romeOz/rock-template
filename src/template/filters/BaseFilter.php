<?php
namespace rock\template\filters;

use rock\base\ClassName;
use rock\date\DateTime;
use rock\helpers\ArrayHelper;
use rock\helpers\Helper;
use rock\helpers\Instance;
use rock\helpers\Json;
use rock\helpers\Serialize;
use rock\image\ImageProvider;
use rock\image\ThumbInterface;
use rock\template\Html;
use rock\template\Template;
use rock\url\Url;

class BaseFilter
{
    use className;

    /**
     * Unserialize.
     *
     * @param string $value serialized array
     * @param array $params params:
     *
     * - key
     * - separator
     *
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
     * Replace variables template (`chunk`, `snippet`...).
     *
     * @param string $content content
     * @param array $placeholders
     * @param Template $template
     * @return string
     */
    public static function replaceTpl($content, array $placeholders = null, Template $template)
    {
        $template = clone $template;
        $template->removeAllPlaceholders();

        return $template->replace($content, $placeholders);
    }

    /**
     * Modify date.
     *
     * @param string $date date
     * @param array $params params:
     *
     * - format: date format
     * - locale: date locale.
     *
     * @return string|null
     */
    public static function modifyDate($date, array $params = [])
    {
        if (empty($date)) {
            return null;
        }
        $params['config'] = Helper::getValue($params['config'], []);
        if (!empty($params['locale'])) {
            $params['config']['locale'] = $params['locale'];
        }
        $dateTime = DateTime::set($date, null, $params['config']);
        if (isset($params['timezone'])) {
            $dateTime->convertTimezone($params['timezone']);
        }
        return $dateTime->format(Helper::getValue($params['format']));
    }

    /**
     * Modify url.
     *
     * @param string $url
     * @param array $params params:
     *
     * - modify:      modify arguments.
     * - csrf:        adding a CSRF-token.
     * - scheme: adduce URL to: {@see \rock\url\Url::ABS}, {@see \rock\url\Url::HTTP},
     *                  and {@see \rock\url\Url::HTTPS}.
     * @param Template $template
     * @return string
     * @throws \rock\url\UrlException
     */
    public static function modifyUrl($url, array $params = [], Template $template)
    {
        if (empty($url)) {
            return '#';
        }
        if (!isset($params['modify'])) {
            $params['modify'] = [];
        }
        array_unshift($params['modify'], $url);
        $config = isset($params['config']) ? $params['config'] : [];
        if (isset($params['csrf'])) {
            $config['csrf'] = (bool)$params['csrf'];
        }
        if (isset($params['scheme'])) {
            $params['modify']['@scheme'] = $params['scheme'];
        }
        $config['request'] = $template->request;

        return Url::modify($params['modify'], $config);
    }

    /**
     * Converting array to JSON-object.
     *
     * @param array $array current array
     * @return string
     */
    public static function arrayToJson($array)
    {
        if (empty($array)) {
            return null;
        }

        return Json::encode($array) ?: null;
    }

    /**
     * Get thumb.
     *
     * @param string $path src to image
     * @param array $params params:
     *
     * - type:     get `src`, `<a>`, `<img>` (default: `<img>`)
     * - w:        width
     * - h:        height
     * - q:        quality
     * - class:    attr `class`
     * - alt:      attr `alt`
     * - const
     * - dummy
     * @param Template $template
     * @return string
     * @throws \rock\helpers\InstanceException
     */
    public static function thumb($path, array $params, Template $template)
    {
        if (empty($path)) {
            if (empty($params['dummy'])) {
                return '';
            }
            $path = $params['dummy'];
        }
        $const = Helper::getValue($params['const'], 1, true);
        /** @var ImageProvider $imageProvider */
        $imageProvider = Instance::ensure(isset($params['imageProvider']) ? $params['imageProvider'] : 'imageProvider');
        $src = $imageProvider->get($path, Helper::getValue($params['w']), Helper::getValue($params['h']));
        if (!($const & ThumbInterface::WITHOUT_WIDTH_HEIGHT)) {
            $params['width'] = $imageProvider->width;
            $params['height'] = $imageProvider->height;
        }
        unset($params['h'], $params['w'], $params['type'], $params['const']);
        if (!empty($params['alt'])) {
            $params['alt'] = $template->replace($params['alt']);
        }
        return $const & ThumbInterface::OUTPUT_IMG ? Html::img($src, $params) : $src;
    }
}