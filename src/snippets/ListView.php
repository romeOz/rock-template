<?php
namespace rock\template\snippets;

use rock\template\helpers\ArrayHelper;
use rock\template\helpers\Helper;
use rock\template\helpers\Json;
use rock\template\Snippet;

/**
 * Snippet "ListView"
 *
 * Examples:
 *
 * ```
 * [[ListView
 *      ?array=`[{"name" : "Tom", "email" : "tom@site.com"}, {"name" : "Chuck", "email" : "chuck@site.com"}]`
 *      ?tpl=`@INLINE<h1>[[+name]]</h1>[[+email]]`
 *      ?wrapperTpl=`@INLINE<p>[[+output]]</p>`
 * ]]
 *
 * [[ListView
 *      ?call=`\foo\FooController.getAll`
 *      ?tpl=`/to/path/chunk_item`
 *      ?wrapperTpl=`@INLINE<p>[[+output]][[++navigation]]</p>`
 *      ?nav=`{
 *              "call" : "\\foo\\FooController.getPagination",
 *              "toPlaceholder" : "navigation"
 *      }`
 * ]]
 * ```
 *
 * As PHP engine
 *
 * ```php
 * $template = new \rock\Template;
 *
 * $items = [
 *  [
 *      'name' => 'Tom',
 *      'email' => 'tom@site.com',
 *      'about' => '<b>biography</b>'
 *  ],
 *  [
 *      'name' => 'Chuck',
 *      'email' => 'chuck@site.com'
 *  ]
 * ];
 *
 * $params = [
 *      'array' => $items
 *      'nav' => [
 *          'array' => \rock\template\helpers\Pagination::get(count($items), (int)$_GET['page'])
 *      ]
 * ];
 * $template->getSnippet('ListView', $params);
 * ```
 *
 *
 */
class ListView extends Snippet
{
    /**
     * Array for parsing
     *
     * @var array
     */
    public $array;

    /**
     * May be a callable, snippet, and instance
     *
     * ```
     * [[ListView?call=`\foo\FooController.getAll`]]
     * [[ListView?call=`context.getAll`]] - self context
     * ```
     *
     * ```php
     * $params = [
     *  'call' => ['\foo\FooController', 'getAll']
     * ];
     * (new \rock\template\Template)->getSnippet('ListView', $params);
     * ```
     *
     * @var mixed
     */
    public $call;

    /**
     * Adding placeholders
     *
     * @var
     */
    public $addPlaceholders = [];

    /**
     * params Navigate
     *          => array         - data of navigate
     *          => call             -
     *          => toPlaceholder    - navigate to placeholder (name of placeholder)
     *          => pageLimit        - count button of navigation
     *          => pageVar          - name url-argument of navigation
     *          => pageArgs         - url-arguments of navigation
     *          => pageAnchor       - url-anchor of navigation
     *          => pageNavTpl       - wrapper template of navigation
     *          => pageNumTpl       - template for disabled buttons
     *          => pageActiveTpl    - template for enabled button
     *          => pageFirstTpl     - template for button "first"
     *          => pageLastTpl      - template for button  "end"
     *
     * @var array
     */
    public $nav = [];

    /**
     * Prepare item
     * @var array
     *
     * ```php
     * ['call' => '\foo\Snippet', 'params' => [...]]
     * ['call' => function{}()]
     * ['call' => [Foo::className(), 'staticMethod']]
     * ['call' => [new Foo(), 'method']]
     * ```
     */
    public $prepare;


    /**
     * name of template
     *
     * @var string
     */
    public $tpl;

    /**
     * name of wrapper template
     *
     * @var string
     */
    public $wrapperTpl;

    /**
     * result to placeholder (name of placeholder)
     *
     * @var string
     */
    public $toPlaceholder;

    /**
     * text of error
     *
     * @var string
     */
    public $errorText;

    /**
     * @var int|bool|null
     */
    public $autoEscape = false;
        


    public function get()
    {
        if (empty($this->array) && empty($this->call)) {
            return $this->getError();
        }
        $this->calculateArray();
        $this->calculateNavigate();
        if (empty($this->array) || !is_array($this->array)) {
            return null;
        }
        return $this->renderTpl();
    }

    /**
     * Get text of error
     *
     * @return string
     */
    protected function getError()
    {
        if (!isset($this->errorText)) {
            $this->errorText = 'content is empty';
        }

        return $this->errorText;
    }

    protected function calculateArray()
    {
        $this->array = Helper::getValue($this->array);
        if (!empty($this->call)) {
            $this->array = $this->callFunction($this->call);
        }
        if (!empty($this->array) && !is_int(key($this->array))) {
            $this->array = [$this->array];
        }
    }

    /**
     * Adding navigation
     *
     * @return void
     */
    protected function calculateNavigate()
    {
        if (empty($this->nav['array']) && empty($this->nav['call'])) {
            return;
        }
        if (empty($this->nav['pageSort'])) {
            $this->nav['pageSort'] = SORT_DESC;
        }
        if (empty($this->nav['pageLimit'])) {
            $this->nav['pageLimit'] = \rock\template\helpers\Pagination::PAGE_LIMIT;
        }

        if (isset($this->nav['call'])) {
            $this->nav['array'] = $this->callFunction($this->nav['call']);
        }

        $keys = [
            'array',
            'pageVar',
            'pageArgs',
            'pageNavTpl',
            'pageNumTpl',
            'pageActiveTpl',
            'pageFirstTpl',
            'pageLastTpl',
            'pageArgs',
            'pageAnchor'
        ];
        $nav = $this->template->getSnippet('Pagination', ArrayHelper::intersectByKeys($this->nav, $keys));
        //$this->template->removeMultiPlaceholders(array_keys($navParams));
        if (!empty($this->nav['toPlaceholder'])) {
            $this->template->addPlaceholder($this->nav['toPlaceholder'], $nav, true);
            $this->template->cachePlaceholders[$this->nav['toPlaceholder']] = $nav;
            return;
        }
        $this->template->addPlaceholder('nav', $nav);
    }


    /**
     * Parsing template
     *
     * @return string|null
     */
    protected function renderTpl()
    {
        if (empty($this->tpl)) {
            return Json::encode($this->array);
        }
        $i = 1;
        $result = "";
        $countItems = count($this->array);
        //Adding placeholders
        $addPlaceholders = $this->template->calculateAddPlaceholders($this->addPlaceholders);
        $addPlaceholders['countItems'] = $countItems;
        $placeholders = [];

        foreach ($this->array as $placeholders) {
            $placeholders['currentItem'] = $i;
            $this->prepareItem($placeholders);
            $result .= $this->template->replaceParamByPrefix(
                $this->tpl,
                array_merge($placeholders, $addPlaceholders)
            );

            ++$i;
        }

        // Deleting placeholders
        $this->template->removeMultiPlaceholders(array_keys($placeholders));
        // Inserting content into wrapper template (optional)
        if (!empty($this->wrapperTpl)) {
            $result = $this->renderWrapperTpl($result, $addPlaceholders);
        }
        // Adding navigation
        $result .= $this->template->getPlaceholder('nav', false);
        // Deleting placeholders
        $this->template->removePlaceholder('nav');
        $this->template->removeMultiPlaceholders(array_keys($addPlaceholders));
        // To placeholder
        if (!empty($this->toPlaceholder)) {
            $this->template->addPlaceholder($this->toPlaceholder, $result, true);
            $this->template->cachePlaceholders[$this->toPlaceholder] = $result;
            return null;
        }

        return $result;
    }

    /**
     * @param array $placeholders
     *
     * ```php
     * ['call' => '\foo\FooSnippet', 'params' => [...]]
     * ['call' => function{}()]
     * ['call' => [Foo::className(), 'staticMethod']]
     * ['call' => [new Foo(), 'method']]
     * ```
     */
    protected function prepareItem(array &$placeholders)
    {
        if (empty($this->prepare['call'])) {
            return;
        }
        $this->prepare['params'] = Helper::getValue($this->prepare['params'], []);
        $this->prepare['params']['data'] = $placeholders;
        $this->prepare['params']['autoEscape'] = false;
        $placeholders = $this->callFunction($this->prepare['call'], $this->prepare['params']);
    }

    /**
     * Inserting content into wrapper template
     *
     * @param string $value - content
     * @param array  $placeholders
     * @return string
     */
    protected function renderWrapperTpl($value, array $placeholders)
    {
        $placeholders['output'] = $value;
        $value = $this->template->replaceParamByPrefix($this->wrapperTpl, $placeholders);
        $this->template->removePlaceholder('output');

        return $value;
    }
}