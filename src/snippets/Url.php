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
    public $urlManager;

    public $autoEscape = Template::STRIP_TAGS;


    public function init()
    {
        parent::init();
        if (!isset($this->urlManager)) {
            $this->urlManager = new \rock\template\url\Url;
        } elseif($this->urlManager instanceof \Closure) {
            $this->urlManager = call_user_func($this->urlManager, $this);
        }

        $this->urlManager = new $this->urlManager;
    }

    public function get()
    {
        $urlManager = $this->urlManager->set($this->url);
        if (isset($this->removeArgs)) {
            $urlManager->removeArgs($this->removeArgs);
        }
        if (isset($this->removeAllArgs)) {
            $urlManager->removeAllArgs();
        }
        if (isset($this->removeAnchor)) {
            $urlManager->removeAnchor();
        }
        if (isset($this->beginPath)) {
            $urlManager->addBeginPath($this->beginPath);
        }
        if (isset($this->endPath)) {
            $urlManager->addEndPath($this->endPath);
        }
        if (isset($this->args)) {
            $urlManager->setArgs($this->args);
        }
        if (isset($this->addArgs)) {
            $urlManager->addArgs($this->addArgs);
        }
        if (isset($this->anchor)) {
            $urlManager->addAnchor($this->anchor);
        }

        return $urlManager->get((int)$this->const, (bool)$this->selfHost);
    }
}