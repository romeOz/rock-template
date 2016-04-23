<?php
namespace rock\template\twig;

/**
 * Optimizer removes echo before special functions call and injects function name as an argument for the view helper
 * calls.
 *
 */
class Optimizer implements \Twig_NodeVisitorInterface
{
    /**
     * @inheritdoc
     */
    public function enterNode(\Twig_NodeInterface $node, \Twig_Environment $env)
    {
        return $node;
    }

    /**
     * @inheritdoc
     */
    public function leaveNode(\Twig_NodeInterface $node, \Twig_Environment $env)
    {
        if ($node instanceof \Twig_Node_Print) {
            $expression = $node->getNode('expr');
            if ($expression instanceof \Twig_Node_Expression_Function) {
                $name = $expression->getAttribute('name');
                if (preg_match('/^(?:register_.+_asset|use|.+_begin|.+_end)$/', $name)) {
                    return new \Twig_Node_Do($expression, $expression->getLine());
                } elseif (in_array($name, ['begin_page', 'end_page', 'begin_body', 'end_body'])) {
                    $arguments = [
                        new \Twig_Node_Expression_Constant($name, $expression->getLine()),
                    ];
                    if ($expression->hasNode('arguments') && $expression->getNode('arguments') !== null) {
                        foreach ($expression->getNode('arguments') as $key => $value) {
                            if (is_int($key)) {
                                $arguments[] = $value;
                            } else {
                                $arguments[$key] = $value;
                            }
                        }
                    }
                    $expression->setNode('arguments', new \Twig_Node($arguments));
                    return new \Twig_Node_Do($expression, $expression->getLine());
                }
            }
        }
        return $node;
    }

    /**
     * @inheritdoc
     */
    public function getPriority()
    {
        return 100;
    }
}