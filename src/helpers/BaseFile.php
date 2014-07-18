<?php
namespace rock\template\helpers;


use rock\template\Template;

class BaseFile
{
    /**
     * Create of file
     *
     * @param string $pathFile - path to file.
     * @param string $value    - value.
     * @param int    $const    - constant for file_put_contents.
     * @param bool   $recursive
     * @param int    $mode - the permission to be set for the created file.
     * @return bool
     */
    public static function create($pathFile, $value = "", $const = 0, $recursive = true, $mode = 0775)
    {
        $pathFile = Template::getAlias($pathFile);
        if ($recursive === true) {
            if (!static::createDirectory(dirname($pathFile))) {
                return false;
            }
        }

        if (!file_put_contents($pathFile, $value, $const)) {
            return false;
        }
        chmod($pathFile, $mode);
        return true;
    }

    /**
     * Creates a new directory.
     *
     * This method is similar to the PHP `mkdir()` function except that
     * it uses `chmod()` to set the permission of the created directory
     * in order to avoid the impact of the `umask` setting.
     *
     * @param string $path path of the directory to be created.
     * @param integer $mode the permission to be set for the created directory.
     * @param boolean $recursive whether to create parent directories if they do not exist.
     * @return boolean whether the directory is created successfully
     */
    public static function createDirectory($path, $mode = 0775, $recursive = true)
    {
        $path = Template::getAlias($path);
        if (is_dir($path)) {
            return true;
        }
        $parentDir = dirname($path);
        if ($recursive && !is_dir($parentDir)) {
            static::createDirectory($parentDir, $mode, true);
        }
        if (!$result = mkdir($path, $mode)) {
            return false;
        }
        chmod($path, $mode);
        return $result;
    }

    /**
     * Removes a directory (and all its content) recursively.
     *
     * @param string $dir the directory to be deleted recursively.
     * @return bool
     */
    public static function deleteDirectory($dir)
    {
        $dir = Template::getAlias($dir);
        if (!is_dir($dir) || !($handle = opendir($dir))) {
            return false;
        }
        while (($file = readdir($handle)) !== false) {
            if ($file === '.' || $file === '..') {
                continue;
            }
            $path = $dir . DIRECTORY_SEPARATOR . $file;
            if (is_file($path)) {
                unlink($path);
            } else {
                static::deleteDirectory($path);
            }
        }
        closedir($handle);
        rmdir($dir);
        return true;
    }


    /**
     * Normalizes a file/directory path.
     * After normalization, the directory separators in the path will be `DIRECTORY_SEPARATOR`,
     * and any trailing directory separators will be removed. For example, '/home\demo/' on Linux
     * will be normalized as '/home/demo'.
     * @param string $path the file/directory path to be normalized
     * @param string $ds the directory separator to be used in the normalized result. Defaults to `DIRECTORY_SEPARATOR`.
     * @return string the normalized file/directory path
     */
    public static function normalizePath($path, $ds = DIRECTORY_SEPARATOR)
    {
        return rtrim(strtr(Template::getAlias($path), ['/' => $ds, '\\' => $ds]), $ds);
    }
}