<?php
namespace rock\template\snippets;

use rock\template\execute\CacheExecute;
use rock\template\execute\Execute;
use rock\template\helpers\Helper;
use rock\template\helpers\String;
use rock\template\Snippet;

/**
 * Snippet "Formula"
 *
 * ```html
 * [[Formula
 *      ?subject=`:pageCurrent - 1`
 *      ?operands=`{"pageCurrent" : "[[+pageCurrent]]"}`
 * ]]
 * ```
 */
class Formula extends Snippet
{
    /**
     * Subject (strip html/php-tags). E.g `:pageCurrent - 1`
     * @var string
     */
    public $subject;
    public $operands;
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
        if (!isset($this->subject) || (empty($this->operands))) {
            return null;
        }

        $data = [];
        $this->subject = strip_tags($this->subject);

        foreach ($this->operands as $keyParam => $valueParam) {
            $valueParam = Helper::toType($valueParam);
            if (is_string($valueParam)) {
                $valueParam = addslashes($valueParam);
            }
            $data[$keyParam] = $valueParam;
        }

        return $this->execute->get(
            String::trimSpaces('return ' . preg_replace('/:([\\w]+)/', '$data[\'$1\']', $this->subject) . ';'),
            [
                'subject'   => $this->subject,
                'operands'    => $this->operands
            ],
            $data
        );
    }
}

