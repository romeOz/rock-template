<?php
namespace rock\template;


abstract class Snippet
{
    use ObjectTrait;

    /**
     * @var int|bool
     */
    public $autoEscape = true;

    /** @var  Template */
    public $template;

    /**
     * @inheritdoc
     */
    public function init()
    {
        if (!isset($this->template)) {
            $this->template = new Template();
        }
    }

    /**
     * Get result
     * @return mixed
     */
    public function get()
    {
        return null;
    }


    /**
     * @param mixed $function - may be a callable, snippet, and instance
     * @param array $params
     * @throws Exception
     * @return mixed
     *
     * ```php
     * $this->callFunction('\foo\Snippet');
     * $this->callFunction('\foo\FooController.get');
     * $this->callFunction(function{}());
     * $this->callFunction([Foo::className(), 'get']);
     * $this->callFunction([new Foo(), 'get']);
     * ```
     */
    protected function callFunction($function, array $params = [])
    {
        if (is_string($function)) {
            $function = trim($function);
            if (strpos($function, '.') !== false) {
                $function = explode('.', $function);
            } else {
                return $this->template->getSnippet($function, $params);
            }
        }
        if (is_array($function)) {
            if ($function[0] === 'context') {
                $function[0] = $this->template->context;
                return call_user_func_array($function, $params);
            } elseif (is_string($function[0])) {
                if (!class_exists($function[0])) {
                    throw new Exception(Exception::UNKNOWN_CLASS, 0, ['class' => $function[0]]);
                }
                if (is_string($function[0])) {
                    $function[0] = new $function[0];
                }
                return call_user_func_array($function, $params);
            }
        }
        return call_user_func_array($function, $params);
    }
}