<?php
namespace rock\template\snippets;

use rock\template\Snippet;
use rock\template\Template;
use rock\template\url\UrlInterface;

/**
 * Snippet "Url"
 *
 * Example:
 *
 * ```
 * [[Url
 *  ?url=`http://site.com/categories/?view=all`
 *  ?args=`{"page" : 1}`
 *  ?beginPath=`/parts`
 *  ?endPath=`/news/`
 *  ?anchor=`name`
 *  ?const=`32`
 * ]]
 * ```
 */
class Url extends Snippet implements UrlInterface
{
    public $url;
    /**
     * Set args.
     * @var array
     */
    public $args;
    /**
     * Adding args.
     * @var array
     */
    public $addArgs;
    /**
     * Adding anchor.
     * @var string
     */
    public $anchor;
    /**
     * Concat to begin URL.
     * @var string
     */
    public $beginPath;
    /**
     * Concat to end URL.
     * @var string
     */
    public $endPath;
    /**
     * Selective removing arguments.
     * @var array
     */
    public $removeArgs;
    /**
     * Removing all arguments.
     * @var bool
     */
    public $removeAllArgs;
    /**
     * Removing anchor.
     * @var bool
     */
    public $removeAnchor;
    /**
     * @var int
     * @see UrlInterface
     */
    public $const;
    /**
     * Self host
     * @var bool
     */
    public $selfHost;

    /** @var \rock\template\url\Url */
    public $urlBuilder;

    public $autoEscape = Template::STRIP_TAGS;


    public function init()
    {
        parent::init();
        if (!isset($this->urlBuilder)) {
            $this->urlBuilder = new \rock\template\url\Url;
        } elseif($this->urlBuilder instanceof \Closure) {
            $this->urlBuilder = call_user_func($this->urlBuilder, $this);
        }

        $this->urlBuilder = new $this->urlBuilder;
    }

    public function get()
    {
        $urlBuilder = $this->urlBuilder->set($this->url);
        if (isset($this->removeArgs)) {
            $urlBuilder->removeArgs($this->removeArgs);
        }
        if (isset($this->removeAllArgs)) {
            $urlBuilder->removeAllArgs();
        }
        if (isset($this->removeAnchor)) {
            $urlBuilder->removeAnchor();
        }
        if (isset($this->beginPath)) {
            $urlBuilder->addBeginPath($this->beginPath);
        }
        if (isset($this->endPath)) {
            $urlBuilder->addEndPath($this->endPath);
        }
        if (isset($this->args)) {
            $urlBuilder->setArgs($this->args);
        }
        if (isset($this->addArgs)) {
            $urlBuilder->addArgs($this->addArgs);
        }
        if (isset($this->anchor)) {
            $urlBuilder->addAnchor($this->anchor);
        }

        return $urlBuilder->get((int)$this->const, (bool)$this->selfHost);
    }
}