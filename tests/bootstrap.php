<?php
$composerAutoload = dirname(__DIR__) . '/vendor/autoload.php';
if (is_file($composerAutoload)) {
    /** @var \Composer\Autoload\ClassLoader $loader */
    $loader = require($composerAutoload);
}

$loader->addPsr4('rockunit\\', __DIR__);

defined('ROCKUNIT_RUNTIME') or define('ROCKUNIT_RUNTIME', __DIR__ . '/runtime');
ini_set('xdebug.var_display_max_data', '50000');
ini_set('xdebug.var_display_max_depth', '10');
$_SERVER['SERVER_NAME'] = $_SERVER['HTTP_HOST'] = 'site.com';
$_SERVER['REQUEST_URI'] = '/';
$_SERVER['REMOTE_ADDR'] = '127.0.0.1';