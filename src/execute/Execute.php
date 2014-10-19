<?php

namespace rock\template\execute;

use rock\template\ObjectTrait;

abstract class Execute
{
    use ObjectTrait;

    /**
     * @param string $value key
     * @param array  $data
     * @param array  $params
     * @return mixed
     */
    abstract public function get($value, array $params = null, array $data = null);
} 