<?php

namespace rock\template;


use rock\base\ObjectInterface;
use rock\base\ObjectTrait;

abstract class BaseViewRenderer implements ObjectInterface
{
    use ObjectTrait;

    /**
     * Renders a view file.
     *
     * This method is invoked by {@see \rock\template\Template} whenever it tries to render a view.
     * Child classes must implement this method to render the given view file.
     *
     * @param Template $template the template instance.
     * @param string $path the view file.
     * @param array $params the parameters to be passed to the view file.
     * @return string the rendering result
     */
    abstract public function render(Template $template, $path, array $params);
}