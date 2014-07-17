<?php
namespace rock\template\helpers;

class BaseObjectHelper
{
    public static function isNamespace($value)
    {
       return (bool)strstr($value, '\\');
    }
}