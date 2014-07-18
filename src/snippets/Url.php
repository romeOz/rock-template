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
    public $args;
    public $addArgs;
    public $anchor;
    public $selfHost;
    public $beginPath;
    public $endPath;
    public $removeArgs;
    public $removeAllArgs;
    public $removeAnchor;
    public $const;

    public $autoEscape = Template::STRIP_TAGS;

    /** @var \rock\template\url\Url */
    public $urlManager;


    public function init()
    {
        parent::init();
        if (!isset($this->urlManager)) {
            $this->urlManager = new \rock\template\url\Url;
        } elseif($this->urlManager instanceof \Closure) {
            $this->urlManager = call_user_func($this->urlManager, $this);
        }
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
