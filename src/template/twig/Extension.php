<?php

namespace rock\template\twig;


use rock\helpers\Inflector;
use rock\helpers\StringHelper;
use rock\template\TemplateException;
use rock\url\Url;

class Extension extends \Twig_Extension
{
    /**
     * @var array used namespaces
     */
    protected $namespaces = [];
    /**
     * @var array used class aliases
     */
    protected $aliases = [];
    /**
     * @var array used widgets
     */
    protected $widgets = [];
    /**
     * Creates new instance
     *
     * @param array $uses namespaces and classes to use in the template
     */
    public function __construct(array $uses = [])
    {
        $this->addUses($uses);
    }
    /**
     * @inheritdoc
     */
    public function getNodeVisitors()
    {
        return [
            new Optimizer(),
        ];
    }
    /**
     * @inheritdoc
     */
    public function getFunctions()
    {
        $options = [
            'is_safe' => ['html'],
        ];
        $functions = [
            new \Twig_SimpleFunction('use', [$this, 'addUses'], $options),
            new \Twig_SimpleFunction('*_begin', [$this, 'beginWidget'], $options),
            new \Twig_SimpleFunction('*_end', [$this, 'endWidget'], $options),
            new \Twig_SimpleFunction('widget_end', [$this, 'endWidget'], $options),
            new \Twig_SimpleFunction('*_widget', [$this, 'widget'], $options),
            new \Twig_SimpleFunction('url_abs', [$this, 'urlAbs']),
            new \Twig_SimpleFunction('url', [$this, 'url']),
            new \Twig_SimpleFunction('void', function(){}),
            new \Twig_SimpleFunction('set', [$this, 'setProperty']),
        ];
        $options = array_merge($options, [
            'needs_context' => true,
        ]);
        
        foreach (['begin_page', 'end_page', 'begin_body', 'end_body'] as $helper) {
            $functions[] = new \Twig_SimpleFunction($helper, [$this, 'viewHelper'], $options);
        }
        return $functions;
    }

    /**
     * Function for *_begin syntax support
     *
     * @param string $widget widget name
     * @param array $config widget config
     * @return mixed
     */
    public function beginWidget($widget, $config = [])
    {
        $widget = $this->resolveClassName($widget);
        $this->widgets[] = $widget;
        return $this->call($widget, 'begin', [
            $config,
        ]);
    }

    /**
     * Function for *_end syntax support
     *
     * @param string $widget widget name
     * @throws TemplateException
     */
    public function endWidget($widget = null)
    {
        if ($widget === null) {
            if (empty($this->widgets)) {
                throw new TemplateException('Unexpected end_widget() call. A matching begin_widget() is not found.');
            }
            $this->call(array_pop($this->widgets), 'end');
        } else {
            array_pop($this->widgets);
            $this->resolveAndCall($widget, 'end');
        }
    }
    /**
     * Function for *_widget syntax support
     *
     * @param string $widget widget name
     * @param array $config widget config
     * @return mixed
     */
    public function widget($widget, $config = [])
    {
        return $this->resolveAndCall($widget, 'widget', [
            $config,
        ]);
    }
    /**
     * Used for 'begin_page', 'end_page', 'begin_body', 'end_body', 'head'
     *
     * @param array $context context information
     * @param string $name
     */
    public function viewHelper($context, $name = null)
    {
        //var_dump();
        //$name = 'end_body';
        //var_dump(debug_backtrace(DEBUG_BACKTRACE_IGNORE_ARGS ));
        if ($name !== null && isset($context['this'])) {
            echo $this->call($context['this'], Inflector::variablize($name));
        }
    }
    /**
     * Resolves a method from widget and asset syntax and calls it
     *
     * @param string $className class name
     * @param string $method method name
     * @param array $arguments
     * @return mixed
     */
    public function resolveAndCall($className, $method, $arguments = null)
    {
        return $this->call($this->resolveClassName($className), $method, $arguments);
    }
    /**
     * Calls a method
     *
     * @param string $className class name
     * @param string $method method name
     * @param array $arguments
     * @return mixed
     */
    public function call($className, $method, $arguments = null)
    {
        $callable = [$className, $method];
        if ($arguments === null) {
            return call_user_func($callable);
        } else {
            return call_user_func_array($callable, $arguments);
        }
    }
    /**
     * Resolves class name from widget and asset syntax
     *
     * @param string $className class name
     * @return string
     */
    public function resolveClassName($className)
    {
        $className = Inflector::id2camel($className, '_');
        if (isset($this->aliases[$className])) {
            return $this->aliases[$className];
        }
        foreach ($this->namespaces as $namespace) {
            $resolvedClassName = $namespace . '\\' . $className;
            if (class_exists($resolvedClassName)) {
                return $this->aliases[$className] = $resolvedClassName;
            }
        }
        return $className;
    }
    /**
     * Adds a namespaces and aliases from constructor
     *
     * @param array $args namespaces and classes to use in the template
     */
    public function addUses($args)
    {
        foreach ((array) $args as $key => $value) {
            $value = str_replace('/', '\\', $value);
            if (is_int($key)) {
                // namespace or class import
                if (class_exists($value)) {
                    // class import
                    $this->aliases[StringHelper::basename($value)] = $value;
                } else {
                    // namespace
                    $this->namespaces[] = $value;
                }
            } else {
                // aliased class import
                $this->aliases[$key] = $value;
            }
        }
    }
    /**
     * Generates absolute URL
     *
     * @param string $url the parameter to be used to generate a valid URL
     * @param array $config
     * @return string the generated absolute URL
     */
    public function urlAbs($url, array $config = [])
    {
        $url = (array)$url;
        $url['@scheme'] = Url::ABS;
        return Url::modify($config);
    }
    /**
     * Generates relative URL
     *
     * @param string $url the parameter to be used to generate a valid URL
     * @param array $config
     * @return string the generated relative URL
     */
    public function url($url, $config = [])
    {
        return Url::modify($url, $config);
    }
    /**
     * Sets object property
     *
     * @param \stdClass $object
     * @param string $property
     * @param mixed $value
     */
    public function setProperty($object, $property, $value)
    {
        $object->$property = $value;
    }
    /**
     * @inheritdoc
     */
    public function getName()
    {
        return 'rock-twig';
    }
}