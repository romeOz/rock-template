<?php

namespace rockunit\snippets\data;


use rock\template\Snippet;

class PrepareSnippet extends Snippet
{
    public $data;

    public function get()
    {
        return $this->data;
    }
} 