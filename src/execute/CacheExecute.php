<?php

namespace rock\template\execute;

use rock\template\helpers\File;
use rock\template\Template;

class CacheExecute extends Execute
{
    public $path = '@rock/runtime/execute';

    /**
     * Create file
     *
     * @param string $path
     * @param string $value
     * @return bool
     */
    protected function createFile($path, $value)
    {
        return File::create($path, "<?php\n" . $value, LOCK_EX);
    }

    /**
     * @param string $value
     * @return string
     */
    protected function preparePath($value)
    {
        return Template::getAlias($this->path) . DIRECTORY_SEPARATOR . md5($value) . '.php';
    }

    /**
     * Get
     *
     * @param string $value - key
     * @param array  $data
     * @param array  $params
     * @throws Exception
     * @return mixed
     */
    public function get($value, array $params = null, array $data = null)
    {
        $path = static::preparePath($value);

        if (!file_exists($path) && !$this->createFile($path, $value)) {
            throw new Exception(Exception::NOT_CREATE_FILE, 0, ['path' => $path]);
        }
        unset($value);

        return include($path);
    }
}