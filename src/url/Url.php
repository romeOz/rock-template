<?php

namespace rock\template\url;

use rock\template\helpers\Helper;
use rock\template\helpers\String;
use rock\template\ObjectTrait;
use rock\template\Template;

class Url implements UrlInterface
{
    use ObjectTrait;

    /**
     * Array data by url
     *
     * @var array
     */
    protected $dataUrl = [];

    /**
     * Dummy by url
     *
     * @var string
     */
    public $dummy = '#';

    public $strip = true;


    public function set($url = null)
    {
        $url = empty($url) ? $this->getHostInfo() . $this->getBaseUrl() : Template::getAlias($url);
        $this->dataUrl = parse_url(trim($url));
        if (isset($this->dataUrl['query'])) {
            parse_str($this->dataUrl['query'], $this->dataUrl['query']);
        }

        return $this;
    }

    /**
     * Set args
     *
     * @param array $args - array args
     * @return $this
     */
    public function setArgs(array $args)
    {
        if (empty($this->dataUrl)) {
            $this->set();
        }
        $this->dataUrl['query'] = $args;

        return $this;
    }

    /**
     * Add args
     *
     * @param array $args - array args
     * @return $this
     */
    public function addArgs(array $args)
    {
        if (empty($this->dataUrl)) {
            $this->set();
        }
        $this->dataUrl['query'] = array_merge(Helper::getValue($this->dataUrl['query'], []), $args);
        $this->dataUrl['query'] = array_filter($this->dataUrl['query']);
        return $this;
    }


    /**
     * Remove args
     *
     * @param array $args - array args
     * @return $this
     */
    public function removeArgs(array $args)
    {
        if (empty($this->dataUrl)) {
            $this->set();
        }
        if (empty($this->dataUrl['query'])) {
            return $this;
        }

        $this->dataUrl['query'] = array_diff_key(
            $this->dataUrl['query'],
            array_flip($args)
        );

        return $this;
    }

    public function removeAllArgs()
    {
        if (empty($this->dataUrl)) {
            $this->set();
        }
        $this->dataUrl['query'] = null;
        return $this;
    }

    /**
     * Add args
     *
     * @param string $anchor
     * @return $this
     */
    public function addAnchor($anchor)
    {
        if (empty($this->dataUrl)) {
            $this->set();
        }
        $this->dataUrl['fragment'] = $anchor;

        return $this;
    }

    /**
     * Remove anchor
     *
     * @return $this
     */
    public function removeAnchor()
    {
        if (empty($this->dataUrl)) {
            $this->set();
        }
        $this->dataUrl['fragment'] = null;

        return $this;
    }


    /**
     * Add string to begin of path
     *
     * @param string $value
     * @return $this
     */
    public function addBeginPath($value)
    {
        if (empty($this->dataUrl)) {
            $this->set();
        }
        $this->dataUrl['path'] = $value . $this->dataUrl['path'];

        return $this;
    }


    /**
     * Add string to end pf path
     *
     * @param string $value
     * @return $this
     */
    public function addEndPath($value)
    {
        if (empty($this->dataUrl)) {
            $this->set();
        }
        $this->dataUrl['path'] .= $value;

        return $this;
    }



    public function callback(\Closure $callback)
    {
        if (empty($this->dataUrl)) {
            $this->set();
        }
        call_user_func($callback, $this->dataUrl);
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
        $url .= preg_replace(['/\/+(?!http:\/\/)/', '/\\\+/'], '/', $data['path']);

        if (isset($data['query'])) {
            /**
             * @see http://php.net/manual/ru/function.http-build-query.php#111819
             */
            $url .= '?' . preg_replace('/%5B[0-9]+%5D/i', '%5B%5D', http_build_query($data['query']));
        }
        $url .= String::lconcat($data['fragment'], '#');

        return $url;
    }

    public function get($const = 0, $selfHost = false)
    {
        if (empty($this->dataUrl)) {
            $this->set();
        }
        if (empty($this->dataUrl['path'])) {
            $this->dataUrl = array_merge(parse_url($this->getHostInfo() . $this->getBaseUrl()), $this->dataUrl);
        }
        if ($selfHost == true) {
            $this->dataUrl['scheme'] = $this->getScheme();
            $this->dataUrl['host'] = $this->getHost();
        }

        if ($const & self::HTTP && isset($this->dataUrl['host'])) {
            $this->dataUrl['scheme'] = 'http';
        } elseif ($const & self::HTTPS && isset($this->dataUrl['host'])) {
            $this->dataUrl['scheme'] = 'https';
        } elseif($const & self::ABS) {
            if (!isset($this->dataUrl['host'])) {
                $this->dataUrl['scheme'] = $this->getScheme();
                $this->dataUrl['host'] = $this->getHost();
            }
        } else {
            unset($this->dataUrl['scheme'] , $this->dataUrl['host'], $this->dataUrl['user'], $this->dataUrl['pass']);
        }
        return $this->strip === true ? strip_tags($this->build($this->dataUrl)) : $this->build($this->dataUrl);
    }

    /**
     * Get absolute url: http://site.com
     * @param bool $selfHost
     * @return null|string
     */
    public function getAbsoluteUrl($selfHost = false)
    {
        return $this->get(self::ABS, $selfHost);
    }

    /**
     * Get absolute url: /
     * @param bool $selfHost
     * @return null|string
     */
    public function getRelativeUrl($selfHost = false)
    {
        return $this->get(0, $selfHost);
    }

    /**
     * Get http url: http://site.com
     * @param bool $selfHost
     * @return null|string
     */
    public function getHttpUrl($selfHost = false)
    {
        return $this->get(self::HTTP, $selfHost);
    }

    /**
     * Get https url: http://site.com
     * @param bool $selfHost
     * @return null|string
     */
    public function getHttpsUrl($selfHost = false)
    {
        return $this->get(self::HTTPS, $selfHost);
    }
    
    public function reset()
    {
        $this->dataUrl = [];
        $this->strip = true;
        $this->dummy = '#';
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
    public function getHostInfo()
    {
        if (self::$_hostInfo === null) {
            $secure = $this->isSecureConnection();
            $http = $secure ? 'https' : 'http';
            if (isset($_SERVER['HTTP_HOST'])) {
                self::$_hostInfo = $http . '://' . $_SERVER['HTTP_HOST'];
            } else {
                self::$_hostInfo = $http . '://' . $_SERVER['SERVER_NAME'];
                $port = $secure ? $this->getSecurePort() : $this->getPort();
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
    public function getPort()
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
    public function setPort($value)
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
    public function getScheme()
    {
        if (static::$_schema === null) {
            static::$_schema = $this->isSecureConnection() ? 'https' : 'http';
        }

        return static::$_schema;
    }

    private static $_host;

    public function getHost()
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
}