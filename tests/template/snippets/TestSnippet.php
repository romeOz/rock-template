<?php

namespace rockunit\template\snippets;


use rock\snippets\Snippet;

class TestSnippet extends Snippet
{
    public $param;

    public function get()
    {
        return $this->param;
    }
} 