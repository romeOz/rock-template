<?php

namespace rock\template\execute;

use rock\template\ObjectTrait;

abstract class Execute
{
    use ObjectTrait;

    /**
     * @param string $value
     * @return mixed
     */
    abstract public function get($value);
} 