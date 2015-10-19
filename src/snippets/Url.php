<?php
namespace rock\snippets;

use rock\helpers\Instance;
use rock\template\Template;
use rock\url\UrlInterface;

/**
 * Snippet "Url"
 *
 * Example:
 *
 * ```
 * [[url
 *  ?modify=`{"0": "http://site.com/categories/?view=all", "page" : 1, "#" : "name"}`
 *  ?scheme=`abs`
 * ]]
 * ```
 */
class Url extends Snippet implements UrlInterface
{
    /**
     * Adding a CSRF-token.
     * @var bool
     */
    public $csrf = false;
    /**
     * Modify arguments.
     * @var array
     */
    public $modify;
    /**
     * Adduce URL to: `\rock\url\UrlInterface::ABS`, `\rock\url\UrlInterface::HTTP`, `\rock\url\UrlInterface::HTTPS`.
     * @var string
     * @see UrlInterface
     */
    public $scheme = \rock\url\Url::REL;
    /**
     * @inheritdoc
     */
    public $autoEscape = Template::STRIP_TAGS;
    /** @var  \rock\csrf\CSRF|string|array */
    public $csrfInstance = 'csrf';

    public function init()
    {
        parent::init();
        $this->csrfInstance = Instance::ensure($this->csrfInstance, '\rock\csrf\CSRF', [], false);
    }

    /**
     * @inheritdoc
     */
    public function get()
    {
        if ($this->csrf && $this->csrfInstance instanceof \rock\csrf\CSRF) {
            $this->modify[$this->csrfInstance->csrfParam] = $this->csrfInstance->get();
        }

        return \rock\url\Url::modify($this->modify, $this->scheme);
    }
}