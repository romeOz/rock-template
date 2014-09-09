<?php

namespace rock\template\url;

use rock\template\helpers\Helper;
use rock\template\helpers\String;
use rock\template\ObjectTrait;
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

    /**
     * @param string|null  $url - URL for formatting. If url as `NULL`, then use current (self) URL.
     * @param array $config
     */
    public function __construct($url = null, $config = [])
    {
        $this->parentConstruct($config);
        $url = !isset($url) ? $this->getBaseHostInfo() . $this->getBaseUrl() : Template::getAlias($url);
        $this->dataUrl = parse_url(trim($url));
        if (isset($this->dataUrl['query'])) {
            parse_str($this->dataUrl['query'], $this->dataUrl['query']);
        }
    }

    /**
     * Set URL-args
     *
     * @param array $args - array args
     * @return $this
     */
    public function setArgs(array $args)
    {
        $this->dataUrl['query'] = $args;

        return $this;
    }

    /**
     * Adding URL-arguments
     *
     * @param array $args - arguments
     * @return $this
     */
    public function addArgs(array $args)
    {
        $this->dataUrl['query'] = array_merge(Helper::getValue($this->dataUrl['query'], []), $args);
        $this->dataUrl['query'] = array_filter($this->dataUrl['query']);
        return $this;
    }

    /**
     * Removing URL-args
     *
     * @param array $args - arguments
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
     * @param bool $selfHost - to use current host (security).
     * @return null|string
     */
    public function get($const = 0, $selfHost = false)
    {
        if ($selfHost == true) {
            $this->dataUrl['scheme'] = $this->getBaseScheme();
            $this->dataUrl['host'] = $this->getBaseHost();
        }

        if ($const & self::HTTP && isset($this->dataUrl['host'])) {
            $this->dataUrl['scheme'] = 'http';
        } elseif ($const & self::HTTPS && isset($this->dataUrl['host'])) {
            $this->dataUrl['scheme'] = 'https';
        } elseif($const & self::ABS) {
            if (!isset($this->dataUrl['host'])) {
                $this->dataUrl['scheme'] = $this->getBaseScheme();
                $this->dataUrl['host'] = $this->getBaseHost();
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
     * (new \rock\template\url\Url)->host = site.com
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


    private static $_hostInfo;

    /**
     * Returns the schema and host part of the current request URL.
     * The returned URL does not have an ending slash.
     * By default this is determined based on the user request information.
     * You may explicitly specify it by setting the [[setHostInfo()|hostInfo]] property.
     * @return string schema and hostname part (with port number if needed) of the request URL (e.g. `http://www.site.com`)
     * @see setHostInfo()
     */
    public function getBaseHostInfo()
    {
        if (self::$_hostInfo === null) {
            $secure = $this->isSecureConnection();
            $http = $secure ? 'https' : 'http';
            if (isset($_SERVER['HTTP_HOST'])) {
                self::$_hostInfo = $http . '://' . $_SERVER['HTTP_HOST'];
            } else {
                self::$_hostInfo = $http . '://' . $_SERVER['SERVER_NAME'];
                $port = $secure ? $this->getSecurePort() : $this->getBasePort();
                if (($port !== 80 && !$secure) || ($port !== 443 && $secure)) {
                    self::$_hostInfo .= ':' . $port;
                }
            }
        }

        return self::$_hostInfo;
    }

    /**
     * Return if the request is sent via secure channel (https).
     * @return boolean if the request is sent via secure channel (https)
     */
    public function isSecureConnection()
    {
        return isset($_SERVER['HTTPS']) && (strcasecmp($_SERVER['HTTPS'], 'on') === 0 || $_SERVER['HTTPS'] == 1)
               || isset($_SERVER['HTTP_X_FORWARDED_PROTO']) && strcasecmp($_SERVER['HTTP_X_FORWARDED_PROTO'], 'https') === 0;
    }

    private static $_port;


    /**
     * Returns the port to use for insecure requests.
     * Defaults to 80, or the port specified by the server if the current
     * request is insecure.
     * @return integer port number for insecure requests.
     * @see setPort()
     */
    public function getBasePort()
    {
        if (self::$_port === null) {
            self::$_port = !$this->isSecureConnection() && isset($_SERVER['SERVER_PORT']) ? (int)$_SERVER['SERVER_PORT'] : 80;
        }
        return self::$_port;
    }

    /**
     * Sets the port to use for insecure requests.
     * This setter is provided in case a custom port is necessary for certain
     * server configurations.
     * @param integer $value port number.
     */
    public function setBasePort($value)
    {
        if ($value != self::$_port) {
            self::$_port = (int)$value;
            self::$_hostInfo = null;
        }
    }

    private static $_securePort;

    /**
     * Returns the port to use for secure requests.
     * Defaults to 443, or the port specified by the server if the current
     * request is secure.
     * @return integer port number for secure requests.
     * @see setSecurePort()
     */
    public function getSecurePort()
    {
        if (self::$_securePort === null) {
            self::$_securePort = $this->isSecureConnection() && isset($_SERVER['SERVER_PORT']) ? (int)$_SERVER['SERVER_PORT'] : 443;
        }
        return self::$_securePort;
    }

    /**
     * Sets the port to use for secure requests.
     * This setter is provided in case a custom port is necessary for certain
     * server configurations.
     * @param integer $value port number.
     */
    public function setSecurePort($value)
    {
        if ($value != self::$_securePort) {
            self::$_securePort = (int)$value;
            self::$_hostInfo = null;
        }
    }

    /**
     * @var string
     */
    private static $_schema;

    /**
     * @return string
     */
    public function getBaseScheme()
    {
        if (static::$_schema === null) {
            static::$_schema = $this->isSecureConnection() ? 'https' : 'http';
        }

        return static::$_schema;
    }

    private static $_host;

    public function getBaseHost()
    {
        if (self::$_host === null) {
            self::$_host = Helper::getValue($_SERVER['HTTP_HOST'], $_SERVER['SERVER_NAME']);
        }

        return self::$_host;
    }

    private $_url;

    /**
     * Returns the currently requested relative URL.
     * This refers to the portion of the URL that is after the [[hostInfo]] part.
     * It includes the [[queryString]] part if any.
     * @return string the currently requested relative URL. Note that the URI returned is URL-encoded.
     * @throws Exception if the URL cannot be determined due to unusual server configuration
     */
    public function getBaseUrl()
    {
        if ($this->_url === null) {
            $this->_url = $this->resolveRequestUri();
        }
        return $this->_url;
    }

    /**
     * Resolves the request URI portion for the currently requested URL.
     * This refers to the portion that is after the [[hostInfo]] part. It includes the [[queryString]] part if any.
     * The implementation of this method referenced Zend_Controller_Request_Http in Zend Framework.
     * @return string|boolean the request URI portion for the currently requested URL.
     * Note that the URI returned is URL-encoded.
     * @throws Exception if the request URI cannot be determined due to unusual server configuration
     */
    protected function resolveRequestUri()
    {
        if (isset($_SERVER['HTTP_X_REWRITE_URL'])) { // IIS
            $requestUri = $_SERVER['HTTP_X_REWRITE_URL'];
        } elseif (isset($_SERVER['REQUEST_URI'])) {
            $requestUri = $_SERVER['REQUEST_URI'];
            if ($requestUri !== '' && $requestUri[0] !== '/') {
                $requestUri = preg_replace('/^(http|https):\/\/[^\/]+/i', '', $requestUri);
            }
        } elseif (isset($_SERVER['ORIG_PATH_INFO'])) { // IIS 5.0 CGI
            $requestUri = $_SERVER['ORIG_PATH_INFO'];
            if (!empty($_SERVER['QUERY_STRING'])) {
                $requestUri .= '?' . $_SERVER['QUERY_STRING'];
            }
        } else {
            throw new Exception('Unable to determine the request URI.');
        }
        return $requestUri;
    }

    /**
     * Returns the URL referrer, null if not present
     * @return string URL referrer, null if not present
     */
    public static function getReferrer()
    {
        return isset($_SERVER['HTTP_REFERER']) ? $_SERVER['HTTP_REFERER'] : null;
    }
}