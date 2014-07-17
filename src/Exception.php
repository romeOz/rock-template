<?php

namespace rock\template;

use rock\template\helpers\String;

class Exception extends \Exception
{
    use className;

    const UNKNOWN_CLASS = 'Unknown class: {class}';
    const UNKNOWN_METHOD = 'Unknown method: {method}';
    const NOT_UNIQUE  = 'Keys must be unique: {data}';
    const INVALID_SAVE = 'Cache invalid save by key: {key}';
    const UNKNOWN_FILE = 'Unknown file: {path}';
    const FILE_EXISTS = 'File exists: {path}';
    const NOT_CREATE_FILE = 'Does not create file: {path}';

    const UNKNOWN_SNIPPET = 'Unknown snippet: {name}';
    const UNKNOWN_FILTER = 'Unknown filter: {name}';
    const UNKNOWN_PARAM_FILTER = 'Unknown param filter: {filter}';

    /**
     * @param string     $msg
     * @param int        $code
     * @param array      $dataReplace
     * @param \Exception $handler
     */
    public function __construct($msg, $code = 0, array $dataReplace = [], \Exception $handler = null)
    {
        $msg = String::replace($msg, $dataReplace);
        return parent::__construct($msg, $code, $handler);
    }

} 