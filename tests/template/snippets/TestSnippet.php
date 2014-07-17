<?php

namespace rockunit\template\snippets;


use rock\template\Snippet;

class TestSnippet extends Snippet
{
    public $param;

    public function get()
    {
        return $this->param;
    }
} 