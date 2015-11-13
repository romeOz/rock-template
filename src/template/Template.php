<?php
namespace rock\template;

use rock\base\Alias;
use rock\cache\CacheInterface;
use rock\events\Event;
use rock\events\EventsInterface;
use rock\events\EventsTrait;
use rock\helpers\ArrayHelper;
use rock\helpers\FileHelper;
use rock\helpers\Helper;
use rock\helpers\Instance;
use rock\helpers\Json;
use rock\helpers\NumericHelper;
use rock\helpers\Serialize;
use rock\helpers\StringHelper;
use rock\i18n\i18n;
use rock\request\Request;
use rock\snippets\Snippet;
use rock\template\filters\ConditionFilter;

/**
 * Template engine
 * @package rock\template
 */
class Template implements EventsInterface, \ArrayAccess
{
    use EventsTrait;

    const ENGINE_ROCK = 1;
    const ENGINE_PHP = 2;

    const SANITIZE_DISABLE = 0;
    const SANITIZE_ESCAPE = 1;
    const SANITIZE_STRIP_TAGS = 2;
    const SANITIZE_TO_TYPE = 4;

    /**
     * @event Event an event that is triggered by {@see \rock\template\Template::beginPage()}.
     */
    const EVENT_BEGIN_PAGE = 'beginPage';
    /**
     * @event Event an event that is triggered by {@see \rock\template\Template::endPage()}.
     */
    const EVENT_END_PAGE = 'endPage';

    /**
     * @event Event an event that is triggered by {@see \rock\template\Template::beginBody()}.
     */
    const EVENT_BEGIN_BODY = 'beginBody';
    /**
     * @event Event an event that is triggered by {@see \rock\template\Template::endBody()}.
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
     * Mapping extensions with engines.
     *
     * @var array
     */
    protected $engines = [
        self::ENGINE_ROCK => 'html',
        self::ENGINE_PHP => 'php',
    ];
    /**
     * Use of engine default.
     *
     * @var int
     */
    protected $defaultEngine = self::ENGINE_ROCK;
    /**
     * List filters.
     *
     * @var array
     */
    protected $filters = [];
    /**
     * List snippets.
     * @var array
     */
    protected $snippets = [];
    /**
     * Collection extensions.
     *
     * @var array
     */
    protected $extensions = [];
    /**
     * Handler for replace link: `[[~link]]`
     * @var callable
     */
    protected $handlerLink;
    /**
     * Mode sanitize.
     *
     * @var int
     */
    protected $sanitize = self::SANITIZE_ESCAPE;
    /**
     * Enable automatic serialization value.
     *
     * @var bool
     */
    protected $autoSerialize = true;
    /**
     * List CSS code blocks.
     * @var array the registered CSS code blocks.
     * @see registerCss()
     */
    public $css = [];
    /**
     * List CSS files.
     * @var array the registered CSS files.
     * @see registerCssFile()
     */
    public $cssFiles = [];
    /**
     * List JavaScript code blocks.
     * @var array the registered JS code blocks
     * @see registerJs()
     */
    public $js = [];
    /**
     * List JavaScript files.
     * @var array the registered JS files.
     * @see registerJsFile()
     */
    public $jsFiles = [];
    /**
     * Instance Rock Cache.
     * @var \rock\cache\CacheInterface|string|array
     */
    public $cache = 'cache';
    /**
     * Instance Rock Request.
     * @var Request
     */
    public $request = 'request';
    /**
     * Instance Controller where render template.
     * @var object
     */
    protected $context;
    /**
     * Enable throw exception. When {@see \rock\template\Template::addMultiPlaceholders()} and {@see \rock\template\Template::removeMultiPlaceholders()}.
     * @var bool
     */
    protected $throwException = false;
    /**
     * List root-paths to view directories.
     * @var array
     */
    protected $chroots = ['@template.views'];
    /**
     * Current locale.
     * @var string
     */
    protected $locale = 'en';
    /**
     * Head layout.
     * @var string
     */
    protected $head = "<!DOCTYPE html>\n<html>";
    /**
     * The page title.
     * @var string
     */
    protected $title = '';
    /**
     * @var array the registered meta tags.
     * @see registerMetaTag()
     */
    protected $metaTags = [];
    /**
     * @var array the registered link tags.
     * @see registerLinkTag()
     */
    protected $linkTags = [];
    /**
     * Body layout.
     * @var string
     */
    protected $body = '<body>';
    /**
     * List placeholders.
     * @var array
     */
    protected $placeholders = [];
    /**
     * List constants.
     * @var array
     */
    protected static $constants = [];
    /**
     * Current path to chunk/layout.
     * @var string
     */
    protected $path;
    /**
     * List scopes.
     * @var Template[]
     */
    protected $scopes = [];
    public $cachePlaceholders = [];
    private static $_sanitizeCache = [];
    private static $_conditionNames = [];
    private static $_inlineConditionNames;

    public function init()
    {
        // default path to view
        if (!Alias::existsAlias('template.views')) {
            Alias::setAlias('template.views', dirname(__DIR__) . '/views');
        }
        $this->request = Instance::ensure($this->request, '\rock\request\Request');
        $this->cache = Instance::ensure($this->cache, null, [], false);
        $this->snippets = array_merge($this->defaultSnippets(), $this->snippets);
        $this->filters = array_merge($this->defaultFilters(), $this->filters);
    }

    /**
     * Sets a locale.
     * @param string $locale
     */
    public function setLocale($locale)
    {
        $this->locale = strtolower($locale);
    }

    /**
     * Sets a head.
     * @param string $head
     * @return $this
     */
    public function setHead($head)
    {
        $this->head = $head;
        return $this;
    }

    /**
     * Sets a title page.
     * @param string $title
     * @return $this
     */
    public function setTitle($title)
    {
        $this->title = $title;
        return $this;
    }

    /**
     * Sets a list meta tags.
     * @param array $meta
     * @return $this
     */
    public function setMetaTags($meta)
    {
        $this->metaTags = $meta;
        return $this;
    }

    /**
     * Sets a list link tags.
     * @param array $links
     * @return $this
     */
    public function setLinkTags($links)
    {
        $this->linkTags = $links;
        return $this;
    }

    /**
     * Sets a body.
     * @param string $body
     * @return $this
     */
    public function setBody($body)
    {
        $this->body = $body;
        return $this;
    }

    /**
     * Registers a constants.
     * @param array $constants list constants.
     * @return $this
     */
    public function setConstants(array $constants)
    {
        static::$constants = $constants;
        return $this;
    }

    /**
     * Registers a snippets.
     * @param array $snippets list snippets.
     * @return $this
     */
    public function setSnippets(array $snippets)
    {
        $this->snippets = array_merge($this->snippets, $snippets);
        return $this;
    }

    /**
     * Registers a filters.
     * @param array $filters list filters.
     * @return $this
     */
    public function setFilters(array $filters)
    {
        $this->filters = array_merge($this->filters, $filters);
        return $this;
    }

    /**
     * Registers a extensions.
     * @param array $extensions list extensions.
     * @return $this
     */
    public function setExtensions(array $extensions)
    {
        $this->extensions = array_merge($this->extensions, $extensions);
        return $this;
    }

    /**
     * Registers a handler link.
     * @param callable $handler
     * @return $this
     */
    public function setHandlerLink(callable $handler)
    {
        $this->handlerLink = $handler;
        return $this;
    }

    /**
     * Sets a default engine.
     * @param int $engine
     * @return $this
     */
    public function setDefaultEngine($engine)
    {
        $this->defaultEngine = $engine;
        return $this;
    }

    /**
     * Sets a sanitize mode.
     * @param bool $mode
     * @return $this
     */
    public function setSanitize($mode)
    {
        $this->sanitize = $mode;
        return $this;
    }

    /**
     * Returns sanitize mode.
     * @return int
     */
    public function getSanitize()
    {
        return $this->sanitize;
    }

    /**
     * Enable auto-serialize.
     * @param bool $enable
     * @return $this
     */
    public function setAutoSerialize($enable)
    {
        $this->autoSerialize = $enable;
        return $this;
    }

    /**
     * Enable throw exception. When {@see \rock\template\Template::addMultiPlaceholders()} and {@see \rock\template\Template::removeMultiPlaceholders()}.
     * @param bool $enable
     * @return $this
     */
    public function setThrowException($enable)
    {
        $this->throwException = $enable;
        return $this;
    }

    /**
     * Sets a list root-paths to view directories.
     * @param array $paths list absolute paths.
     * @return $this
     */
    public function setChroots(array $paths)
    {
        $this->chroots = $paths;
        return $this;
    }

    /**
     * Sets a instance Controller where render template.
     * @param object $controller
     * @return $this
     */
    public function setContext($controller)
    {
        $this->context = $controller;
        return $this;
    }

    /**
     * Returns a instance Controller where render template.
     * @return object
     */
    public function getContext()
    {
        return $this->context;
    }

    /**
     * Rendering layout.
     * @param string $path path to layout
     * @param array $placeholders
     * @param object|null $context
     * @param bool $isAjax
     * @return string
     * @throws TemplateException
     */
    public function render($path, array $placeholders = [], $context = null, $isAjax = false)
    {
        if (isset($context)) {
            $this->setContext($context);
        }
        $this->scopes[spl_object_hash($this)] = $this;
        list($cacheKey, $cacheExpire, $cacheTags) = $this->calculateCacheParams($placeholders);
        // Get cache
        if (($resultCache = $this->getCacheContent($cacheKey)) !== false) {

            return $resultCache;
        }
        $result = $this->renderInternal($path, $placeholders);
        if (!$isAjax) {
            $result = implode("\n", [$this->beginPage(), $this->beginBody(), $result, $this->endBody(), $this->endPage()]);
        }

        // Set cache
        $this->setCacheContent($cacheKey, $result, $cacheExpire, $cacheTags ?: []);

        return $result;
    }

    /**
     * Replace variables template (chunk, snippet...).
     * @param string $code current template with variables template.
     * @param array $placeholders array placeholders of variables template.
     * @return string
     */
    public function replace($code, array $placeholders = [])
    {
        $code = Helper::toType($code);
        if (empty($code) || !is_string($code)) {
            return $code;
        }
        if (!empty($placeholders)) {
            $this->addMultiPlaceholders($placeholders);
        }
        /*
         * Remove tpl-comment
         * ```
         * {* Comment about *}
         * ```
         */
        $code = preg_replace('/\{\*.*?\*\}/is', "", $code);
        $code = preg_replace_callback(
            '/
                (?P<beforeSkip>\{\!\\s*)?\[\[
                (?P<sanitizeDisable>\!)?
                (?P<type>[\#\%\~\$]?|\+{1,2}|@{1,2})					# search type of variable template
                (?P<name>@?[\\w\-\/\\\.\$]+)							# name of variable template [\w, -, \, .]
                (?:[^\[\]]++ | \[(?!\[) | \](?!\]) | (?R))*		# possible recursion
                \]\](?P<afterSkip>\\s*\!\})?
            /iux',
            [$this, 'replaceCallback'],
            $code
        );
        return Helper::toType($code);
    }

    /**
     * Returns placeholder by name.
     * @param string|array $name name of placeholder.
     * @param int $sanitize
     * @return mixed
     * @throws TemplateException
     */
    public function getPlaceholder($name, $sanitize = null)
    {
        list($name, $placeholders, $template) = $this->getParentPlaceholder($name);
        if ($template === false) {
            if ($this->throwException) {
                if (is_array($name)) {
                    $name = implode('.', $name);
                }
                throw new TemplateException("Unknown scope: {$name}");
            }
            return null;
        }
        return $this->sanitize(ArrayHelper::getValue($placeholders, $name), $sanitize);
    }

    /**
     * Returns a placeholder by name.
     * @param string|array $name name of placeholder.
     * @return mixed
     */
    public function offsetGet($name)
    {
        if ($this->existsPlaceholder($name)) {
            return $this->getPlaceholder($name);
        }
        return null;
    }

    /**
     * Returns all placeholders.
     * @param string|array|null $parent $root or $parent.
     * @param int $sanitize
     * @param array $only
     * @param array $exclude
     * @return array
     * @throws TemplateException
     */
    public function getAllPlaceholders($parent = null, $sanitize = null, array $only = [], array $exclude = [])
    {
        if (isset($parent)) {
            list(, , $template) = $this->getParentPlaceholder($parent);
        }

        if (!isset($template)) {
            $template = $this;
        }
        if ($template === false) {
            if ($this->throwException) {
                if (is_array($parent)) {
                    $parent = implode('.', $parent);
                }
                throw new TemplateException("Unknown scope: {$parent}");
            }
            return [];
        }
        return $this->sanitize(ArrayHelper::only($template->placeholders, $only, $exclude), $sanitize);
    }

    /**
     * Adding placeholder.
     * @param string $name name of placeholder
     * @param mixed $value value
     * @param bool $recursive
     * @throws TemplateException
     */
    public function addPlaceholder($name, $value = null, $recursive = false)
    {
        list($name, , $template) = $this->getParentPlaceholder($name);
        if (!isset($template)) {
            $template = $this;
        }
        if ($template === false) {
            if ($this->throwException) {
                throw new TemplateException("Unknown scope: {$name}");
            }
            return;
        }
        $template->placeholders[$name] =
            isset($template->placeholders[$name]) && is_array($template->placeholders[$name])
                ? $this->_merge($template->placeholders[$name], (array)$value, $recursive)
                : $value;
    }

    /**
     * Adding placeholders.
     * @param array $placeholders placeholders
     * @param bool $recursive
     * @return mixed
     * @throws TemplateException
     */
    public function addMultiPlaceholders(array $placeholders, $recursive = false)
    {
        foreach ($placeholders as $name => $placeholder) {
            $this->addPlaceholder($name, $placeholder, $recursive);
        }
    }

    /**
     * Adding placeholder.
     * @param string $name name of placeholder.
     * @param        $value
     */
    public function offsetSet($name, $value)
    {
        $this->addPlaceholder($name, $value);
    }

    /**
     * Exists placeholder.
     * @param string $name name of placeholder.
     * @return bool
     */
    public function existsPlaceholder($name)
    {
        return $this->getPlaceholder($name, false) !== null;
    }

    /**
     * Exists placeholder.
     * @param string $name name of placeholder.
     * @return bool
     */
    public function offsetExists($name)
    {
        return $this->existsPlaceholder($name);
    }

    /**
     * Removing placeholder.
     * @param string $name name of placeholder.
     * @throws TemplateException
     */
    public function removePlaceholder($name)
    {
        if (empty($name)) {
            return;
        }
        $_name = $name;
        list($name, , $template) = $this->getParentPlaceholder($name);
        if (!isset($template)) {
            $template = $this;
        }

        if ($template === false) {
            if ($this->throwException) {
                throw new TemplateException("Unknown scope: {$name}");
            }
            return;
        }
        // removes all placeholders by scope
        if (empty($name)) {
            $this->removeAllPlaceholders($_name);
            return;
        }
        unset($template->placeholders[$name]);
    }

    /**
     * Removing placeholder.
     * @param string $name name of placeholder.
     */
    public function offsetUnset($name)
    {
        $this->removePlaceholder($name);
    }

    /**
     * Removing multi-placeholders.
     * @param array $names
     * @throws TemplateException
     */
    public function removeMultiPlaceholders(array $names)
    {
        foreach ($names as $name) {
            $this->removePlaceholder($name);
        }
    }

    /**
     * Removing all placeholders
     * @param string|array|null $parent $root or $parent
     * @throws TemplateException
     */
    public function removeAllPlaceholders($parent = null)
    {
        if (isset($parent)) {
            list(, , $template) = $this->getParentPlaceholder($parent);
        }

        if (!isset($template)) {
            $template = $this;
        }
        if ($template === false) {
            if ($this->throwException) {
                if (is_array($parent)) {
                    $parent = implode('.', $parent);
                }
                throw new TemplateException("Unknown scope: {$parent}");
            }
            return;
        }
        $template->placeholders = [];
    }

    /**
     * Finds a placeholders.
     *
     * ```php
     * (new \rock\Template)->findPlaceholders(['foo', 'bar' => 'text']); // ['foo' => 'text', 'bar' => 'text']
     * ```
     *
     * @param array $placeholders
     * @param bool $recursive
     * @return array
     * @throws TemplateException
     */
    public function findPlaceholders(array $placeholders = [], $recursive = false)
    {
        if (empty($placeholders)) {
            return [];
        }
        $result = [];
        foreach ($placeholders as $name => $value) {
            if (is_int($name)) {

                if ($this->existsPlaceholder($value)) {

                    $result[$this->_prepareNamePlaceholder($value)] = $this->getPlaceholder($value, false);
                } elseif ($recursive) {
                    /** @var static $scope */
                    foreach (array_reverse($this->scopes, true) as $scope) {
                        if ($scope->existsPlaceholder($value)) {
                            $result[$this->_prepareNamePlaceholder($value)] = $scope->getPlaceholder($value);
                            break;
                        }
                    }
                }
                continue;
            }
            $result[$name] = $value;
        }

        return $result;
    }

    /**
     * Returns a constant.
     * @param string|array $name name of constant
     * @param int $sanitize
     * @return mixed
     */
    public function getConst($name, $sanitize = null)
    {
        return $this->sanitize(ArrayHelper::getValue(static::$constants, $name), $sanitize);
    }

    /**
     * Adds a constant.
     * @param string $name name of constant
     * @param mixed $value value
     * @param bool $recursive
     * @param bool $changeable
     * @throws TemplateException
     */
    public function addConst($name, $value = null, $recursive = false, $changeable = false)
    {
        if ($this->existsConst($name) && !$changeable) {
            if (is_array($name)) {
                $name = implode('.', $name);
            }
            throw new TemplateException("Constant is defined: {$name}");
        }
        static::$constants[$name] =
            isset(static::$constants[$name]) && is_array(static::$constants[$name])
                ? $this->_merge(static::$constants[$name], (array)$value, $recursive)
                : $value;
    }

    /**
     * Exists constant.
     * @param string|array $name name of constant.
     * @return bool
     */
    public function existsConst($name)
    {
        return $this->getConst($name, false) !== null;
    }

    /**
     * Sanitizing value.
     * @param mixed $value
     * @param int $mode
     * @return mixed
     */
    public function sanitize($value, $mode = null)
    {
        if ($mode === null) {
            $mode = $this->sanitize;
        }
        if (is_array($value)) {
            $hash = md5(json_encode($value));
            /* Optimize lazy loading */
            if (isset(static::$_sanitizeCache[$hash])) {
                return static::$_sanitizeCache[$hash];
            }

            return static::$_sanitizeCache[$hash] =
                ArrayHelper::map(
                    $value,
                    function ($value) use ($mode) {
                        return $this->sanitizeValue($value, $mode);
                    },
                    true
                );
        }

        return $this->sanitizeValue($value, $mode);
    }

    protected function sanitizeValue($value, $mode)
    {
        if (!isset($value)) {
            return null;
        }
        if ($mode === self::SANITIZE_DISABLE) {
            return $value;
        }
        if ($mode & self::SANITIZE_TO_TYPE) {
            $value = Helper::toType($value);
        }
        if (!is_string($value)) {
            return $value;
        }
        if ($mode & self::SANITIZE_STRIP_TAGS) {
            $value = strip_tags($value);
        }
        if ($mode & self::SANITIZE_ESCAPE) {
            $value = StringHelper::encode($value);
        }

        return $value;
    }

    /**
     * Rendering chunk.
     * @param string $path path to chunk.
     * @param array $placeholders list placeholders
     * @return string
     */
    public function getChunk($path, array $placeholders = [])
    {
        $template = clone $this;
        $template->scopes[spl_object_hash($this)] = $this;
        $template->removeAllPlaceholders();
        list($cacheKey, $cacheExpire, $cacheTags) = $template->calculateCacheParams($placeholders);
        // Get cache
        if (($resultCache = $template->getCacheContent($cacheKey)) !== false) {
            return $resultCache;
        }
        $result = $template->renderInternal($path, $placeholders);
        // Set cache
        $template->setCacheContent($cacheKey, $result, $cacheExpire, $cacheTags ?: []);

        return $result;
    }

    /**
     * Exists chunk.
     * @param string $path path to chunk.
     * @return bool
     */
    public function existsChunk($path)
    {
        $path = Alias::getAlias($path, ['lang' => $this->locale]);
        if (!pathinfo($path, PATHINFO_EXTENSION)) {
            $path .= '.' . $this->engines[$this->defaultEngine];
        }

        return file_exists($path);
    }

    /**
     * Returns a snippet by name.
     * @param string|\rock\snippets\Snippet $snippet name of
     *                                           snippet/instance @see \rock\base\Snippet
     * @param array $params params
     * @param int $sanitize
     * @return mixed
     */
    public function getSnippet($snippet, array $params = [], $sanitize = null)
    {
        $template = clone $this;
        $template->scopes[spl_object_hash($this)] = $this;
        $template->removeAllPlaceholders();
        $result = $template->getSnippetInternal($snippet, $params, $sanitize);
        $this->cachePlaceholders = $template->cachePlaceholders;

        return $result;
    }

    /**
     * Returns a extansion by name.
     * @param string $name name of extension
     * @param array $params
     * @param int $sanitize
     * @return mixed
     */
    public function getExtension($name, array $params = [], $sanitize = null)
    {
        $result = $this->_getExtensionInternal($name, $params);
        if (!empty($params)) {
            $this->removePlaceholder('params');
        }

        return $this->sanitize($result, $sanitize);
    }

    /**
     * Make filter (modifier).
     * @param string $value value
     * @param array $filters array of filters with params
     * @throws TemplateException
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
                    $filterParams = $this->filters[$method];
                    $class = $filterParams['class'];
                    $method = Helper::getValue($filterParams['method'], $method);
                    unset($filterParams['class'], $filterParams['method']);
                    $value = call_user_func([$class, $method], $value, array_merge($filterParams, $_params), $this);
                } elseif (function_exists($method)) {
                    $value = call_user_func_array($method, array_merge([$value], $_params));
                } else {
                    throw new TemplateException(TemplateException::UNKNOWN_FILTER, ['name' => $method]);
                }
            }
        };

        return $value;
    }

    /**
     * Replaces a inline tpl.
     * @param string $value value
     * @param array $placeholders
     * @return string
     */
    public function replaceByPrefix($value, array $placeholders = [])
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
     * Returns name of prefix.
     *
     * @param string $value
     * @return array|null
     */
    public function getNamePrefix($value)
    {
        if (empty($value)) {
            return null;
        }
        preg_match('/(?:\@(?P<prefix>INLINE))?(?P<value>.+)/is', $value, $matches);

        return [
            'prefix' => Helper::getValue($matches['prefix']),
            'value' => Helper::getValue($matches['value'])
        ];
    }

    /**
     * Removing prefix by param.
     * @param string $value value of param
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
     * Marks the beginning of a page.
     */
    public function beginPage()
    {
        Event::trigger($this, self::EVENT_BEGIN_PAGE);

        return $this->renderHeadHtml();
    }

    /**
     * Marks the beginning of an HTML body section.
     */
    public function beginBody()
    {
        Event::trigger($this, self::EVENT_BEGIN_BODY);

        return $this->renderBodyBeginHtml();
    }

    /**
     * Renders the content to be inserted at the beginning of the body section.
     * The content is rendered using the registered JS code blocks and files.
     *
     * @return string the rendered content
     */
    protected function renderBodyBeginHtml()
    {
        $lines = [$this->body];
        if (!empty($this->jsFiles[self::POS_BEGIN])) {
            $lines[] = implode("\n", $this->jsFiles[self::POS_BEGIN]);
        }
        if (!empty($this->js[self::POS_BEGIN])) {
            $lines[] = Html::script(implode("\n", $this->js[self::POS_BEGIN]), ['type' => 'text/javascript']);
        }

        return empty($lines) ? '' : implode("\n", $lines);
    }

    /**
     * Marks the ending of an HTML body section.
     */
    public function endBody()
    {
        Event::trigger($this, self::EVENT_END_BODY);

        return $this->renderBodyEndHtml();
    }

    /**
     * Marks the ending of an HTML page.
     * @return string
     */
    public function endPage()
    {
        Event::trigger($this, self::EVENT_END_PAGE);
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
        static::$constants = [];
        $this->placeholders = [];
    }

    /**
     * Registers a meta tag.
     * @param array $options the HTML attributes for the meta tag.
     * @param string $key the key that identifies the meta tag. If two meta tags are registered
     *                        with the same key, the latter will overwrite the former. If this is null, the new meta tag
     *                        will be appended to the existing ones.
     */
    public function registerMetaTag($options, $key = null)
    {
        if ($key === null) {
            $this->metaTags[] = $this->renderWrapperTag(Html::tag('meta', '', $options), $options);
        } else {
            $this->metaTags[$key] = $this->renderWrapperTag(Html::tag('meta', '', $options), $options);
        }
    }

    protected function renderWrapperTag($value, array $options = [])
    {
        if (empty($options['wrapperTpl'])) {
            return $value;
        }
        $value = $this->replaceByPrefix($options['wrapperTpl'], ['output' => $value]);

        return $value;
    }

    /**
     * Registers a link tag.
     * @param array $options the HTML attributes for the link tag.
     * @param string $key the key that identifies the link tag. If two link tags are registered
     *                        with the same key, the latter will overwrite the former. If this is null, the new link tag
     *                        will be appended to the existing ones.
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
     *                        $css as the key. If two CSS code blocks are registered with the same key, the latter
     *                        will overwrite the former.
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
     *                        $url as the key. If two CSS files are registered with the same key, the latter
     *                        will overwrite the former.
     */
    public function registerCssFile($url, $options = [], $key = null)
    {
        $key = $key ?: $url;
        if (is_array($key)) {
            $key = current($key);
        }
        $position = isset($options['position']) ? $options['position'] : self::POS_HEAD;
        unset($options['position']);
        $this->cssFiles[$position][$key] = $this->renderWrapperTag(Html::cssFile($url, $options), $options);
    }

    /**
     * Registers a JS code block.
     *
     * @param string $js the JS code block to be registered
     * @param integer $position the position at which the JS script tag should be inserted
     *                          in a page. The possible values are:
     *
     * - `POS_HEAD`: in the head section
     * - `POS_BEGIN`: at the beginning of the body section
     * - `POS_END`: at the end of the body section
     *
     * @param string $key the key that identifies the JS code block. If null, it will use
     *                          $js as the key. If two JS code blocks are registered with the same key, the latter
     *                          will overwrite the former.
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
     *                        named "position" is supported which specifies where the JS script tag should be inserted
     *                        in a page. The possible values of "position" are:
     *
     * - `POS_HEAD`: in the head section
     * - `POS_BEGIN`: at the beginning of the body section
     * - `POS_END`: at the end of the body section. This is the default value.
     *
     * @param string $key the key that identifies the JS script file. If null, it will use
     *                        $url as the key. If two JS files are registered with the same key, the latter
     *                        will overwrite the former.
     */
    public function registerJsFile($url, $options = [], $key = null)
    {
        $key = $key ?: $url;
        if (is_array($key)) {
            $key = current($key);
        }
        $position = isset($options['position']) ? $options['position'] : self::POS_END;
        unset($options['position']);
        $this->jsFiles[$position][$key] = $this->renderWrapperTag(Html::jsFile($url, $options), $options);
    }

    /**
     * @param string $path path to layout/chunk.
     * @param array $placeholders
     * @throws TemplateException
     * @return string
     */
    protected function renderInternal($path, array $placeholders = [])
    {
        $path = $this->normalizePath($path);
        if (!$this->checkPath($path)) {
            throw new TemplateException("The requested view '{$path}' is beyond the scope of chroot");
        }
        $this->path = $path;
        if (current(array_keys($this->engines, pathinfo($path, PATHINFO_EXTENSION))) === self::ENGINE_PHP) {
            $this->addMultiPlaceholders($placeholders ?: []);

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
     * Callback to replace variables template.
     *
     * @param array $matches array of variables template.
     * @throws TemplateException
     * @return string
     */
    protected function replaceCallback($matches)
    {
        if (!empty($matches['beforeSkip']) && !empty($matches['afterSkip'])) {
            return trim($matches[0], '{!} ');
        }
        // check: count quotes does not parity
        if (!NumericHelper::isParity(mb_substr_count($matches[0], '`', 'UTF-8'))) {
            return $matches[0];
        }
        $matches[0] = preg_replace_callback(
            '/
                \\s*(?P<sugar> (?!`)\*(?!`) | (?!`)\*\*(?!`) | (?!`)\/(?!`) | (?!`)\%(?!`) |
                \\s+(?!`)mod(?!`)\\s+ | (?!`)\+(?!`) | (?!`)\-(?!`) | (?!`)\|(?!`) | (?!`)\&(?!`) |
                (?!`)\^(?!`) | (?!`)\>\>(?!`) | (?!`)\<\<(?!`) |
                (?!`)\|\|(?!`) | (?!`)\&\&(?!`) | \\s+(?!`)' . $this->_getInlineConditionNames() . '(?!`)\\s+ |`\\s+\?\\s+|`\\s+\:\\s+)\\s*`
            /x',
            [$this, 'replaceSugar'],
            $matches[0]);
        // replace `=` tpl mnemonics
        $matches[0] = preg_replace('/`([\!\<\>]?)[\=]+`/', '`$1&#61;`', $matches[0]);
        // replace `text` to ““text””
        $matches[0] = preg_replace(['/=\\s*\`/', '/\`/'], ['=““', '””'], $matches[0]);
        // replacement of internal recursion on {{{...}}}
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
        $params = Serialize::unserializeRecursive($params);
        // returns cache
        list($cacheKey, $cacheExpire, $cacheTags) = $this->calculateCacheParams($params);
        if (($resultCache = $this->getCacheContent($cacheKey)) !== false) {
            return $resultCache;
        }
        $filters = Serialize::unserializeRecursive($filters);
        $sanitize = $this->sanitize;
        if (isset($params['sanitize'])) {
            $sanitize = $params['sanitize'];
        } elseif(isset($matches['sanitizeDisable'])) {
            $sanitize = self::SANITIZE_DISABLE;
        }

        // chunk
        if ($matches['type'] === '$') {
            $result = $this->getChunk($matches['name'], $params);
            // alias
        } elseif ($matches['type'] === '@@') {
            $result = Alias::getAlias("@{$matches['name']}");
            // placeholder
        } elseif ($matches['type'] === '+') {
            $result = $this->getPlaceholder($matches['name'], $sanitize);
            // constant
        } elseif ($matches['type'] === '++') {
            $result = $this->getConst($matches['name'], $sanitize);
            // extension
        } elseif ($matches['type'] === '#') {
            $result =
                $this->getExtension($matches['name'], $params, $sanitize);
            //  i18n
        } elseif ($matches['type'] === '%') {
            $result = $this->_calculateI18N(
                $matches['name'],
                Helper::getValue($params['placeholders'], []),
                Helper::getValue($params['locale']),
                Helper::getValue($params['category'])
            );
            // link
        } elseif ($matches['type'] === '~') {
            $result = $this->_calculateLink($matches['name'], $params);
            // snippet
        } elseif (empty($matches['type'])) {
            $result = $this->getSnippet($matches['name'], $params, $sanitize);
        } else {
            return $matches[0];
        }
        // make a filter
        if (!empty($filters)) {
            $result = $this->makeFilter($result, $filters);
        }
        if ($this->autoSerialize) {
            if (is_array($result) || (is_object($result) && !$result instanceof \Closure)) {
                $result = @serialize($result);
            }
        }
        if (!is_scalar($result) && !empty($result)) {
            throw new TemplateException('Wrong type is var: ' . Json::encode($result));
        }
        // sets a content to cache
        $this->setCacheContent(
            $cacheKey,
            $result,
            $cacheExpire,
            $cacheTags ?: []
        );

        return $result;
    }

    private function _getInlineConditionNames()
    {
        if (!isset(static::$_inlineConditionNames)) {
            static::$_inlineConditionNames = implode('\\s+|', array_flip($this->_getConditionNames()));
        }

        return static::$_inlineConditionNames;
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

    /**
     * Search placeholders is variable of template.
     *
     * ```
     * ?<name>=<value>
     * ```
     *
     * @param string $value
     * @param array $dataRecursive
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
                $valueParams = $this->_prepareValueOfParam(str_replace('&#61;', '=', $valueParams));
                $valueParams = Helper::toType($valueParams);

            }
            // If multiple placeholders with the same name, then create to array
            if (isset($result[$nameParams])) {
                if (is_array($result[$nameParams])) {
                    $result[$nameParams][] = $valueParams;
                } else {
                    $result[$nameParams] = [$result[$nameParams], $valueParams];
                }
            } else {
                $result[$nameParams] = $valueParams;
            }
            ++$i;
        }

        return $result;
    }

    private function _prepareValueOfParam($value)
    {
        if (empty($value) || !array_key_exists($value[0], ['!' => true, '+' => true, '#' => true])) {
            return $value;
        }

        $str = $value;
        $sanitize = $this->sanitize;
        if ($value[0] === '!') {
            $str = ltrim($str, '!');
            $sanitize = self::SANITIZE_DISABLE;
        }

        if (isset($str[0])) {
            if ($str[0] === '+') {
                return $this->getPlaceholder(ltrim($str, '+'), $sanitize);
            }
            if ($str[0] === '#') {
                return $this->getExtension(ltrim($str, '#'), [], $sanitize);
            }
        }

        return $value;
    }

    /**
     * Check: not replace is `@INLINE` prefix exists or parameters then/else.
     *
     * @param string $name name of param
     * @param string $value value of param
     * @return string
     */
    private function _searchPrefix($name, $value)
    {
        $matches = [];
        // Check: not replace is @INLINE exists or parameters then/else
        preg_match('/\@(?P<prefix>(?:INLINE|FILE)).+/s', $value, $matches);
        if ((!isset($matches['prefix']) || strtolower($matches['prefix']) !== 'inline')
            && !in_array($name, ['then', 'else'], true)
        ) {
            return $this->replace($value);
        }

        return $value;
    }

    /**
     * Search of filters (modifiers).
     *
     * @param string $value
     * @param array $dataRecursive
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
     * @param string $name name of extension
     * @param array $params params
     * @throws TemplateException
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
        if (is_callable($this->extensions[$name])) {
            unset($names[0]);

            return call_user_func($this->extensions[$name], array_values($names), $params, $this);
        }

        return null;
    }

    private function _calculateI18N($name, $placeholders = [], $locale = null, $category = null)
    {
        if (!class_exists('rock\i18n\i18n')) {
            throw new TemplateException(TemplateException::NOT_INSTALL_I18N);
        }
        return i18n::t(
            explode('.', $name),
            $placeholders,
            $category,
            $locale
        );
    }

    private function _calculateLink($link, array $params = [])
    {
        if (empty($link) || !is_callable($this->handlerLink)) {
            return '#';
        }

        return call_user_func($this->handlerLink, $link, $this, $params);
    }

    protected function getSnippetInternal($snippet, array $params = [], $sanitize = null)
    {
        list($cacheKey, $cacheExpire, $cacheTags) = $this->calculateCacheParams($params);
        $params['template'] = $this;
        if ($sanitize === self::SANITIZE_DISABLE) {
            $params['sanitize'] = self::SANITIZE_DISABLE;
        }
        $snippet = $this->getInstanceSnippet($snippet, $params);
        if (!$snippet->beforeSnippet()) {
            return null;
        }
        // Get cache
        if (($resultCache = $this->getCacheContent($cacheKey)) !== false) {
            if (!$snippet->afterSnippet($resultCache)) {
                return null;
            }

            return $resultCache;
        }
        $result = $snippet->get();
        $result = $this->sanitize(
            $result,
            isset($params['sanitize']) && $params['sanitize'] === self::SANITIZE_DISABLE ? self::SANITIZE_DISABLE : $snippet->sanitize
        );
        $result = is_string($result)
            ? str_replace(
                ['[[', ']]', '{{{', '}}}', '`', '“', '”'],
                ['&#91;&#91;', '&#93;&#93;', '&#123;&#123;&#123;', '&#125;&#125;&#125;', '&#96;', '&laquo;', '&raquo;'],
                $result
            )
            : $result;
        //  Set cache
        $this->setCacheContent($cacheKey, $result, $cacheExpire, $cacheTags ?: []);
        if (!$snippet->afterSnippet($result)) {
            return null;
        }

        return $result;
    }

    /**
     * @param $snippet
     * @param array $params
     * @return Snippet
     * @throws TemplateException
     * @throws \rock\helpers\InstanceException
     */
    protected function getInstanceSnippet($snippet, array $params = [])
    {
        if ($snippet instanceof Snippet) {
            if (!empty($params)) {
                Instance::configure($snippet, $params);
            }
            return $snippet;
        }
        if (!isset($this->snippets[$snippet])) {
            throw new TemplateException(TemplateException::UNKNOWN_SNIPPET, ['name' => $snippet]);
        }
        $config = $this->snippets[$snippet];
        if (is_callable($config)) {
            if (($config = call_user_func($this->snippets[$snippet], $params)) instanceof Snippet) {
                return $config;
            }
        }

        if (!isset($config['class'])) {
            throw new TemplateException(TemplateException::UNKNOWN_SNIPPET, ['name' => $snippet]);
        }
        $config = array_merge($config, $params);

        /** @var \rock\snippets\Snippet $snippet */
        $snippet = Instance::ensure($config);

        if (!$snippet instanceof Snippet) {
            throw new TemplateException(TemplateException::UNKNOWN_SNIPPET, ['name' => $snippet::className()]);
        }
        return $snippet;
    }

    protected function replaceSugar($matches)
    {
        $matches['sugar'] = trim($matches['sugar'], " `\n\t\r");
        switch ($matches['sugar']) {
            case '||':
                return "\n:iempty\n&then=`";
            case '&&':
                return "\n:inotEmpty\n&then=`";
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

    protected function getParentPlaceholder($name)
    {
        if (is_string($name)) {
            $name = explode('.', $name);
        }
        $placeholders = $this->placeholders;
        $template = null;

        if (empty($this->scopes)) {
            $this->scopes[spl_object_hash($this)] = $this;
        }
        reset($this->scopes);
        if (current($name) === '$root') {
            /** @var Template $template */
            $template = current($this->scopes);
            array_shift($name);
            return [implode('.', $name), $template->placeholders, $template];
        }
        end($this->scopes);
        while (current($name) === '$parent') {
            $template = current($this->scopes);
            prev($this->scopes);
            next($name);
            array_shift($name);
        }

        if (isset($template) && $template instanceof static) {
            $placeholders = $template->placeholders;
        }

        return [implode('.', $name), $placeholders, $template];
    }

    private function _prepareNamePlaceholder($name)
    {
        if (is_string($name)) {
            $name = explode('.', $name);
        }
        $name = array_filter($name, function ($value) {
            return $value !== '$parent' && $value !== '$root';
        });
        return implode('.', $name);
    }

    /**
     * Renders the content to be inserted in the head section.
     * The content is rendered using the registered meta tags, link tags, CSS/JS code blocks and files.
     *
     * @return string the rendered content
     */
    protected function renderHeadHtml()
    {
        $lines = [];
        $lines[] = $this->head;
        $lines[] = '<head>';
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
        $lines[] = '</head>';

        return empty($lines) ? '' : implode("\n", $lines);
    }

    /**
     * Renders the content to be inserted at the end of the body section.
     * The content is rendered using the registered JS code blocks and files.
     *
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

    /**
     * Returns the content from the cache.
     *
     * @param string|null $key
     * @return bool
     */
    protected function getCacheContent($key = null)
    {
        if (!$this->cache instanceof CacheInterface) {
            return false;
        }
        if (isset($key) && ($result = $this->cache->get($key)) !== false) {
            if (is_array($result) && isset($result['placeholders'], $result['result'])) {
                $this->addMultiPlaceholders($result['placeholders']);
                $result = $result['result'];
            }

            return $result;
        }

        return false;
    }

    /**
     * Caching template.
     *
     * @param string $key
     * @param mixed $value
     * @param int $expire
     * @param array $tags
     */
    protected function setCacheContent($key = null, $value = null, $expire = 0, array $tags = [])
    {
        if ($this->cache instanceof CacheInterface && isset($key)) {
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

    protected function calculateCacheParams(array &$params = [])
    {
        if (empty($params) || empty($params['cacheKey'])) {
            unset($params['cacheKey'], $params['cacheExpire'], $params['cacheTags']);

            return [null, null, null];
        }
        $cacheKey = $params['cacheKey'];
        $cacheExpire = Helper::getValue($params['cacheExpire'], 0, true);
        $cacheTags = Helper::getValue($params['cacheTags'], []);
        unset($params['cacheKey'], $params['cacheExpire'], $params['cacheTags']);

        return [$cacheKey, $cacheExpire, $cacheTags];
    }

    /**
     * Normalize path.
     * @param string $path
     * @return string
     * @throws TemplateException
     * @throws \Exception
     */
    protected function normalizePath($path)
    {
        $path = Alias::getAlias($path, ['lang' => $this->locale]);
        $path = FileHelper::normalizePath($path, DIRECTORY_SEPARATOR, false);

        if (!pathinfo($path, PATHINFO_EXTENSION)) {
            $path .= '.' . $this->engines[$this->defaultEngine];
        }
        $normalizePath = $path;
        // relative path
        if ((strpos($normalizePath, DIRECTORY_SEPARATOR) === false || strpos($normalizePath, '.' . DIRECTORY_SEPARATOR) !== false) && $this->path) {
            $normalizePath = dirname($this->path) . DIRECTORY_SEPARATOR . $normalizePath;
            $normalizePath = realpath($normalizePath);
        }

        if (!file_exists($normalizePath)) {
            throw new TemplateException(TemplateException::UNKNOWN_FILE, ['path' => $path]);
        }

        return $normalizePath;
    }

    /**
     * Check path to view.
     * @param string $path path to view.
     * @return bool
     */
    protected function checkPath($path)
    {
        foreach ($this->chroots as $chroot) {
            if (StringHelper::contains($path, Alias::getAlias($chroot))) {
                return true;
            }
        }
        return false;
    }

    private function _merge(array $array1, array $array2, $recursive = false)
    {
        if ($recursive) {
            return ArrayHelper::merge($array1, $array2);
        }
        return array_merge($array1, $array2);
    }

    protected function defaultFilters()
    {
        return [
            'size' => [
                'class' => \rock\template\filters\StringFilter::className(),
            ],
            'trimPattern' => [
                'class' => \rock\template\filters\StringFilter::className(),
            ],
            'contains' => [
                'class' => \rock\template\filters\StringFilter::className(),
            ],
            'truncate' => [
                'class' => \rock\template\filters\StringFilter::className(),
            ],
            'truncateWords' => [
                'class' => \rock\template\filters\StringFilter::className(),
            ],
            'upper' => [
                'class' => \rock\template\filters\StringFilter::className(),
            ],
            'lower' => [
                'class' => \rock\template\filters\StringFilter::className(),
            ],
            'upperFirst' => [
                'class' => \rock\template\filters\StringFilter::className(),
            ],
            'encode' => [
                'class' => \rock\template\filters\StringFilter::className(),
            ],
            'decode' => [
                'class' => \rock\template\filters\StringFilter::className(),
            ],
            'markdown' => [
                'class' => \rock\template\filters\StringFilter::className(),
            ],
            'paragraph' => [
                'class' => \rock\template\filters\StringFilter::className(),
            ],
            'isParity' => [
                'class' => \rock\template\filters\NumericFilter::className(),
            ],
            'positive' => [
                'class' => \rock\template\filters\NumericFilter::className(),
            ],
            'formula' => [
                'class' => \rock\template\filters\NumericFilter::className(),
            ],
            'unserialize' => [
                'class' => \rock\template\filters\BaseFilter::className(),
            ],
            'replaceTpl' => [
                'class' => \rock\template\filters\BaseFilter::className(),
            ],
            'modifyDate' => [
                'class' => \rock\template\filters\BaseFilter::className(),
            ],
            'date' => [
                'class' => \rock\template\filters\BaseFilter::className(),
            ],
            'modifyUrl' => [
                'class' => \rock\template\filters\BaseFilter::className(),
            ],
            'url' => [
                'method' => 'modifyUrl',
                'class' => \rock\template\filters\BaseFilter::className(),
            ],
            'arrayToJson' => [
                'class' => \rock\template\filters\BaseFilter::className(),
            ],
            'toJson' => [
                'method' => 'arrayToJson',
                'class' => \rock\template\filters\BaseFilter::className(),
            ],
            'jsonToArray' => [
                'method' => 'unserialize',
                'class' => \rock\template\filters\BaseFilter::className(),
            ],
            'toArray' => [
                'method' => 'unserialize',
                'class' => \rock\template\filters\BaseFilter::className(),
            ],
            'notEmpty' => [
                'class' => \rock\template\filters\ConditionFilter::className(),
            ],
            'empty' => [
                'method' => '_empty',
                'class' => \rock\template\filters\ConditionFilter::className(),

            ],
            'inotEmpty' => [
                'class' => \rock\template\filters\ConditionFilter::className(),
            ],
            'iempty' => [
                'class' => \rock\template\filters\ConditionFilter::className(),

            ],
            'notIsset' => [
                'class' => \rock\template\filters\ConditionFilter::className(),
            ],
            'isset' => [
                'method' => '_isset',
                'class' => \rock\template\filters\ConditionFilter::className(),

            ],
            'if' => [
                'method' => '_if',
                'class' => \rock\template\filters\ConditionFilter::className(),
            ],
            'thumb' => [
                'class' => \rock\template\filters\BaseFilter::className(),
            ],
        ];
    }

    protected function defaultSnippets()
    {
        return [
            'listView' => [
                'class' => \rock\snippets\ListViewSnippet::className(),
            ],
            'list' => [
                'class' => \rock\snippets\ListViewSnippet::className(),
            ],
            'date' => [
                'class' => \rock\snippets\DateTimeSnippet::className(),
            ],
            'for' => [
                'class' => \rock\snippets\ForSnippet::className(),
            ],
            'formula' => [
                'class' => \rock\snippets\FormulaSnippet::className(),
            ],
            'if' => [
                'class' => \rock\snippets\IfSnippet::className(),
            ],
            'pagination' => [
                'class' => \rock\snippets\PaginationSnippet::className(),
            ],
            'url' => [
                'class' => \rock\snippets\UrlSnippet::className(),
            ],
            'thumb' => [
                'class' => \rock\snippets\ThumbSnippet::className(),
            ],
        ];
    }
}