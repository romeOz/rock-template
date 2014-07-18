<?php
namespace rock\template\snippets;

use rock\template\execute\CacheExecute;
use rock\template\execute\Execute;
use rock\template\helpers\Helper;
use rock\template\helpers\String;
use rock\template\Snippet;

/**
 * Snippet "IfSnippet"
 *
 * [[If
 *      ?subject=`((int):foo > 1) && ((int):foo < 3)`
 *      ?operands=`{"foo" : "[[+foo]]"}`
 *      ?then=`success`
 *      ?else=`fail`
 * ]]
 */
class IfSnippet extends Snippet
{
    /**
     * condition (disallow html/php-tags)
     * @var string
     */
    public $subject;

    public $operands = [];
    public $then;
    public $else;
    /**
     * Added external placeholders
     * @var array
     */
    public $addPlaceholders = [];

    /** @var Execute */
    public $execute;

    public function init()
    {
        parent::init();
        if (!isset($this->execute)) {
            $this->execute = new CacheExecute;
        } elseif($this->execute instanceof \Closure) {
            $this->execute = call_user_func($this->execute);
        }
    }

    public function get()
    {
        if (!isset($this->subject, $this->operands, $this->then) ||
            empty($this->operands)) {
            return null;
        }
        $operands = $this->operands;
        $this->template->addMultiPlaceholders($this->template->calculateAddPlaceholders($this->addPlaceholders));
        $paramsTpl = [
            'subject'   => $this->subject,
            'params'    => $operands,
            'then'      => $this->then,
            'template' => $this->template
        ];

        if (isset($this->else)) {
            $paramsTpl['else'] = $this->else;
        }
        $data = [];
        $this->subject = strip_tags($this->subject);
        foreach ($operands as $keyParam => $valueParam) {

            $valueParam = Helper::toType($valueParam);

            if (is_string($valueParam)) {
                $valueParam = addslashes((string)$valueParam);
            }

            $data[$keyParam] = $valueParam;
        }

        $value = '
            $template = $params[\'template\'];
            if (' . preg_replace('/:([\\w]+)/', '$data[\'$1\']', $this->subject) . ') {
                return $template->replace($params[\'then\']);
            }' .
            (isset($this->else)
                ? ' else {return $template->replace($params[\'else\']);}'
                : null
            );

        return $this->execute->get(String::trimSpaces($value), $paramsTpl, $data);
    }
}