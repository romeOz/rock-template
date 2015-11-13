<?php

namespace rock\snippets\filters;


use rock\filters\AccessTrait;

class AccessFilter extends SnippetFilter
{
    use AccessTrait;

    /**
     * Sending response headers. `true` by default.
     * @var bool
     */
    public $sendHeaders = false;
    public $rules = [];

    public function before()
    {
        return $this->check();
    }
}