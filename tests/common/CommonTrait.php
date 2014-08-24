<?php

namespace rockunit\common;


use rock\template\helpers\File;
use rock\template\Template;

trait CommonTrait
{
    protected static function clearRuntime()
    {
        $runtime = Template::getAlias('@runtime');
        File::deleteDirectory($runtime);
        $runtime = Template::getAlias('@rock/runtime');
        File::deleteDirectory($runtime);
    }

    protected static function sort($value)
    {
        ksort($value);
        return $value;
    }
} 