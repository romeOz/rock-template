<?php

namespace rock\template\url;

use rock\helpers\Helper;
use rock\helpers\String;
use rock\template\ObjectTrait;
use rock\template\request\Request;
use rock\template\Template;

/**
 * Class Url
 * @property string $scheme
 * @property string $host
 * @property int $port
 * @property string $user
 * @property string $pass
 * @property string $path
 * @property string|null $query
 * @property string|null $fragment
 * @package rock\template\url
 */
class Url implements UrlInterface
{
    use ObjectTrait {
        ObjectTrait::__construct as parentConstruct;
    }

    /**
     * Array URL-data.
     *
     * @var array
     */
    protected $dataUrl = [];
    /**
     * Dummy by URL. If URL is empty.
     *
     * @var string
     */
    public $dummy = '#';
    /**
     * Strip tags (security).
     * @var bool
     */
    public $strip = true;
    /** @var  Request */
    private $_request;


    /**
     * @param string|null  $url - URL for formatting. If url as `NULL`, then use current (self) URL.
     * @param array $config
     */
    public function __construct($url = null, $config = [])
    {
        $this->parentConstruct($config);
        $this->_request = new Request();
        $url = !isset($url) ? $this->_request->getBaseHostInfo() .$this->_request->getBaseUrl() : Template::getAlias($url);
        $this->dataUrl = parse_url(trim($url));
        if (isset($this->dataUrl['query'])) {
            parse_str($this->dataUrl['query'], $this->dataUrl['query']);
        }
    }

    /**
     * Set URL-args.
     *
     * @param array $args array args
     * @return $this
     */
    public function setArgs(array $args)
    {
        $this->dataUrl['query'] = $args;

        return $this;
    }

    /**
     * Adding URL-arguments.
     *
     * @param array $args arguments
     * @return $this
     */
    public function addArgs(array $args)
    {
        $this->dataUrl['query'] = array_merge(Helper::getValue($this->dataUrl['query'], []), $args);
        $this->dataUrl['query'] = array_filter($this->dataUrl['query']);
        return $this;
    }

    /**
     * Removing URL-args.
     *
     * @param array $args arguments.
     * @return $this
     */
    public function removeArgs(array $args)
    {
        if (empty($this->dataUrl['query'])) {
            return $this;
        }

        $this->dataUrl['query'] = array_diff_key(
            $this->dataUrl['query'],
            array_flip($args)
        );

        return $this;
    }

    /**
     * Removing all URL-arguments
     * @return $this
     */
    public function removeAllArgs()
    {
        $this->dataUrl['query'] = null;
        return $this;
    }

    /**
     * Adding anchor.
     *
     * @param string $anchor
     * @return $this
     */
    public function addAnchor($anchor)
    {
        $this->dataUrl['fragment'] = $anchor;

        return $this;
    }

    /**
     * Removing anchor.
     *
     * @return $this
     */
    public function removeAnchor()
    {
        $this->dataUrl['fragment'] = null;

        return $this;
    }

    /**
     * Adding string to begin of URL-path.
     *
     * @param string $value
     * @return $this
     */
    public function addBeginPath($value)
    {
        $this->dataUrl['path'] = $value . $this->dataUrl['path'];

        return $this;
    }

    /**
     * Adding string to end of URL-path.
     *
     * @param string $value
     * @return $this
     */
    public function addEndPath($value)
    {
        $this->dataUrl['path'] .= $value;

        return $this;
    }

    /**
     * Replacing path
     *
     * @param string $search
     * @param string $replace
     * @return $this
     */
    public function replacePath($search, $replace)
    {
        $this->dataUrl['path'] = str_replace($search, $replace, $this->dataUrl['path']);
        return $this;
    }

    /**
     * Custom formatting
     *
     * @param callable $callback
     * @return $this
     */
    public function callback(\Closure $callback)
    {
        call_user_func($callback, $this);
        return $this;
    }

    protected function build(array $data)
    {
        $url = String::rconcat($data['scheme'], '://');

        if (isset($data['user']) && isset($data['pass'])) {
            $url .= String::rconcat($data['user'], ':');
            $url .= String::rconcat($data['pass'], '@');
        }
        $url .= Helper::getValue($data['host']);
        if (isset($data['path'])) {
            $url .= preg_replace(['/\/+(?!http:\/\/)/', '/\\\+/'], '/', $data['path']);
        }
        if (isset($data['query'])) {
            if (is_string($data['query'])) {
                $data['query'] = [$data['query']];
            }
            // @see http://php.net/manual/ru/function.http-build-query.php#111819
            $url .= '?' . preg_replace('/%5B[0-9]+%5D/i', '%5B%5D', http_build_query($data['query']));
        }
        $url .= String::lconcat($data['fragment'], '#');

        return $url;
    }

    /**
     * Get formatted URL.
     *
     * @param int  $const
     * @param bool $selfHost to use current host (security).
     * @return null|string
     */
    public function get($const = 0, $selfHost = false)
    {
        if ($selfHost == true) {
            $this->dataUrl['scheme'] = $this->_request->getBaseScheme();
            $this->dataUrl['host'] = $this->_request->getBaseHost();
        }

        if ($const & self::HTTP && isset($this->dataUrl['host'])) {
            $this->dataUrl['scheme'] = 'http';
        } elseif ($const & self::HTTPS && isset($this->dataUrl['host'])) {
            $this->dataUrl['scheme'] = 'https';
        } elseif($const & self::ABS) {
            if (!isset($this->dataUrl['host'])) {
                $this->dataUrl['scheme'] = $this->_request->getBaseScheme();
                $this->dataUrl['host'] = $this->_request->getBaseHost();
            }
        } else {
            unset($this->dataUrl['scheme'] , $this->dataUrl['host'], $this->dataUrl['user'], $this->dataUrl['pass']);
        }
        return $this->strip === true ? strip_tags($this->build($this->dataUrl)) : $this->build($this->dataUrl);
    }

    /**
     * Set data of URL.
     *
     * @param $name
     * @param $value
     *
     * ```php
     * (new \rock\template\url\Url)->host = site.com;
     * ```
     */
    public function __set($name, $value)
    {
        $this->dataUrl[$name] = $value;
    }

    /**
     * Get URL-data.
     * @param $name
     * @return string|null
     *
     * ```php
     * echo (new \rock\template\url\Url)->host; // result: site.com
     * ```
     */
    public function __get($name)
    {
        if (isset($this->dataUrl[$name])) {
            return $this->dataUrl[$name];
        }

        return null;
    }

    /**
     * Get absolute URL: `http://site.com`
     * @param bool $selfHost
     * @return null|string
     */
    public function getAbsoluteUrl($selfHost = false)
    {
        return $this->get(self::ABS, $selfHost);
    }

    /**
     * Get absolute URL: `/`
     * @param bool $selfHost
     * @return null|string
     */
    public function getRelativeUrl($selfHost = false)
    {
        return $this->get(0, $selfHost);
    }

    /**
     * Get http URL: `http://site.com`
     * @param bool $selfHost
     * @return null|string
     */
    public function getHttpUrl($selfHost = false)
    {
        return $this->get(self::HTTP, $selfHost);
    }

    /**
     * Get https URL: `https://site.com`
     * @param bool $selfHost
     * @return null|string
     */
    public function getHttpsUrl($selfHost = false)
    {
        return $this->get(self::HTTPS, $selfHost);
    }
}