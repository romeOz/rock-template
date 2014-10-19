<?php

namespace rock\template\execute;


class EvalExecute extends Execute
{
    /**
     * @param string $value key
     * @param array  $data
     * @param array  $params
     * @return mixed
     */
    public function get($value, array $params = null, array $data = null)
    {
        return eval($value);
    }
} 