<?php
namespace rock\snippets;

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
class UrlSnippet extends Snippet implements UrlInterface
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
    public $scheme;
    /**
     * Config to {@see \rock\url\Url} instance.
     * @var array
     */
    public $config = [];
    /**
     * @inheritdoc
     */
    public $autoEscape = Template::STRIP_TAGS;

    public function init()
    {
        parent::init();
        $this->config['csrf'] = $this->csrf;
        $this->config['request'] = $this->template->request;
        $this->modify = (array)$this->modify;
        if (isset($this->scheme)) {
            $this->modify['@scheme'] = $this->scheme;
        }
    }

    /**
     * @inheritdoc
     */
    public function get()
    {
        return \rock\url\Url::modify($this->modify, $this->config);
    }
}