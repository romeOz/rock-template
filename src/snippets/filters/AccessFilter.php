<?php

namespace rock\snippets\filters;


use rock\access\Access;
use rock\helpers\Instance;

class AccessFilter extends SnippetFilter
{
    public $rules = [];

    public function before()
    {
        /** @var Access $access */
        $access = Instance::ensure([
            'class' => Access::className(),
            'owner' => $this->owner,
            'rules' => $this->rules,
        ]);

        if (!$access->checkAccess()) {
            return false;
        }

        return true;
    }
}