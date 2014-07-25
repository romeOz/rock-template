<?php
namespace rock\template;

use rock\template\filters\ConditionFilter;
use rock\template\helpers\ArrayHelper;
use rock\template\helpers\File;
use rock\template\helpers\Helper;
use rock\template\helpers\Html;
use rock\template\helpers\Json;
use rock\template\helpers\Numeric;
use rock\template\helpers\ObjectHelper;
use rock\template\helpers\Serialize;
use rock\template\helpers\String;

class Template
{
    use ObjectTrait;

    const ESCAPE = 1;
    const STRIP_TAGS = 2;
    const TO_TYPE = 4;

    const ENGINE_ROCK = 1;
    const ENGINE_PHP = 2;

    /**
     * @event Event an event that is triggered by [[beginPage()]].
     */
    const EVENT_BEGIN_PAGE = 'beginPage';
    /**
     * @event Event an event that is triggered by [[endPage()]].
     */
    const EVENT_END_PAGE = 'endPage';

    /**
     * @event Event an event that is triggered by [[beginBody()]].
     */
    const EVENT_BEGIN_BODY = 'beginBody';
    /**
     * @event Event an event that is triggered by [[endBody()]].
     */
    const EVENT_END_BODY = 'endBody';


    /**
     * The location of registered JavaScript code block or files.
     * This means the location is in the head section.
     */
    const POS_HEAD = 1;
    /**
     * The location of registered JavaScript code block or files.
     * This means the location is at the beginning of the body section.
     */
    const POS_BEGIN = 2;
    /**
     * The location of registered JavaScript code block or files.
     * This means the location is at the end of the body section.
     */
    const POS_END = 3;

    /**
     * Extension file layout/chunk. If used [[ENGINE_PHP]], then ".php" by default
     * @var string
     */
    public $fileExtension = 'html';
    /** @var int  */
    public $engine = self::ENGINE_ROCK;
    /** @var array  */
    public $snippets = [];
    /** @var array  */
    public $filters = [];
    /** @var array  */
    public $extensions = [];
    /** @var int|bool  */
    public $autoEscape = self::ESCAPE;

    /** @var string */
    public $head = '<!DOCTYPE html>';
    /**
     * @var array the registered link tags.
     * @see registerLinkTag()
     */
    public $linkTags = [];

    /**
     * @var array the registered CSS code blocks.
     * @see registerCss()
     */
    public $css = [];
    /**
     * @var array the registered CSS files.
     * @see registerCssFile()
     */
    public $cssFiles = [];
    /**
     * @var array the registered JS code blocks
     * @see registerJs()
     */
    public $js = [];
    /**
     * @var array the registered JS files.
     * @see registerJsFile()
     */
    public $jsFiles = [];

    /**
     * @var string the page title
     */
    public $title = '';
    /**
     * @var array the registered meta tags.
     * @see registerMetaTag()
     */
    public $metaTags = [];
    /**
     * Instance Controller where render template
     * @var object
     */
    public $context;
    /** @var \rock\cache\CacheInterface|null  */
    public $cache;
    /**
     * Array global placeholders of variables template engine
     * @var array
     */
    protected static $placeholders = [];
    /**
     * Array local placeholders of variables template engine
     * @var array
     */
    protected $localPlaceholders = [];
    protected $oldPlaceholders = [];
    public $cachePlaceholders = [];

    public function init()
    {
        $configs = $this->defaultConfig();
        $this->snippets = array_merge($configs['snippets'], $this->snippets);
        $this->filters = array_merge($configs['filters'], $this->filters);
    }

    /**
     * Get default filters and snippets
     * @return array
     */
    public function defaultConfig()
    {
        return require(__DIR__ . '/configs.php');
    }

    /**
     * Rendering layout
     *
     * @param string      $name - path to layout
     * @param array       $placeholders
     * @param object|null $context
     * @return string
     */
    public function render($name, array $placeholders = [], $context = null)
    {
        if (!isset($this->context)) {
            $this->context = $context;
        }
        list($cacheKey, $cacheExpire, $cacheTags) = $this->calculateCacheParams($placeholders);
        // Get cache
        if (($resultCache = $this->getCache($cacheKey)) !== false) {
            return $resultCache;
        }
        $result = $this->prepareRender($name, $placeholders);
        foreach (['jsFiles', 'js', 'linkTags', 'cssFiles', 'css','linkTags', 'title', 'metaTags'] as $property) {
            if ($this->$property instanceof \Closure) {
                $this->$property = call_user_func($this->$property, $this);
            }
        }
        $result = implode("\n", [$this->beginPage(), $this->beginBody(), $result, $this->endBody(), $this->endPage()]);
        // Set cache
        $this->setCache($cacheKey, $result, $cacheExpire, $cacheTags);

        return $result;
    }


    /**
     * @param string      $name - path to layout/chunk
     * @param array       $placeholders
     * @throws Exception
     * @return string
     */
    protected function prepareRender($name, array $placeholders = [])
    {
        $name = static::getAlias($name);
        $path = $name .
              '.' . ($this->engine === self::ENGINE_PHP ? 'php' : $this->fileExtension);
        $path = File::normalizePath($path);

        if (!file_exists($path)) {
            throw new Exception(Exception::UNKNOWN_FILE, 0, ['path' => $path]);
        }
        if ($this->engine === self::ENGINE_PHP) {
            $this->addMultiPlaceholders($placeholders ? : []);
            return $this->renderPhpFile($path);
        } else {
            return $this->replace(file_get_contents($path), $placeholders);
        }
    }

    protected function renderPhpFile($_path_)
    {
        ob_start();
        ob_implicit_flush(false);
        require($_path_);

        return ob_get_clean();
    }

    /**
     * Replace variables template (chunk, snippet...) on content
     *
     * @param string $code         - current template with variables template
     * @param array  $placeholders - array placeholders of variables template
     * @return string
     */
    public function replace($code, array $placeholders = [])
    {
        $code = Helper::toType($code);
        if (empty($code) || !is_string($code)) {
            return $code;
        }
        if (!empty($placeholders) && is_array($placeholders)) {
            $this->addMultiPlaceholders($placeholders);
        }

        /**
         * Remove tpl-comment
         * ```
         * {* Comment about *}
         * ```
         */
        $code = preg_replace('/\{\*.*?\*\}/is', "", $code);
        $code = preg_replace_callback(
            '/
                (?P<beforeSkip>\{\!\\s*)?\[\[
                (?P<escape>\!)?
                (?P<type>[\#\%\~]?|\+{1,2}|\*{1,2}|\${1,2})					# search type of variable template
                (?P<name>[\\w\-\/\\\.@]+)							# name of variable template [\w, -, \, .]
                (?:[^\[\]]++ | \[(?!\[) | \](?!\]) | (?R))*		# possible recursion
                \]\](?P<afterSkip>\\s*\!\})?
            /iux',
            [$this, 'replaceCallback'],
            $code
        );

        return $code;
    }

    /**
     * Rendering chunk
     *
     * @param string      $name - path to chunk
     * @param array  $placeholders - params
     * @return string
     */
    public function getChunk($name, array $placeholders = [])
    {
        $template = clone $this;
        $template->removeAllPlaceholders();
        list($cacheKey, $cacheExpire, $cacheTags) = $template->calculateCacheParams($placeholders);
        // Get cache
        if (($resultCache = $template->getCache($cacheKey)) !== false) {
            return $resultCache;
        }
        $result = $template->prepareRender($name, $placeholders);
        // Set cache
        $template->setCache($cacheKey, $result, $cacheExpire, $cacheTags);
        return $result;
    }

    /**
     * Has chunk
     *
     * @param string $name - name of chunk
     * @return bool
     */
    public function hasChunk($name)
    {
        return file_exists(static::getAlias($name) . '.' . $this->fileExtension);
    }

    /**
     * Get data from snippet
     *
     * @param string|Snippet $snippet - name
     * @param array          $params  - params
     * @param bool           $autoEscape
     * @return mixed
     */
    public function getSnippet($snippet, array $params = [], $autoEscape = true)
    {
        $template = clone $this;
        $template->removeAllPlaceholders();
        $result = $template->getSnippetInternal($snippet, $params, $autoEscape);
        $this->cachePlaceholders = $template->cachePlaceholders;
        return $result;
    }

    protected function getSnippetInternal($snippet, array $params = [], $autoEscape = true)
    {
        list($cacheKey, $cacheExpire, $cacheTags) = $this->calculateCacheParams($params);
        $config = [];
        if ($snippet instanceof Snippet) {
            if (!empty($params)) {
                $snippet->setProperties($params);
            }
        } else {
            $class = static::getAlias($snippet);
            if (isset($this->snippets[$class])) {
                $config = $this->snippets[$class];
                $class = $config['class'];
                unset($config['class']);
            }
            if (!class_exists($class)) {
                throw new Exception(Exception::UNKNOWN_SNIPPET, 0, ['name' => $class]);
            }
            /** @var Snippet $snippet */
            $snippet = new $class(array_merge($config, $params));

            if (!$snippet instanceof Snippet) {
                throw new Exception(Exception::UNKNOWN_SNIPPET, 0, ['name' => $snippet::className()]);
            }
        }
        if ($autoEscape === false) {
            $snippet->autoEscape = false;
        }
        $snippet->template = $this;

        // Get cache
        if (($resultCache = $this->getCache($cacheKey)) !== false) {
            return $resultCache;
        }
        $result = $snippet->get();
        $result = $this->autoEscape($result, isset($params['autoEscape']) && $params['autoEscape'] === false ? false : $snippet->autoEscape);
        $result = is_string($result)
            ? str_replace(
                ['[[', ']]', '{{{', '}}}', '`', '“', '”'],
                ['&#91;&#91;', '&#93;&#93;', '&#123;&#123;&#123;', '&#125;&#125;&#125;', '&#96;', '&laquo;', '&raquo;'],
                $result
            )
            : $result;

        //  Set cache
        $this->setCache($cacheKey, $result, $cacheExpire, $cacheTags);
        return $result;
    }

    /**
     * Get placeholder
     *
     * @param string $name          - name
     * @param int|bool|null   $autoEscape
     * @param bool   $global - globally placeholder
     * @return string|null
     */
    public function getPlaceholder($name, $autoEscape = true, $global = false)
    {
        if ($global === true) {
            return $this->autoEscape(ArrayHelper::getValue(static::$placeholders, $name), $autoEscape);
        }
        return $this->autoEscape(ArrayHelper::getValue($this->localPlaceholders, $name), $autoEscape);
    }

    /**
     * Get local/global placeholder and resource
     * @param $name
     * @return mixed
     */
    public function __get($name)
    {
        if ($this->hasPlaceholder($name)) {
            return $this->getPlaceholder($name);
        }
        if ($this->hasPlaceholder($name, true)) {
            return $this->getPlaceholder($name, true, true);
        }
        if ($this->hasResource($name)) {
            return $this->getResource($name);
        }
        return null;
    }

    /**
     * Exists local/global placeholder and resource
     * @param $name
     * @return bool
     */
    public function __isset($name)
    {
        if ($this->hasPlaceholder($name)) {
            return true;
        }
        if ($this->hasPlaceholder($name, true)) {
            return true;
        }
        if ($this->hasResource($name)) {
            return true;
        }
        return false;
    }

    /**
     * Add local placeholder
     * @param $name
     * @param $value
     */
    public function __set($name, $value)
    {
        $this->addPlaceholder($name, $value);
    }

    /**
     * Remove local placeholder
     * @param $name
     */
    public function __unset($name)
    {
        $this->removePlaceholder($name);
    }

    /**
     * Get all placeholders
     *
     * @param int|bool $autoEscape
     * @param bool          $global - globally placeholder
     * @param array         $only
     * @param array         $exclude
     * @return array
     */
    public function getAllPlaceholders($autoEscape = true, $global = false, array $only = [], array $exclude = [])
    {
        if ($global === true) {
            return $this->autoEscape(ArrayHelper::prepareArray(static::$placeholders, $only, $exclude), $autoEscape);
        }

        return $this->autoEscape(ArrayHelper::prepareArray($this->localPlaceholders, $only, $exclude), $autoEscape);
    }

    /**
     * Exists placeholder
     *
     * @param      $name - name of placeholder
     * @param bool $global
     * @return bool
     */
    public function hasPlaceholder($name, $global = false)
    {
        if ($global === true) {
            return (bool)ArrayHelper::getValue(static::$placeholders, $name);
        }

        return (bool)ArrayHelper::getValue($this->localPlaceholders, $name);

    }

    /**
     * Set placeholder
     *
     * @param string $name  - key
     * @param mixed  $value - value
     * @param bool   $global - globally placeholder
     */
    public function addPlaceholder($name, $value = null, $global = false)
    {
        if ($global === true) {
            static::$placeholders[$name] =
                isset(static::$placeholders[$name]) && is_array(static::$placeholders[$name])
                    ? array_merge(static::$placeholders[$name], (array)$value)
                    : $value;
            return;
        }

        $this->localPlaceholders[$name] =
            isset($this->localPlaceholders[$name]) && is_array($this->localPlaceholders[$name])
                ? array_merge($this->localPlaceholders[$name], (array)$value)
                : $value;
        $this->oldPlaceholders = $this->localPlaceholders;
    }

    /**
     * Set placeholders
     *
     * @param array $placeholders - array
     * @param bool   $global - globally placeholder
     * @return mixed
     */
    public function addMultiPlaceholders(array $placeholders, $global = false)
    {
        if ($global === true) {
            static::$placeholders = array_merge(static::$placeholders, $placeholders);
            return;
        }

        $this->localPlaceholders = $this->oldPlaceholders = array_merge($this->localPlaceholders, $placeholders);
    }

    /**
     * Deleted placeholder
     *
     * @param string $name   - key
     * @param bool   $global - globally placeholder
     */
    public function removePlaceholder($name, $global = false)
    {
        if (empty($name)) {
            return;
        }
        if ($global === true) {
            unset(static::$placeholders[$name]);
            return;
        }
        unset($this->localPlaceholders[$name]);
    }

    /**
     * Deleted multi-placeholders
     *
     * @param array $names - array of
     * @param bool   $global - globally placeholder
     */
    public function removeMultiPlaceholders(array $names, $global = false)
    {
        if ($global === true) {
            static::$placeholders = array_diff_key(static::$placeholders, array_flip($names));
            return;
        }
        $this->localPlaceholders = array_diff_key($this->localPlaceholders, array_flip($names));
    }

    /**
     * Deleted all placeholders
     * @param bool $global
     */
    public function removeAllPlaceholders($global = false)
    {
        if ($global === true) {
            static::$placeholders = [];
            return;
        }
        $this->localPlaceholders = [];
    }

    protected static $resources = [];

    /**
     * Added resource
     * @param $name - name of resource
     * @param $value - value of resource
     */
    public function addResource($name, $value)
    {
        static::$resources[$name] = $value;
    }

    /**
     * Added multi-resources
     * @param array $resources
     */
    public function addMultiResources(array $resources)
    {
        foreach ($resources as $name => $value) {
            $this->addResource($name, $value);
        }
    }

    /**
     * Get resource
     * @param string     $name - name of resource
     * @param bool $autoEscape
     * @return array|mixed|null|string
     */
    public function getResource($name, $autoEscape = true)
    {
        return $this->autoEscape(ArrayHelper::getValue(static::$resources, $name), $autoEscape);
    }

    /**
     * Has resource by name
     * @param $name - name of resource
     * @return bool
     */
    public function hasResource($name)
    {
        return (bool)ArrayHelper::getValue(static::$resources, $name);
    }

    /**
     * Get all resources
     * @param bool  $autoEscape
     * @param array $only
     * @param array $exclude
     * @return array|mixed|null|string
     */
    public function getAllResources($autoEscape = true, array $only = [], array $exclude = [])
    {
        return $this->autoEscape(ArrayHelper::prepareArray(static::$resources, $only, $exclude), $autoEscape);
    }

    /**
     * Deleted resource
     * @param $name - name of resource
     */
    public function removeResource($name)
    {
        unset(static::$resources[$name]);
    }

    /**
     * Deleted all resources
     */
    public function removeAllResource()
    {
        static::$resources = [];
    }

    /**
     * Replace inline tpl
     * @param string $value  - value
     * @param array  $placeholders
     * @return string
     */
    public function replaceParamByPrefix($value, array $placeholders = [])
    {
        $dataPrefix = $this->getNamePrefix($value);
        if (strtolower($dataPrefix['prefix']) === 'inline') {
            $template = clone $this;
            $result = $template->replace($dataPrefix['value'], $placeholders);

            return $result;
        }
        $result = $this->getChunk(trim($value), $placeholders);

        return $result;
    }

    /**
     * Get name prefix by param
     * @param string $value - value of param
     * @return array|null
     */
    public function getNamePrefix($value)
    {
        if (empty($value)) {
            return null;
        }
        preg_match('/(?:\@(?P<prefix>INLINE))?(?P<value>.+)/s', $value, $matches);

        return [
            'prefix' => Helper::getValue($matches['prefix']),
            'value'  => Helper::getValue($matches['value'])
        ];
    }

    /**
     * Remove prefix by param
     * @param string $value - value of param
     * @return string|null
     */
    public function removePrefix($value)
    {
        if (empty($value)) {
            return null;
        }

        return preg_replace('/\@[A-Z\-\_]+/', '', $value);
    }

    /**
     * Make filter (modifier)
     * @param string $value   - value
     * @param array  $filters - array of filters with params
     * @throws Exception
     * @return string
     */
    public function makeFilter($value, $filters)
    {
        foreach ($filters as $method => $params) {
            if (empty($params)) {
                $params = [];
            }
            foreach ($params as $_params) {
                if (isset($this->filters[$method]['class'])) {
                    $class = $this->filters[$method]['class'];
                    if (isset($this->filters[$method]['handlers'])) {
                        $_params['_handlers'] = $this->filters[$method]['handlers'];
                        if (is_array($_params['_handlers'])) {
                            $_params['_handlers'] = array_map(
                                function($value){
                                    if ($value instanceof \Closure) {
                                        return call_user_func($value, $this);
                                    }
                                    return $value;
                                },
                                $_params['_handlers']
                            );
                        } elseif ($_params['_handlers'] instanceof \Closure) {
                            $_params['_handlers'] = call_user_func($_params['_handlers'], $this);
                        }
                    }
                    $method  = Helper::getValue($this->filters[$method]['method'], $method);
                    $value = call_user_func([$class, $method], $value, $_params, $this);
                } elseif (function_exists($method)) {
                    $value = call_user_func_array($method, array_merge([$value], $_params));
                } else {
                    throw new Exception(Exception::UNKNOWN_FILTER, 0, ['name' => $method]);
                }
            }
        };

        return $value;
    }


    /**
     * Calculate for adding placeholders
     * @param array $placeholders
     * @return array
     *
     * ```php
     * (new \rock\Template)->calculateAddPlaceholders(['foo', 'bar' => 'text']); // ['foo' => 'text', 'bar' => 'text']
     * ```
     */
    public function calculateAddPlaceholders(array $placeholders = [])
    {
        if (empty($placeholders)) {
            return [];
        }
        $result = [];
        foreach ($placeholders as $name => $value) {
            if (is_int($name)) {
                if ($this->hasPlaceholder($value)) {
                    $result[$value] = $this->getPlaceholder($value, false);
                } elseif (isset($this->oldPlaceholders[$value])) {
                    $result[$value] = $this->oldPlaceholders[$value];
                }
                continue;
            }

            $result[$name] = $value;
        }

        return $result;
    }

    /**
     * @param string $name - name of extension
     * @param array $params
     * @param bool|int  $autoEscape
     * @return mixed
     */
    public function getExtension($name, array $params = [], $autoEscape = true)
    {
        $result = $this->_getExtensionInternal($name, $params);
        if (!empty($params)) {
            $this->removePlaceholder('params');
        }

        return $this->autoEscape($result, $autoEscape);
    }

    protected static $data = [];

    /**
     * Autoescape vars of template engine
     * @param mixed $value
     * @param bool|int $const
     * @return mixed
     */
    public function autoEscape($value, $const = true)
    {
        if (is_array($value)) {
            $hash = Helper::hash($value, Helper::SERIALIZE_JSON);
            if (isset(static::$data[$hash])) {
                return static::$data[$hash];
            }

            return static::$data[$hash] =
                ArrayHelper::map(
                    $value,
                    function ($value) use ($const) {
                        return $this->escape($value, $const);
                    },
                    true
                );
        }

        return $this->escape($value, $const);
    }

    /**
     * Marks the beginning of a page.
     */
    public function beginPage()
    {
        return $this->renderHeadHtml();
    }

    /**
     * Marks the beginning of an HTML body section.
     */
    public function beginBody()
    {
        return $this->renderBodyBeginHtml();
    }

    /**
     * Marks the ending of an HTML body section.
     */
    public function endBody()
    {
        return $this->renderBodyEndHtml();
    }

    /**
     * Marks the ending of an HTML page.
     * If true, the JS scripts registered at [[POS_READY]] and [[POS_LOAD]] positions
     * will be rendered at the end of the view like normal scripts.
     */
    public function endPage()
    {
        $this->clear();
        return '</html>';
    }

    /**
     * Clears up the registered meta tags, link tags, css/js scripts and files.
     */
    public function clear()
    {
        $this->metaTags = [];
        $this->linkTags = [];
        $this->css = [];
        $this->cssFiles = [];
        $this->js = [];
        $this->jsFiles = [];
        static::$placeholders = [];
        $this->localPlaceholders = [];
    }

    /**
     * Registers a meta tag.
     * @param array $options the HTML attributes for the meta tag.
     * @param string $key the key that identifies the meta tag. If two meta tags are registered
     * with the same key, the latter will overwrite the former. If this is null, the new meta tag
     * will be appended to the existing ones.
     */
    public function registerMetaTag($options, $key = null)
    {
        if ($key === null) {
            $this->metaTags[] = $this->renderWrapperTag(Html::tag('meta', '', $options), $options);
        } else {
            $this->metaTags[$key] = $this->renderWrapperTag(Html::tag('meta', '', $options), $options);
        }
    }

    /**
     * Registers a link tag.
     * @param array $options the HTML attributes for the link tag.
     * @param string $key the key that identifies the link tag. If two link tags are registered
     * with the same key, the latter will overwrite the former. If this is null, the new link tag
     * will be appended to the existing ones.
     */
    public function registerLinkTag($options, $key = null)
    {
        if ($key === null) {
            $this->linkTags[] = $this->renderWrapperTag(Html::tag('link', '', $options), $options);
        } else {
            $this->linkTags[$key] = $this->renderWrapperTag(Html::tag('link', '', $options), $options);
        }
    }

    /**
     * Registers a CSS code block.
     * @param string $css the CSS code block to be registered
     * @param array $options the HTML attributes for the style tag.
     * @param string $key the key that identifies the CSS code block. If null, it will use
     * $css as the key. If two CSS code blocks are registered with the same key, the latter
     * will overwrite the former.
     */
    public function registerCss($css, $options = [], $key = null)
    {
        $key = $key ?: md5($css);
        $this->css[$key] = Html::style($css, $options);
    }

    /**
     * Registers a CSS file.
     * @param string $url the CSS file to be registered.
     * @param array $options the HTML attributes for the link tag.
     * @param string $key the key that identifies the CSS script file. If null, it will use
     * $url as the key. If two CSS files are registered with the same key, the latter
     * will overwrite the former.
     */
    public function registerCssFile($url, $options = [], $key = null)
    {
        $url = static::getAlias($url);
        $key = $key ?: $url;

        $position = isset($options['position']) ? $options['position'] : self::POS_HEAD;
        unset($options['position']);
        $this->cssFiles[$position][$key] = $this->renderWrapperTag(Html::cssFile($url, $options), $options);
    }

    /**
     * Registers a JS code block.
     * @param string $js the JS code block to be registered
     * @param integer $position the position at which the JS script tag should be inserted
     * in a page. The possible values are:
     *
     * - [[POS_HEAD]]: in the head section
     * - [[POS_BEGIN]]: at the beginning of the body section
     * - [[POS_END]]: at the end of the body section
     *   Note that by using this position, the method will automatically register the jQuery js file.
     *
     * @param string $key the key that identifies the JS code block. If null, it will use
     * $js as the key. If two JS code blocks are registered with the same key, the latter
     * will overwrite the former.
     */
    public function registerJs($js, $position = self::POS_HEAD, $key = null)
    {
        $key = $key ?: md5($js);
        $this->js[$position][$key] = $js;
    }

    /**
     * Registers a JS file.
     * @param string $url the JS file to be registered.
     * @param array $options the HTML attributes for the script tag. A special option
     * named "position" is supported which specifies where the JS script tag should be inserted
     * in a page. The possible values of "position" are:
     *
     * - [[POS_HEAD]]: in the head section
     * - [[POS_BEGIN]]: at the beginning of the body section
     * - [[POS_END]]: at the end of the body section. This is the default value.
     *
     * @param string $key the key that identifies the JS script file. If null, it will use
     * $url as the key. If two JS files are registered with the same key, the latter
     * will overwrite the former.
     */
    public function registerJsFile($url, $options = [], $key = null)
    {
        $url = static::getAlias($url);
        $key = $key ?: $url;

        $position = isset($options['position']) ? $options['position'] : self::POS_END;
        unset($options['position']);
        $this->jsFiles[$position][$key] = $this->renderWrapperTag(Html::jsFile($url, $options), $options);

    }

    /**
     * @var array registered path aliases
     * @see getAlias()
     * @see setAlias()
     */
    public static $aliases = [
        '@rock' => __DIR__,
        '@rock.views' => '@rock/views'
    ];

    /**
     * Translates a path alias into an actual path.
     *
     * The translation is done according to the following procedure:
     *
     * 1. If the given alias does not start with '@', it is returned back without change;
     * 2. Otherwise, look for the longest registered alias that matches the beginning part
     *    of the given alias. If it exists, replace the matching part of the given alias with
     *    the corresponding registered path.
     * 3. Throw an exception or return false, depending on the `$throwException` parameter.
     *
     * For example, by default '@rock' is registered as the alias to the Rock framework directory,
     * say '/path/to/rock'. The alias '@rock/web' would then be translated into '/path/to/rock/web'.
     *
     * If you have registered two aliases '@foo' and '@foo/bar'. Then translating '@foo/bar/config'
     * would replace the part '@foo/bar' (instead of '@foo') with the corresponding registered path.
     * This is because the longest alias takes precedence.
     *
     * However, if the alias to be translated is '@foo/barbar/config', then '@foo' will be replaced
     * instead of '@foo/bar', because '/' serves as the boundary character.
     *
     * Note, this method does not check if the returned path exists or not.
     *
     * @param string  $alias          the alias to be translated.
     * @param array   $dataReplace
     * @param boolean $throwException whether to throw an exception if the given alias is invalid.
     *                                If this is false and an invalid alias is given, false will be returned by this method.
     * @throws \Exception if the alias is invalid while $throwException is true.
     * @return string|boolean the path corresponding to the alias, false if the root alias is not previously registered.
     * @see setAlias()
     */
    public static function getAlias($alias, array $dataReplace = [],  $throwException = true)
    {
        $alias = String::replace($alias, $dataReplace);
        if (strncmp($alias, '@', 1)) {
            // not an alias
            return $alias;
        }
        $delimiter = ObjectHelper::isNamespace($alias) ? '\\' : '/';

        $pos = strpos($alias, $delimiter);
        $root = $pos === false ? $alias : substr($alias, 0, $pos);

        if (isset(static::$aliases[$root])) {
            if (is_string(static::$aliases[$root])) {
                return $pos === false ? static::$aliases[$root] : static::$aliases[$root] . substr($alias, $pos);
            } else {
                foreach (static::$aliases[$root] as $name => $path) {
                    if (strpos($alias . $delimiter, $name . $delimiter) === 0) {
                        return $path . substr($alias, strlen($name));
                    }
                }
            }
        }

        if ($throwException) {
            throw new \Exception("Invalid path alias: $alias");
        } else {
            return false;
        }
    }

    /**
     * Registers a path alias.
     *
     * A path alias is a short name representing a long path (a file path, a URL, etc.)
     * For example, we use '@rock' as the alias of the path to the Rock framework directory.
     *
     * A path alias must start with the character '@' so that it can be easily differentiated
     * from non-alias paths.
     *
     * Note that this method does not check if the given path exists or not. All it does is
     * to associate the alias with the path.
     *
     * Any trailing '/' and '\' characters in the given path will be trimmed.
     *
     * @param string $alias the alias name (e.g. "@rock"). It must start with a '@' character.
     * It may contain the forward slash '/' which serves as boundary character when performing
     * alias translation by [[getAlias()]].
     * @param string $path the path corresponding to the alias. Trailing '/' and '\' characters
     * will be trimmed. This can be
     *
     * - a directory or a file path (e.g. `/tmp`, `/tmp/main.txt`)
     * - a URL (e.g. `http://www.site.com`)
     * - a path alias (e.g. `@rock/base`). In this case, the path alias will be converted into the
     *   actual path first by calling [[getAlias()]].
     *
     * @throws \Exception if $path is an invalid alias.
     * @see getAlias()
     */
    public static function setAlias($alias, $path)
    {
        if (strncmp($alias, '@', 1)) {
            $alias = '@' . $alias;
        }
        $delimiter = ObjectHelper::isNamespace($alias) ? '\\' : '/';

        $pos = strpos($alias, $delimiter);
        $root = $pos === false ? $alias : substr($alias, 0, $pos);
        if ($path !== null) {
            $path = strncmp($path, '@', 1) ? rtrim($path, '\\/') : static::getAlias($path);
            if (!isset(static::$aliases[$root])) {
                if ($pos === false) {
                    static::$aliases[$root] = $path;
                } else {
                    static::$aliases[$root] = [$alias => $path];
                }
            } elseif (is_string(static::$aliases[$root])) {
                if ($pos === false) {
                    static::$aliases[$root] = $path;
                } else {
                    static::$aliases[$root] = [
                        $alias => $path,
                        $root => static::$aliases[$root],
                    ];
                }
            } else {
                static::$aliases[$root][$alias] = $path;
                krsort(static::$aliases[$root]);
            }
        } elseif (isset(static::$aliases[$root])) {
            if (is_array(static::$aliases[$root])) {
                unset(static::$aliases[$root][$alias]);
            } elseif ($pos === false) {
                unset(static::$aliases[$root]);
            }
        }
    }

    /**
     * Defines path aliases.
     * This method calls [[Template::setAlias()]] to register the path aliases.
     * This method is provided so that you can define path aliases when configuring a module.
     * @property array list of path aliases to be defined. The array keys are alias names
     * (must start with '@') and the array values are the corresponding paths or aliases.
     * See [[setAliases()]] for an example.
     * @param array $aliases list of path aliases to be defined. The array keys are alias names
     * (must start with '@') and the array values are the corresponding paths or aliases.
     * For example,
     *
     * ```php
     * [
     *     '@models' => '@app/models', // an existing alias
     *     '@backend' => __DIR__ . '/../backend',  // a directory
     * ]
     * ```
     */
    public static function setAliases(array $aliases)
    {
        foreach ($aliases as $name => $alias) {
            static::setAlias($name, $alias);
        }
    }

    /**
     * Renders the content to be inserted in the head section.
     * The content is rendered using the registered meta tags, link tags, CSS/JS code blocks and files.
     * @return string the rendered content
     */
    protected function renderHeadHtml()
    {
        $lines = [];
        $lines[] = $this->head;
        $lines[] = Html::tag('title', $this->title);
        if (!empty($this->metaTags)) {
            $lines[] = implode("\n", $this->metaTags);
        }

        if (!empty($this->linkTags)) {
            $lines[] = implode("\n", $this->linkTags);
        }
        if (!empty($this->cssFiles[self::POS_HEAD])) {
            $lines[] = implode("\n", $this->cssFiles[self::POS_HEAD]);
        }
        if (!empty($this->css)) {
            $lines[] = implode("\n", $this->css);
        }
        if (!empty($this->jsFiles[self::POS_HEAD])) {
            $lines[] = implode("\n", $this->jsFiles[self::POS_HEAD]);
        }
        if (!empty($this->js[self::POS_HEAD])) {
            $lines[] = Html::script(implode("\n", $this->js[self::POS_HEAD]), ['type' => 'text/javascript']);
        }

        return empty($lines) ? '' : implode("\n", $lines);
    }

    /**
     * Renders the content to be inserted at the beginning of the body section.
     * The content is rendered using the registered JS code blocks and files.
     * @return string the rendered content
     */
    protected function renderBodyBeginHtml()
    {
        $lines = ['<body>'];
        if (!empty($this->jsFiles[self::POS_BEGIN])) {
            $lines[] = implode("\n", $this->jsFiles[self::POS_BEGIN]);
        }
        if (!empty($this->js[self::POS_BEGIN])) {
            $lines[] = Html::script(implode("\n", $this->js[self::POS_BEGIN]), ['type' => 'text/javascript']);
        }

        return empty($lines) ? '' : implode("\n", $lines);
    }

    /**
     * Renders the content to be inserted at the end of the body section.
     * The content is rendered using the registered JS code blocks and files.
     * @return string the rendered content
     */
    protected function renderBodyEndHtml()
    {
        $lines = [];
        if (!empty($this->cssFiles[self::POS_END])) {
            $lines[] = implode("\n", $this->cssFiles[self::POS_END]);
        }

        if (!empty($this->jsFiles[self::POS_END])) {
            $lines[] = implode("\n", $this->jsFiles[self::POS_END]);
        }

        $scripts = [];
        if (!empty($this->js[self::POS_END])) {
            $scripts[] = implode("\n", $this->js[self::POS_END]);
        }

        if (!empty($scripts)) {
            $lines[] = Html::script(implode("\n", $scripts), ['type' => 'text/javascript']);
        }
        $lines[] = '</body>';

        return empty($lines) ? '' : implode("\n", $lines);
    }

    private function renderWrapperTag($value, array $options = [])
    {
        if (empty($options['wrapperTpl'])) {
            return $value;
        }
        $value = $this->replaceParamByPrefix($options['wrapperTpl'], ['output' => $value]);

        return $value;
    }

    /**
     * Callback to replace variables template
     *
     * @param array $matches - array of variables template
     * @throws Exception
     * @return string
     */
    protected function replaceCallback($matches)
    {
        if (!empty($matches['beforeSkip']) && !empty($matches['afterSkip'])) {
            return trim($matches[0], '{!} ');
        }
        // Validate: if count quotes does not parity
        if (!Numeric::isParity(mb_substr_count($matches[0], '`', 'UTF-8'))) {
            return $matches[0];
        }

        $matches[0] = preg_replace_callback(
            '/
                \\s*(?P<sugar> (?!`)\*(?!`) | (?!`)\*\*(?!`) | (?!`)\/(?!`) | (?!`)\%(?!`) |
                \\s+(?!`)mod(?!`)\\s+ | (?!`)\+(?!`) | (?!`)\-(?!`) | (?!`)\|(?!`) | (?!`)\&(?!`) |
                (?!`)\^(?!`) | (?!`)\>\>(?!`) | (?!`)\<\<(?!`) |
                (?!`)\|\|(?!`) | (?!`)\&\&(?!`) | \\s+(?!`)'.$this->_getInlineConditionNames().'(?!`)\\s+ |`\\s+\?\\s+|`\\s+\:\\s+)\\s*`
            /x', [$this, 'replaceSugar'], $matches[0]);
        // Replace `=` tpl mnemonics
        $matches[0] = preg_replace('/`([\!\<\>]?)[\=]+`/', '`$1&#61;`', $matches[0]);
        // Replace `text` to ““text””
        $matches[0] = preg_replace(['/=\\s*\`/', '/\`/'], ['=““', '””'], $matches[0]);
        // Replacement of internal recursion on {{{...}}}
        $i = 0;
        $dataRecursive = [];
        $matches[0] = preg_replace_callback(
            '/\“\“(?:[^\“\”]++|(?R))*\”\”/iu',
            function ($value) use (&$dataRecursive, &$i) {
                $key = '{{{' . $i . '}}}';
                $value = current($value);
                $dataRecursive[$key] = $value;
                $i++;

                return $key;
            },
            $matches[0]
        );
        // Search params is variable of template
        $params = $this->_searchParams($matches[0], $dataRecursive);
        // Search of filters (modifiers)
        $filters = $this->_searchFilters($matches[0], $dataRecursive);
        $matches['name'] = trim($matches['name']);
        // Get cache
        list($cacheKey, $cacheExpire, $cacheTags) = $this->calculateCacheParams($params);
        if (($resultCache = $this->getCache($cacheKey)) !== false) {
            return $resultCache;
        }

        $params = Serialize::unserializeRecursive($params);
        $filters = Serialize::unserializeRecursive($filters);
        $escape = !$matches['escape'];

        // chunk
        if ($matches['type'] === '$') {
            $result = $this->getChunk($matches['name'], $params);

        // data of resource
        } elseif ($matches['type'] === '*') {
            $result = $this->getResource(
                $matches['name'],
                Helper::getValueIsset($params['autoEscape'], $escape)
            );

        // get alias
        } elseif ($matches['type'] === '$$') {
            $result = static::getAlias("@{$matches['name']}");

        // local placeholder
        } elseif ($matches['type'] === '+') {
            $result = $this->getPlaceholder(
                $matches['name'],
                Helper::getValueIsset($params['autoEscape'], $escape)
            );

        // global placeholder
        } elseif ($matches['type'] === '++') {
            $result = $this->getPlaceholder(
                $matches['name'],
                Helper::getValueIsset($params['autoEscape'], $escape),
                true
            );

        // extensions
        } elseif ($matches['type'] === '#') {
            $result = $this->getExtension($matches['name'], $params, Helper::getValueIsset($params['autoEscape'], $escape));

        // snippet
        } elseif (empty($matches['type'])) {
            $result = $this->getSnippet($matches['name'], $params, $escape);
        } else {
            return $matches[0];
        }

        // Make a filter
        if (!empty($filters)) {
            $result = $this->makeFilter($result, $filters);
        }

        if (!is_scalar($result) && !empty($result)) {
            throw new Exception('Wrong type is var: ' . Json::encode($result));
            //$result = null;
        }

        // Set cache
        $this->setCache(
            $cacheKey,
            $result,
            $cacheExpire,
            $cacheTags
        );

        return $result;
    }

    private static $_conditionNames = [];

    protected function replaceSugar($matches)
    {
        $matches['sugar'] = trim($matches['sugar'], " `\n\t\r");

        switch ($matches['sugar']) {
            case '||':
                return "\n:empty\n&is=`@INLINE";
            case '&&':
                return "\n:notEmpty\n&is=`@INLINE";
            case array_key_exists($matches['sugar'], $this->_getConditionNames()):
                return "\n:if\n&{$matches['sugar']}=`";
            case '?':
                return "`\n&then=`";
            case ':':
                return "`\n&else=`";
            case '+':
                return "\n:formula\n&operator=`+`\n&operand=`";
            case '-':
                return "\n:formula\n&operator=`-`\n&operand=`";
            case '*':
                return "\n:formula\n&operator=`*`\n&operand=`";
            case '/':
                return "\n:formula\n&operator=`/`\n&operand=`";
            case '**':
                return "\n:formula\n&operator=`**`\n&operand=`";
            case '%':
            case 'mod':
                return "\n:formula\n&operator=`%`\n&operand=`";
            case '|':
                return "\n:formula\n&operator=`|`\n&operand=`";
            case '&':
                return "\n:formula\n&operator=`&`\n&operand=`";
            case '^':
                return "\n:formula\n&operator=`^`\n&operand=`";
            case '<<':
                return "\n:formula\n&operator=`<<`\n&operand=`";
            case '>>':
                return "\n:formula\n&operator=`>>`\n&operand=`";
            default:
                return $matches[0];
        }
    }

    private function _getConditionNames()
    {
        if (empty(static::$_conditionNames)) {
            foreach (ConditionFilter::$conditionNames as $names) {
                static::$_conditionNames = array_merge(static::$_conditionNames, $names);
            }
            static::$_conditionNames = array_flip(static::$_conditionNames);
        }

        return static::$_conditionNames;

    }

    private static $_inlineConditionNames;

    private function _getInlineConditionNames()
    {
        if (!isset(static::$_inlineConditionNames)) {
            static::$_inlineConditionNames = implode('\\s+|', array_flip($this->_getConditionNames()));
        }

        return static::$_inlineConditionNames;
    }

    /**
     * Search placeholders is variable of template
     * ?<name>=<value>
     *
     * @param string $value
     * @param array  $dataRecursive
     * @return array
     */
    private function _searchParams($value, array $dataRecursive)
    {
        preg_match_all(
            '/
                \?
                (?P<name>\\w+)                  # name param
                \\s*\=\\s*
                (?P<value>\{{3}\\d+\}{3}\\s*)*  # value param
                [^\?\[\]]*                      # restriction: is not "?" and not "[" "]"
            /iux',
            $value,
            $matches
        );
        $i = 0;
        $j = 0;
        $result = [];
        if (!isset($matches['name'])) {
            return $result;
        }
        foreach ($matches['name'] as $nameParams) {
            if (!isset($matches['value'][$i]) || !isset($nameParams)) {
                continue;
            }
            $valueParams = mb_substr($dataRecursive[trim($matches['value'][$i])], 2, -2, 'UTF-8');
            // Search prefix
            $valueParams = $this->_searchPrefix($nameParams, $valueParams);
            // to type
            if (is_string($valueParams)) {
                $valueParams = Helper::toType(str_replace('&#61;', '=', $valueParams));
            }
            // If multiple placeholders with the same name, then create to array
            if (isset($result[$nameParams])) {
                if (is_array($result[$nameParams])) {
                    $result[$nameParams][] = $valueParams;
                } else {
                    $result[$nameParams] = [$result[$nameParams], $valueParams];
                }
                ++$j;
            } else {
                $result[$nameParams] = $valueParams;
            }
            ++$i;
        }

        return $result;
    }

    /**
     * Validate: not replace is @INLINE prefix exists or parameters then/else
     *
     * @param string $name  - name of param
     * @param string $value - value of param
     * @return string
     */
    private function _searchPrefix($name, $value)
    {
        $matches = [];
        // Validate: not replace is @INLINE exists or parameters then/else
        preg_match('/\@(?P<prefix>(?:INLINE|FILE)).+/s', $value, $matches);
        if ((!isset($matches['prefix']) || strtolower($matches['prefix']) !== 'inline')
            && !in_array($name, ['then', 'else'], true)
        ) {
            return $this->replace($value);
        }

        return $value;
    }

    /**
     * Search of filters (modifiers)
     *
     * @param string $value
     * @param array  $dataRecursive
     * @return array
     */
    private function _searchFilters($value, array $dataRecursive)
    {
        // Search of filters (modifiers)
        preg_match_all(
            '/
                \:
                (?P<name>\\w+)											# name of filter
                (?P<value>(?:\s*\&\\w+\\s*\=\\s*\{{3}\\d+\}{3}\\s*)*)	# variables of filter
                [^\:\[\]]*												# restriction: is not ":" and not "[" "]"
            /iux',
            $value,
            $matches
        );
        $i = 0;
        $result = [];
        if (!isset($matches['name'])) {
            return $result;
        }
        foreach ($matches['name'] as $value) {
            if (!isset($matches['value'][$i]) || !isset($value)) {
                continue;
            }
            $result[$value][] = $this->_searchParamsByFilters($matches, $dataRecursive, $i);
            ++$i;
        }

        return $result;
    }

    private function _searchParamsByFilters(array $matches, array $array_recursive, $i)
    {
        // Parsing params of filter
        preg_match_all(
            '/
                \&
                (?P<names>\\w+)			    # name variable of filter
                \\s*\=\\s*
                (?P<values>\{{3}\\d+\}{3})	# value variable of filter
                [^\&]*
            /iux',
            $matches['value'][$i],
            $params
        );
        $j = 0;
        $result = [];
        if (isset($params['names'])) {
            foreach ($params['names'] as $name) {
                if (!isset($params['values'][$j]) || !isset($name)) {
                    continue;
                }
                $result[$name] = mb_substr($array_recursive[trim($params['values'][$j])], 2, -2, 'UTF-8');
                // Search prefix
                $result[$name] = $this->_searchPrefix($name, $result[$name]);
                if (is_string($result[$name])) {
                    $result[$name] = Helper::toType($result[$name]);
                }
                ++$j;
            }
        }

        return $result;
    }

    /**
     * @param string     $name   - name of extension
     * @param array $params - params
     * @throws Exception
     * @return mixed
     */
    private function _getExtensionInternal($name = null, array $params = [])
    {
        if (!strstr($name, '.') ||
            (!$names = explode('.', $name)) ||
            count($names) < 2
        ) {
            return null;
        }
        $name = strtolower($names[0]);
        if ($this->extensions[$name] instanceof \Closure) {
            unset($names[0]);
            return call_user_func($this->extensions[$name], array_values($names), $params, $this);
        }

        return null;
    }

    protected function escape($value, $const = true)
    {
        if (!isset($value)) {
            return null;
        }
        if ($const === true) {
            $const = $this->autoEscape;
        }
        if ($const === false) {
            return $value;
        }
        if ($const & self::TO_TYPE) {
            $value = Helper::toType($value);
        }
        if (!is_string($value)) {
            return $value;
        }
        if ($const & self::STRIP_TAGS) {
            $value = strip_tags($value);
        }
        if ($const & self::ESCAPE) {
            $value = String::encode($value);
        }

        return $value;
    }

    protected function calculateCacheParams(array &$params = [])
    {
        if (empty($params) || empty($params['cacheKey'])) {
            unset($params['cacheKey'], $params['cacheExpire'], $params['cacheTags']);
            return [null, null, null];
        }
        $cacheKey = $params['cacheKey'];
        $cacheExpire = Helper::getValueIsset($params['cacheExpire'], 0);
        $cacheTags = Helper::getValue($params['cacheTags']);
        unset($params['cacheKey'], $params['cacheExpire'], $params['cacheTags']);

        return [$cacheKey, $cacheExpire, $cacheTags];
    }

    /**
     * Get the content from the cache
     * @param string|null $key
     * @return bool
     */
    protected function getCache($key = null)
    {
        if (!isset($this->cache)) {
            return false;
        }
        if ($this->cache instanceof \rock\cache\CacheInterface && isset($key) &&
            ($returnCache = $this->cache->get($key)) !== false
        ) {
            if (is_array($returnCache) && isset($returnCache['placeholders'], $returnCache['result'])) {
                $this->addMultiPlaceholders($returnCache['placeholders'], true);
                $returnCache = $returnCache['result'];
            }
            return $returnCache;
        }

        return false;
    }

    /**
     * Caching template
     *
     * @param null $key
     * @param null $value
     * @param int  $expire
     * @param null $tags
     */
    protected function setCache($key = null, $value = null, $expire = 0, $tags = null)
    {
        if (!isset($this->cache)) {
            return;
        }

        if ($this->cache instanceof \rock\cache\CacheInterface && isset($key)) {
            if (!empty($this->cachePlaceholders)) {
                $result = $value;
                $value = [];
                $value['result'] = $result;
                $value['placeholders'] = $this->cachePlaceholders;

            }
            $this->cache->set(
                $key,
                $value,
                $expire,
                is_string($tags) ? explode(',', $tags) : $tags
            );
            $this->cachePlaceholders = [];
        }
    }
}