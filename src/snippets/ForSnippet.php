<?php
namespace rock\template\snippets;

use rock\template\Snippet;

/**
 * Snippet "ForSnippet"
 *
 * ```
 * [[For
 *      ?count=`2`
 *      ?tpl=`@INLINE<b>[[+plh]]</b>`
 *      ?addPlaceholders=`["plh"]`
 *      ?wrapperTpl=`@INLINE<p>[[!+output]]</p>`
 * ]]
 * ```
 */
class ForSnippet extends Snippet
{
    /**
     * Count iteration
     * @var int
     */
    public $count;

    /**
     * Wrapped to template. Path to chunk or [[@INLINE]] html-elements
     * @var string
     */
    public $tpl;

    /**
     * Added external placeholders
     * @var array
     */
    public $addPlaceholders = [];

    /**
     * Wrapped to wrapper-template. Path to chunk or [[@INLINE]] html-elements
     * @var string
     */
    public $wrapperTpl;


    public function get()
    {
        if (!isset($this->count, $this->tpl)) {
            return null;
        }

        $result = null;
        while ($this->count > 0) {
            $result .= $this->template->replaceParamByPrefix($this->tpl, $this->template->calculateAddPlaceholders($this->addPlaceholders));
            --$this->count;
        }
        /**
         * Inserting content into wrapper template (optional)
         */
        if (!empty($this->wrapperTpl)) {
            $result = $this->template->replaceParamByPrefix($this->wrapperTpl, ['output' => $result]);
        }
//        /**
//         *  Deleting placeholders
//         */
//        if (!empty($this->addPlaceholders)) {
//            $this->Rock->template->removeMultiPlaceholders(array_keys($this->addPlaceholders));
//        }

        return $result;
    }
}