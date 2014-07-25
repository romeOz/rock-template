Template engine for PHP
=================

[![Latest Stable Version](https://poser.pugx.org/romeo7/rock-template/v/stable.svg)](https://packagist.org/packages/romeo7/rock-template)
[![Total Downloads](https://poser.pugx.org/romeo7/rock-template/downloads.svg)](https://packagist.org/packages/romeo7/rock-template)
[![Build Status](https://travis-ci.org/romeo7/rock-template.svg?branch=master)](https://travis-ci.org/romeo7/rock-template)
[![Coverage Status](https://coveralls.io/repos/romeo7/rock-template/badge.png?branch=master)](https://coveralls.io/r/romeo7/rock-template?branch=master)
[![License](https://poser.pugx.org/romeo7/rock-template/license.svg)](https://packagist.org/packages/romeo7/rock-template)

[Rock template on Packagist](https://packagist.org/packages/romeo7/rock-template)

Features
-------------------

 * Supports native PHP engine and declarative MODx-like syntax (placeholders, chunk, snippet,...)
 * The variety of filters (arithmetic/bitwise operations, conditions, string, date, and url)
 * Custom autoescaping
 * Support adding/customization filters and snippets
 * There is a possibility of adding custom extensions
 * Caching all entities template engine (suggest [Rock Cache](https://github.com/romeo7/rock-cache))

Installation
-------------------

From the Command Line:

```composer require romeo7/rock-template:*```

In your composer.json:

```json
{
    "require": {
        "romeo7/rock-template": "*"
    }
}
```

Quick Start
-------------------

###PHP engine

```php
use rock\template\Template;

$template = new Template;
$template->engine = Template::ENGINE_PHP;

echo $template->render('/to/path/layout', ['foo' => 'world!!!']);
```

Contents layout.php:

```php
<?php
/** @var \rock\template\Template $this */
?>

Hello <b><?=$this->foo?></b>
```

###Rock engine

```php
use rock\template\Template;

echo (new Template)->render('/to/path/layout', ['foo' => 'world!!!']);
```

Contents layout.html:

```html
Hello <b>[[+foo]]</b>
```

Documentation
-------------------

 * [Guide by Rock engine](https://github.com/romeo7/rock-template/blob/master/docs/rock.md)
 * [Guide by PHP engine](https://github.com/romeo7/rock-template/blob/master/docs/php.md)

Demo & Tests
-------------------

Use a specially prepared environment (Vagrant + Ansible) with preinstalled and configured storages.

###Out of the box:

 * Ubuntu 12.04 32 bit
 * Nginx 1.6
 * PHP-FPM 5.5
 * Composer
 * For caching
    * Couchbase 2.2.0 ( + pecl couchbase-1.2.2)
    * Redis 2.8 ( + php5-redis)
    * Memcached 1.4.14 ( + php5_memcached, php5_memcache)
 * Local IP loop on Host machine /etc/hosts and Virtual hosts in Nginx already set up!

> if you only want local storage for caching, then you comment out the lines redis, couchbase, and memcached
> in the file /to/path/provisioning/main.yml (**fastest way to up Vagrant**)

###Installation:

1. [Install Composer](https://getcomposer.org/doc/00-intro.md#globally)
2. ```composer create-project --prefer-dist --stability=dev romeo7/rock-template```
3. [Install Vagrant](https://www.vagrantup.com/downloads), and additional Vagrant plugins ```vagrant plugin install vagrant-hostsupdater vagrant-vbguest vagrant-cachier```
4. ```vagrant up```
5. Open demo [http://rock.tpl/](http://rock.tpl/) or [http://192.168.33.34/](http://192.168.33.34/)

> Work/editing the project can be done via ssh:
```bash
vagrant ssh
cd /var/www/
```

Requirements
-------------------

 * **PHP 5.4+**
 * **For caching (optional):**
 suggested to use [Rock Cache](https://github.com/romeo7/rock-cache). Should be installed:

```
 composer require romeo7/rock-cache:*
```

 or in your composer.json:

```json
 {
    "require": {
        "romeo7/rock-cache": "*"
    }
 }
```

License
-------------------

The Rock Template engine is open-sourced software licensed under the [MIT license](http://opensource.org/licenses/MIT).