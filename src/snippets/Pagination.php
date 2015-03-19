<?php
namespace rock\snippets;

use rock\helpers\Helper;
use rock\url\Url;

/**
 * Snippet "Pagination".
 *
 * Examples:
 *
 * ```php
 * $template = new \rock\Template;
 * $countItems = 10;
 * $params = [
 *      'array' => \rock\helpers\Pagination::get($countItems, (int)$_GET['page'], SORT_DESC)
 * ];
 * $template->getSnippet('Pagination', $params);
 * ```
 *
 * With ActiveDataProvider:
 *
 * ```php
 * $provider = new \rock\db\ActiveDataProvider(
 *  [
 *      'query' => Post::find()->asArray()->all(),
 *      'pagination' => ['limit' => 10, 'sort' => SORT_DESC, 'pageCurrent' => (int)$_GET['num']]
 *  ]
 * );
 *
 *  $params = [
 *      'array' => $provider->getPagination(),
 *      'pageParam' => 'num'
 * ];
 * $template->getSnippet('\rock\snippet\Pagination', $params);
 * ```
 */
class Pagination extends Snippet
{
    /**
     * @var array
     */
    public $array;
    /**
     * May be a callable, snippet, and instance.
     *
     * ```
     * [[Pagination?call=`\foo\FooController.getPagination`]]
     * [[Pagination?call=`context.getPagination`]] - self context
     * ```
     *
     * ```php
     * $params = [
     *  'call' => ['\foo\FooController', 'getPagination']
     * ];
     * (new \rock\Template)->getSnippet('Pagination', $params);
     * ```
     *
     * @var string|array
     */
    public $call;
    public $pageParam = \rock\helpers\Pagination::PAGE_PARAM;
    /**
     * Template for active page.
     *
     * @var string
     */
    public $pageActiveTpl = '@template.views/pagination/numActive';
    /**
     * Template for num page.
     *
     * @var string
     */
    public $pageNumTpl = '@template.views/pagination/num';
    /**
     * Caption of first page
     * @var string
     */
    public $pageFirstName = 'page first';
    /**
     * Template for first page.
     *
     * @var string
     */
    public $pageFirstTpl = '@template.views/pagination/first';
    /**
     *  Caption of last page
     * @var string
     */
    public $pageLastName = 'page last';
    /**
     * Template for last page.
     *
     * @var string
     */
    public $pageLastTpl = '@template.views/pagination/last';
    /**
     * Template for wrapper.
     *
     * @var string
     */
    public $wrapperTpl = '@template.views/pagination/wrapper';
    /** @var  Url */
    public $url = 'url';
    public $autoEscape = false;
    /**
     * URL-arguments.
     *
     * @var array
     */
    private $_pageArgs = [];

    /**
     * @inheritdoc
     */
    public function init()
    {
        parent::init();
        if (!is_object($this->url)) {
            if (class_exists('\rock\di\Container')) {
                $this->url =  \rock\di\Container::load($this->url);
            } else {
                $this->url = new Url(null, is_array($this->url) ? $this->url : []);
            }
        }
    }

    /**
     * @inheritdoc
     */
    public function get()
    {
        if (empty($this->array) && empty($this->call)) {
            return null;
        }
        $this->calculateArray();
        if (!isset($this->array['pageCount']) ||
            (int)$this->array['pageCount'] === 1 ||
            empty($this->array['pageDisplay'])
        ) {
            return null;
        }
        $data = $this->array;
        // set name of arg-url by pagination
        $pageParam = !empty($data['pageParam']) ? $data['pageParam'] : $this->pageParam;
        // Numeration
        $num = $this->calculateNum($data, $pageParam);
        $pageFirstName = $this->calculateFirstPage($data, $pageParam);
        $pageLastName = $this->calculateLastPage($data, $pageParam);
        $placeholders = [
            'num' => $num,
            'pageFirst' => $pageFirstName,
            'pageLast' => $pageLastName,
            'pageCurrent' => Helper::getValue($data['pageCurrent']),
            'countMore' => Helper::getValue($data['countMore'])
        ];

        return $this->template->replaceByPrefix($this->wrapperTpl, $placeholders);
    }

    protected function calculateArray()
    {
        $this->array = Helper::getValue($this->array);
        if (!empty($this->call)) {
            $this->array = $this->callFunction($this->call);
        }
    }

    protected function calculateNum(array $data, $pageParam)
    {
        $result = '';
        foreach ($data['pageDisplay'] as $num) {
            $this->_pageArgs[$pageParam] = $num;
            $url = $this->url->addArgs($this->_pageArgs)->get();
            // for active page
            if ((int)$data['pageCurrent'] === (int)$num) {
                $result .= $this->template->replaceByPrefix($this->pageActiveTpl, ['num' => $num, 'url' => $url]);
                continue;
            }
            // for default page
            $result .= $this->template->replaceByPrefix($this->pageNumTpl, ['num' => $num, 'url' => $url]);
        }

        return $result;
    }

    protected function calculateFirstPage(array $data, $pageParam)
    {
        if (!$pageFirst = (int)$data['pageFirst']) {
            return null;
        }
        $this->_pageArgs[$pageParam] = $pageFirst;
        $placeholders = [
            'url' => $this->url
                ->addArgs($this->_pageArgs)
                ->get(),
            'pageFirstName' => $this->pageFirstName
        ];
        return $this->template->replaceByPrefix($this->pageFirstTpl, $placeholders);
    }

    protected function calculateLastPage(array $data, $pageParam)
    {
        if (!$pageLast = (int)$data['pageLast']) {
            return null;
        }
        $this->_pageArgs[$pageParam] = $pageLast;
        $placeholders = [
            'url' => $this->url
                ->addArgs($this->_pageArgs)
                ->get(),
            'pageLastName' => $this->pageLastName
        ];
        return $this->template->replaceByPrefix($this->pageLastTpl, $placeholders);
    }
}