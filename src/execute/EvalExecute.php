<?php

namespace rock\template\execute;


class EvalExecute extends Execute
{
    public function get($value)
    {
        return eval($value);
    }
} 