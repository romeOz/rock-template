<?php

namespace rock\snippets\filters;


use rock\access\Access;
use rock\helpers\Instance;

class AccessFilter extends SnippetFilter
{
    /** @var  Access */
    public $access;
    public $rules = [];

    public function before()
    {
        $this->access = Instance::ensure([
            'class' => Access::className(),
            'owner' => $this->owner,
            'rules' => $this->rules,
            'request' => $this->request
        ]);
        if (!$this->access->checkAccess()) {
            return false;
        }

        return true;
    }
}