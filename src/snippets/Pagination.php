<?php
namespace rock\snippets;

use rock\helpers\Helper;
use rock\helpers\StringHelper;
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
 *      'pageVar' => 'num'
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
    public $pageVar;
    /**
     * Template for active page.
     *
     * @var string
     */
    public $pageActiveTpl = '@template.views/pagination/numActive';
    public $pageNumTpl = '@template.views/pagination/num';
    public $pageFirstName = 'page first';
    public $pageFirstTpl = '@template.views/pagination/first';
    public $pageLastName = 'page last';
    public $pageLastTpl = '@template.views/pagination/last';
    public $wrapperTpl = '@template.views/pagination/wrapper';
    /**
     * URL-arguments.
     *
     * @var array
     */
    public $pageArgs = [];
    public $pageAnchor;
    public $autoEscape = false;
    /** @var  Url */
    public $url = 'url';

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
        // if exits args-url
        if (!$this->calculateArgs()) {
            return null;
        }
        // set name of arg-url by pagination
        $pageVar = !empty($this->pageVar)
            ? $this->pageVar
            : (!empty($data['pageVar'])
                ? $data['pageVar']
                : \rock\helpers\Pagination::PAGE_VAR
            );
        // Numeration
        $num = $this->calculateNum($data, $pageVar);
        $pageFirstName = $this->calculateFirstPage($data, $pageVar);
        $pageLastName = $this->calculateLastPage($data, $pageVar);
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

    /**
     * Calculate url args
     *
     * @return bool
     */
    protected function calculateArgs()
    {
        if (empty($this->pageArgs)) {
            return true;
        }
        if (is_string($this->pageArgs)) {
            parse_str(
                StringHelper::removeSpaces($this->pageArgs),
                $this->pageArgs
            );
        }
        if (empty($this->pageArgs) || !is_array($this->pageArgs)) {
            return false;
        }
        foreach ($this->pageArgs as $key => $val) {
            if (empty($key) || empty($val)) {
                continue;
            }
            $this->pageArgs[$key] = strip_tags($val);
        }

        return true;
    }

    protected function calculateNum(array $data, $pageVar)
    {
        $result = '';
        foreach ($data['pageDisplay'] as $num) {
            $this->pageArgs[$pageVar] = $num;
            $url = $this->url->addArgs($this->pageArgs)->addAnchor($this->pageAnchor)->get();
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

    protected function calculateFirstPage(array $data, $pageVar)
    {
        if (!$pageFirst = (int)$data['pageFirst']) {
            return null;
        }
        $this->pageArgs[$pageVar] = $pageFirst;
        $placeholders = [
            'url' => $this->url
                ->addArgs($this->pageArgs)
                ->addAnchor($this->pageAnchor)
                ->get(),
            'pageFirstName' => $this->pageFirstName
        ];
        return $this->template->replaceByPrefix($this->pageFirstTpl, $placeholders);
    }

    protected function calculateLastPage(array $data, $pageVar)
    {
        if (!$pageLast = (int)$data['pageLast']) {
            return null;
        }
        $this->pageArgs[$pageVar] = $pageLast;
        $placeholders = [
            'url' => $this->url
                ->addArgs($this->pageArgs)
                ->addAnchor($this->pageAnchor)
                ->get(),
            'pageLastName' => $this->pageLastName
        ];
        return $this->template->replaceByPrefix($this->pageLastTpl, $placeholders);
    }
}