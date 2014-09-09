<?php

namespace rock\template\request;


use rock\template\ClassName;
use rock\template\helpers\Helper;

class Request
{
    use ClassName;
    /**
     * @var string
     */
    private static $_schema;

    /**
     * @return string
     */
    public static function getBaseScheme()
    {
        if (static::$_schema === null) {
            static::$_schema = static::isSecureConnection() ? 'https' : 'http';
        }

        return static::$_schema;
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
    public static function getBaseHostInfo()
    {
        if (self::$_hostInfo === null) {
            $secure = static::isSecureConnection();
            $http = $secure ? 'https' : 'http';
            if (isset($_SERVER['HTTP_HOST'])) {
                self::$_hostInfo = $http . '://' . $_SERVER['HTTP_HOST'];
            } else {
                self::$_hostInfo = $http . '://' . $_SERVER['SERVER_NAME'];
                $port = $secure ? static::getSecurePort() : static::getBasePort();
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
    public static function isSecureConnection()
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
    public static function getBasePort()
    {
        if (self::$_port === null) {
            self::$_port = !static::isSecureConnection() && isset($_SERVER['SERVER_PORT']) ? (int)$_SERVER['SERVER_PORT'] : 80;
        }
        return self::$_port;
    }

    /**
     * Sets the port to use for insecure requests.
     * This setter is provided in case a custom port is necessary for certain
     * server configurations.
     * @param integer $value port number.
     */
    public static function setBasePort($value)
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
    public static function getSecurePort()
    {
        if (self::$_securePort === null) {
            self::$_securePort = static::isSecureConnection() && isset($_SERVER['SERVER_PORT']) ? (int)$_SERVER['SERVER_PORT'] : 443;
        }
        return self::$_securePort;
    }

    /**
     * Sets the port to use for secure requests.
     * This setter is provided in case a custom port is necessary for certain
     * server configurations.
     * @param integer $value port number.
     */
    public static function setSecurePort($value)
    {
        if ($value != self::$_securePort) {
            self::$_securePort = (int)$value;
            self::$_hostInfo = null;
        }
    }

    private static $_host;

    public static function getBaseHost()
    {
        if (self::$_host === null) {
            self::$_host = Helper::getValue($_SERVER['HTTP_HOST'], $_SERVER['SERVER_NAME']);
        }

        return self::$_host;
    }

    private static $_url;

    /**
     * Returns the currently requested relative URL.
     * This refers to the portion of the URL that is after the [[hostInfo]] part.
     * It includes the [[queryString]] part if any.
     * @return string the currently requested relative URL. Note that the URI returned is URL-encoded.
     * @throws Exception if the URL cannot be determined due to unusual server configuration
     */
    public static function getBaseUrl()
    {
        if (self::$_url === null) {
            self::$_url = static::resolveRequestUri();
        }
        return self::$_url;
    }

    /**
     * Resolves the request URI portion for the currently requested URL.
     * This refers to the portion that is after the [[hostInfo]] part. It includes the [[queryString]] part if any.
     * The implementation of this method referenced Zend_Controller_Request_Http in Zend Framework.
     * @return string|boolean the request URI portion for the currently requested URL.
     * Note that the URI returned is URL-encoded.
     * @throws Exception if the request URI cannot be determined due to unusual server configuration
     */
    protected static function resolveRequestUri()
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