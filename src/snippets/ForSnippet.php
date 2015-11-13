<?php
namespace rock\snippets;
use rock\template\Template;

/**
 * Snippet "ForSnippet"
 *
 * ```
 * [[for
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
     * Adding external placeholders in `tpl` and `wrapperTpl`.
     * @var array
     */
    public $addPlaceholders = [];
    /**
     * Wrapper for item. You can specify the path to chunk `?tpl=/path/to/chunk`, `?tpl=@views/chunk` or
     * on the spot to specify a template `?tpl=@INLINE<b>[[+title]]</b>`.
     *
     * @var string
     */
    public $tpl;
    /**
     * Wrapper for all items. You can specify the path to chunk `?wrapperTpl=/path/to/chunk`, `?tpl=@views/chunk` or
     * on the spot to specify a template `?wrapperTpl=@INLINE<p>[[+output]]</p>`.
     * @var string
     */
    public $wrapperTpl;
    /**
     * @inheritdoc
     */
    public $sanitize = Template::SANITIZE_DISABLE;

    /**
     * @inheritdoc
     */
    public function get()
    {
        if (!isset($this->count, $this->tpl)) {
            return '';
        }

        $result = '';
        while ($this->count > 0) {
            $result .= $this->template->replaceByPrefix($this->tpl, $this->template->findPlaceholders($this->addPlaceholders));
            --$this->count;
        }
        /**
         * Inserting content into wrapper template (optional)
         */
        if (!empty($this->wrapperTpl)) {
            $result = $this->template->replaceByPrefix($this->wrapperTpl, ['output' => $result]);
        }

        return $result;
    }
}