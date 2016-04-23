<?php

namespace rock\template\twig;


use rock\base\Alias;
use rock\template\BaseViewRenderer;
use rock\template\Template;
use rock\template\TemplateException;

class ViewRenderer extends BaseViewRenderer
{
    /**
     * @var string the directory or path alias pointing to where Twig cache will be stored. Set to false to disable
     * templates cache.
     */
    public $cachePath = '@runtime/twig/cache';
    /**
     * @var array Twig options.
     * @see http://twig.sensiolabs.org/doc/api.html#environment-options
     */
    public $options = [];
    /**
     * @var array Objects or static classes.
     * Keys of the array are names to call in template, values are objects or names of static classes.
     * Example: `['html' => '\rock\template\Html']`.
     * In the template you can use it like this: `{{ html.a('Login', 'site/login') | raw }}`.
     */
    public $globals = [];
    /**
     * @var array Custom functions.
     * Keys of the array are names to call in template, values are names of functions or static methods of some class.
     * Example: `['rot13' => 'str_rot13', 'a' => '\rock\template\Html::a']`.
     * In the template you can use it like this: `{{ rot13('test') }}` or `{{ a('Login', 'site/login') | raw }}`.
     */
    public $functions = [];
    /**
     * @var array Custom filters.
     * Keys of the array are names to call in template, values are names of functions or static methods of some class.
     * Example: `['rot13' => 'str_rot13', 'jsonEncode' => '\rock\helpers\Json::encode']`.
     * In the template you can use it like this: `{{ 'test'|rot13 }}` or `{{ model|jsonEncode }}`.
     */
    public $filters = [];
    /**
     * @var array Custom extensions.
     * Example: `['Twig_Extension_Sandbox', new \Twig_Extension_Text()]`
     */
    public $extensions = [];
    /**
     * @var array Twig lexer options.
     *
     * Example: Smarty-like syntax:
     * ```php
     * [
     *     'tag_comment'  => ['{*', '*}'],
     *     'tag_block'    => ['{', '}'],
     *     'tag_variable' => ['{$', '}']
     * ]
     * ```
     * @see http://twig.sensiolabs.org/doc/recipes.html#customizing-the-syntax
     */
    public $lexerOptions = [];
    /**
     * @var array namespaces and classes to import.
     *
     * Example:
     *
     * ```php
     * [
     *     'rock\bootstrap',
     *     'app\assets',
     *     \rock\bootstrap\NavBar::className(),
     * ]
     * ```
     */
    public $uses = [];
    /**
     * @var \Twig_Environment twig environment object that renders twig templates
     */
    public $twig;

    public function init()
    {
        // default path to runtime
        if (!Alias::existsAlias('runtime')) {
            Alias::setAlias('runtime', dirname(__DIR__) . '/runtime');
        }

        $this->twig = new \Twig_Environment(null, array_merge([
            'cache' => Alias::getAlias($this->cachePath)
        ], $this->options));

        //$this->twig->setBaseTemplateClass('rock\template\twig\Template');
        // Adding custom globals (objects or static classes)
        if (!empty($this->globals)) {
            $this->addGlobals($this->globals);
        }
        // Adding custom functions
        if (!empty($this->functions)) {
            $this->addFunctions($this->functions);
        }
        // Adding custom filters
        if (!empty($this->filters)) {
            $this->addFilters($this->filters);
        }
        $this->addExtensions([new Extension($this->uses)]);
        // Adding custom extensions
        if (!empty($this->extensions)) {
            $this->addExtensions($this->extensions);
        }
        //$this->twig->addGlobal('app', \rock\Rock::$app);
        // Change lexer syntax (must be set after other settings)
        if (!empty($this->lexerOptions)) {
            $this->addLexerOptions($this->lexerOptions);
        }
    }

    /**
     * @inheritdoc
     */
    public function render(Template $template, $path, array $params)
    {
        $this->twig->addGlobal('this', $template);
        $loader = new \Twig_Loader_Filesystem($template->getChroots());
        $this->addAliases($loader, Alias::$aliases);
        $this->twig->setLoader($loader);
        return $this->twig->render(pathinfo($path, PATHINFO_BASENAME), $params);
    }

    /**
     * Adds a global objects or static classes
     * @param array $globals @see self::$globals
     */
    public function addGlobals($globals)
    {
        foreach ($globals as $name => $value) {
            if (!is_object($value)) {
                $value = new ViewRendererStaticClassProxy($value);
            }
            $this->twig->addGlobal($name, $value);
        }
    }

    /**
     * Adds a custom functions
     * @param array $functions @see self::$functions
     */
    public function addFunctions($functions)
    {
        $this->_addCustom('Function', $functions);
    }

    /**
     * Adds a custom filters
     * @param array $filters @see self::$filters
     */
    public function addFilters($filters)
    {
        $this->_addCustom('Filter', $filters);
    }

    /**
     * Adds a custom extensions
     * @param array $extensions @see self::$extensions
     */
    public function addExtensions($extensions)
    {
        foreach ($extensions as $extName) {
            $this->twig->addExtension(is_object($extName) ? $extName : new $extName());
        }
    }

    /**
     * Sets a Twig lexer options to change templates syntax
     * @param array $options @see self::$lexerOptions
     */
    public function addLexerOptions($options)
    {
        $lexer = new \Twig_Lexer($this->twig, $options);
        $this->twig->setLexer($lexer);
    }

    /**
     * Adds a aliases.
     *
     * @param \Twig_Loader_Filesystem $loader
     * @param array $aliases
     */
    protected function addAliases($loader, $aliases)
    {
        foreach ($aliases as $alias => $path) {
            if (is_array($path)) {
                $this->addAliases($loader, $path);
            } elseif (is_string($path) && is_dir($path)) {
                $loader->addPath($path, substr($alias, 1));
            }
        }
    }

    /**
     * Adds a custom function or filter
     * @param string $classType 'Function' or 'Filter'
     * @param array $elements Parameters of elements to add
     * @throws TemplateException
     */
    private function _addCustom($classType, $elements)
    {
        $classFunction = 'Twig_Simple' . $classType;
        foreach ($elements as $name => $func) {
            $twigElement = null;
            switch ($func) {
                // Callable (including just a name of function).
                case is_callable($func):
                    $twigElement = new $classFunction($name, $func);
                    break;
                // Callable (including just a name of function) + options array.
                case is_array($func) && is_callable($func[0]):
                    $twigElement = new $classFunction($name, $func[0], (!empty($func[1]) && is_array($func[1])) ? $func[1] : []);
                    break;
                case $func instanceof \Twig_SimpleFunction || $func instanceof \Twig_SimpleFilter:
                    $twigElement = $func;
            }
            if ($twigElement !== null) {
                $this->twig->{'add'.$classType}($twigElement);
            } else {
                throw new TemplateException("Incorrect options for \"$classType\" $name.");
            }
        }
    }

}